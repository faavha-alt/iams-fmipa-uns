<?php

namespace App\Http\Controllers;

use App\Concerns\RetriesUniqueConstraint;
use App\Models\Asset;
use App\Models\HandoverReport;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HandoverReportController extends Controller
{
    use RetriesUniqueConstraint;

    public function index(Request $request): View
    {
        $reports = HandoverReport::query()
            ->with(['unit', 'assets'])
            ->when($request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->input('unit_id')))
            ->latest('tanggal_serah_terima')
            ->paginate(15)
            ->appends($request->query());

        $unitsWithPending = Unit::whereHas('assets', fn ($q) => $q->whereNotNull('purchase_realization_id')->whereDoesntHave('handoverReports'))
            ->withCount(['assets as pending_count' => fn ($q) => $q->whereNotNull('purchase_realization_id')->whereDoesntHave('handoverReports')])
            ->orderBy('name')
            ->get();

        return view('handover-reports.index', [
            'reports' => $reports,
            'units' => Unit::orderBy('name')->get(),
            'unitsWithPending' => $unitsWithPending,
        ]);
    }

    public function create(Unit $unit): View
    {
        $availableAssets = $this->availableAssetsForUnit($unit);

        abort_if($availableAssets->isEmpty(), 403, 'Tidak ada barang yang menunggu BAST untuk unit ini.');

        return view('handover-reports.form', [
            'unit' => $unit,
            'report' => null,
            'availableAssets' => $availableAssets,
            'selectedAssetIds' => [],
        ]);
    }

    public function store(Request $request, Unit $unit): RedirectResponse
    {
        $data = $this->validated($request);

        $validAssetIds = $this->availableAssetsForUnit($unit)->whereIn('id', $data['asset_ids'])->pluck('id');
        abort_if($validAssetIds->isEmpty(), 422, 'Barang yang dipilih tidak valid.');

        // Pembuatan BAST (termasuk generate nomor) dibungkus transaksi + retry agar tidak
        // ada BAST parsial dan nomor bentrok (UNIQUE) saat dua request dibuat bersamaan.
        $this->retryOnUniqueViolation(function () use ($request, $unit, $data, $validAssetIds, &$report) {
            DB::transaction(function () use ($request, $unit, $data, $validAssetIds, &$report) {
                $report = HandoverReport::create([
                    ...collect($data)->except('asset_ids')->toArray(),
                    'nomor_bast' => HandoverReport::generateNomor(),
                    'unit_id' => $unit->id,
                    'status' => 'draft',
                    'created_by' => $request->user()->id,
                ]);

                $report->assets()->attach($validAssetIds);
            });
        });

        return redirect()->route('handover-reports.show', $report->id)->with('message', 'BAST berhasil dibuat. Silakan cetak untuk ditandatangani.');
    }

    public function edit(HandoverReport $handoverReport): View
    {
        $this->abortIfNotDraft($handoverReport);

        $availableAssets = $this->availableAssetsForUnit($handoverReport->unit, $handoverReport);

        return view('handover-reports.form', [
            'unit' => $handoverReport->unit,
            'report' => $handoverReport,
            'availableAssets' => $availableAssets,
            'selectedAssetIds' => $handoverReport->assets->pluck('id')->toArray(),
        ]);
    }

    public function update(Request $request, HandoverReport $handoverReport): RedirectResponse
    {
        $this->abortIfNotDraft($handoverReport);

        $data = $this->validated($request);

        $validAssetIds = $this->availableAssetsForUnit($handoverReport->unit, $handoverReport)
            ->whereIn('id', $data['asset_ids'])
            ->pluck('id');
        abort_if($validAssetIds->isEmpty(), 422, 'Barang yang dipilih tidak valid.');

        $handoverReport->update(collect($data)->except('asset_ids')->toArray());
        $handoverReport->assets()->sync($validAssetIds);

        return redirect()->route('handover-reports.show', $handoverReport->id)->with('message', 'BAST berhasil diperbarui.');
    }

    public function show(HandoverReport $handoverReport): View
    {
        $handoverReport->load(['unit', 'assets.category', 'createdBy']);

        return view('handover-reports.show', ['report' => $handoverReport]);
    }

    public function print(HandoverReport $handoverReport): View
    {
        $handoverReport->load(['unit', 'assets.category']);

        return view('handover-reports.print', [
            'report' => $handoverReport,
            'kopLogo' => \App\Models\Setting::get('bast_kop_logo'),
            'kopBaris1' => \App\Models\Setting::get('bast_kop_baris1', 'Fakultas Matematika dan Ilmu Pengetahuan Alam'),
            'kopBaris2' => \App\Models\Setting::get('bast_kop_baris2', 'Universitas Sebelas Maret'),
        ]);
    }

    public function uploadScan(Request $request, HandoverReport $handoverReport): RedirectResponse
    {
        $request->validate([
            'dokumen_scan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('dokumen_scan')->store('bast', 'public');

        $handoverReport->update([
            'dokumen_scan' => $path,
            'status' => 'final',
        ]);

        return redirect()->route('handover-reports.show', $handoverReport->id)->with('message', 'Dokumen BAST yang sudah ditandatangani berhasil diupload. Status jadi Final.');
    }

    private function availableAssetsForUnit(Unit $unit, ?HandoverReport $excludeReport = null)
    {
        return Asset::where('unit_id', $unit->id)
            ->whereNotNull('purchase_realization_id')
            ->where(function ($q) use ($excludeReport) {
                $q->whereDoesntHave('handoverReports');
                if ($excludeReport) {
                    $q->orWhereHas('handoverReports', fn ($sub) => $sub->where('handover_reports.id', $excludeReport->id));
                }
            })
            ->with('category')
            ->latest('acquisition_date')
            ->get();
    }

    private function abortIfNotDraft(HandoverReport $report): void
    {
        abort_if($report->status !== 'draft', 403, 'BAST ini sudah final (sudah ada dokumen bertanda tangan) — tidak bisa diubah lagi.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'asset_ids' => 'required|array|min:1',
            'asset_ids.*' => 'exists:assets,id',
            'tanggal_serah_terima' => 'required|date',
            'nama_menyerahkan' => 'required|string|max:255',
            'jabatan_menyerahkan' => 'required|string|max:255',
            'nama_menerima' => 'required|string|max:255',
            'jabatan_menerima' => 'required|string|max:255',
            'catatan' => 'nullable|string',
        ]);
    }
}
