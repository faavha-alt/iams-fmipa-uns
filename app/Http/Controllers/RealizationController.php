<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\ProcurementBatch;
use App\Models\PurchaseRealization;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RealizationController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $yearRange = ["{$year}-01-01", "{$year}-12-31"];

        $realizations = PurchaseRealization::query()
            ->with(['unit', 'category', 'recordedBy', 'procurementBatch.vendor', 'vendor'])
            ->withCount('assets')
            ->whereBetween('purchase_date', $yearRange)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->input('unit_id')))
            ->when($request->filled('search'), fn ($q) => $q->where('item_name', 'like', '%'.$request->input('search').'%'))
            ->latest('purchase_date')
            ->get();

        // Vendor sekarang melekat ke Pengadaan induknya. Fallback ke vendor_id lama (data sebelum
        // perubahan ini) supaya data lama tetap tampil rapi tanpa perlu migrasi data manual.
        $grouped = $realizations
            ->groupBy(fn ($r) => $r->procurementBatch?->vendor?->name ?? $r->vendor?->name ?? 'Tanpa Vendor')
            ->sortKeys();

        return view('realizations.index', [
            'grouped' => $grouped,
            'year' => $year,
            'units' => Unit::orderBy('name')->get(),
            'totalBelumFinal' => PurchaseRealization::whereBetween('purchase_date', $yearRange)->where('status', 'belum_final')->sum('cost'),
            'totalSudahFinal' => PurchaseRealization::whereBetween('purchase_date', $yearRange)->where('status', 'sudah_final')->sum('cost'),
        ]);
    }

    public function create(Request $request): View
    {
        $batches = ProcurementBatch::orderByDesc('tanggal_mulai')->get();

        abort_if($batches->isEmpty(), 403, 'Belum ada Pengadaan. Buat Pengadaan dulu (pilih vendornya), baru bisa tambah barang.');

        return view('realizations.form', [
            'realization' => null,
            'units' => Unit::orderBy('name')->get(),
            'categories' => AssetCategory::orderBy('name')->get(),
            'batches' => $batches,
            'preselectedBatchId' => $request->query('batch'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $cost = $data['quantity'] * $data['unit_price'];
        unset($data['unit_price']);

        PurchaseRealization::create([
            ...$data,
            'cost' => $cost,
            'status' => 'belum_final',
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('realizations.index', ['year' => date('Y', strtotime($data['purchase_date']))])
            ->with('message', 'Barang berhasil dicatat. Sisa anggaran sudah ikut terpotong meskipun belum jadi record aset resmi.');
    }

    public function edit(PurchaseRealization $realization): View
    {
        $this->abortIfFinalized($realization);

        return view('realizations.form', [
            'realization' => $realization,
            'units' => Unit::orderBy('name')->get(),
            'categories' => AssetCategory::orderBy('name')->get(),
            'batches' => ProcurementBatch::orderByDesc('tanggal_mulai')->get(),
            'preselectedBatchId' => null,
        ]);
    }

    public function update(Request $request, PurchaseRealization $realization): RedirectResponse
    {
        $this->abortIfFinalized($realization);

        $data = $this->validated($request);
        $cost = $data['quantity'] * $data['unit_price'];
        unset($data['unit_price']);

        $realization->update([...$data, 'cost' => $cost]);

        return redirect()->route('realizations.index', ['year' => date('Y', strtotime($data['purchase_date']))])
            ->with('message', 'Barang berhasil diperbarui.');
    }

    public function destroy(PurchaseRealization $realization): RedirectResponse
    {
        $this->abortIfFinalized($realization);

        $year = $realization->purchase_date->year;
        $realization->delete();

        return redirect()->route('realizations.index', ['year' => $year])
            ->with('message', 'Barang berhasil dihapus, sisa anggaran sudah dikembalikan.');
    }

    public function finalizeForm(PurchaseRealization $realization): View
    {
        $this->abortIfFinalized($realization);

        return view('realizations.finalize', [
            'realization' => $realization,
            'locations' => Location::where('unit_id', $realization->unit_id)->orderBy('name')->get(),
            'categories' => AssetCategory::orderBy('name')->get(),
        ]);
    }

    public function finalize(Request $request, PurchaseRealization $realization): RedirectResponse
    {
        $this->abortIfFinalized($realization);

        $data = $request->validate([
            'asset_category_id' => 'required|exists:asset_categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'condition' => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
            'status' => 'required|in:aktif,dalam_perbaikan,dipinjamkan,dihapuskan',
        ]);

        $unitPrice = $realization->quantity > 0 ? $realization->cost / $realization->quantity : $realization->cost;

        // Vendor diambil dari Pengadaan induknya. Kalau realisasi lama belum punya Pengadaan
        // (dari sebelum perubahan ini), fallback ke vendor_id lama di barangnya sendiri.
        $vendorId = $realization->procurementBatch?->vendor_id ?? $realization->vendor_id;

        for ($i = 0; $i < $realization->quantity; $i++) {
            Asset::create([
                'purchase_realization_id' => $realization->id,
                'name' => $realization->item_name,
                'brand' => $data['brand'] ?? null,
                'model' => $data['model'] ?? null,
                'asset_category_id' => $data['asset_category_id'],
                'unit_id' => $realization->unit_id,
                'location_id' => $data['location_id'] ?? null,
                'acquisition_date' => $realization->purchase_date,
                'acquisition_source' => 'pengadaan',
                'vendor_id' => $vendorId,
                'acquisition_value' => $unitPrice,
                'condition' => $data['condition'],
                'status' => $data['status'],
            ]);
        }

        $realization->update(['status' => 'sudah_final']);

        $label = $realization->quantity > 1 ? "{$realization->quantity} aset" : '1 aset';

        return redirect()->route('realizations.index', ['year' => $realization->purchase_date->year])
            ->with('message', "Realisasi difinalisasi jadi {$label}, lengkap dengan kode aset & QR Code otomatis.");
    }

    private function abortIfFinalized(PurchaseRealization $realization): void
    {
        abort_if($realization->isFinalized(), 403, 'Barang ini sudah difinalisasi jadi aset — ubah/hapus lewat halaman Aset, bukan di sini.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'unit_id' => 'required|exists:units,id',
            'procurement_batch_id' => 'required|exists:procurement_batches,id',
            'asset_category_id' => 'nullable|exists:asset_categories,id',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
    }
}
