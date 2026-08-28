<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\ProcurementBatch;
use App\Models\PurchaseRealization;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcurementBatchController extends Controller
{
    public function index(Request $request): View
    {
        $batches = ProcurementBatch::query()
            ->with('vendor')
            ->withCount('realizations')
            ->withSum('realizations', 'cost')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest('tanggal_mulai')
            ->paginate(15)
            ->appends($request->query());

        return view('procurement-batches.index', [
            'batches' => $batches,
            'orphanCount' => PurchaseRealization::whereNull('procurement_batch_id')->count(),
            'stats' => $this->dashboardStats(),
        ]);
    }

    /**
     * Ringkasan untuk header halaman daftar pengadaan: total nilai, jumlah barang,
     * serapan terhadap pagu tahun berjalan, dan pecahan nilai per kategori alat.
     */
    private function dashboardStats(): array
    {
        $year = now()->year;
        $totalValue = (float) PurchaseRealization::whereNotNull('procurement_batch_id')->sum('cost');

        // Pagu total = jumlah pagu baris fakultas saja; di model anggaran, amount fakultas
        // sudah mencakup alokasi prodi, jadi menjumlah semua baris Budget akan dobel.
        $facultyIds = Unit::where('type', 'fakultas')->pluck('id');
        $paguTahunIni = (float) Budget::where('fiscal_year', $year)
            ->when($facultyIds->isNotEmpty(), fn ($q) => $q->whereIn('unit_id', $facultyIds))
            ->sum('amount');
        $nilaiTahunIni = (float) PurchaseRealization::whereNotNull('procurement_batch_id')
            ->whereBetween('purchase_date', ["{$year}-01-01", "{$year}-12-31"])
            ->sum('cost');

        $perCategory = PurchaseRealization::whereNotNull('procurement_batch_id')
            ->selectRaw('asset_category_id, COUNT(*) as jumlah, SUM(cost) as nilai')
            ->groupBy('asset_category_id')
            ->orderByDesc('nilai')
            ->with('category')
            ->limit(8)
            ->get()
            ->map(fn ($r) => [
                'nama' => $r->category?->name ?? 'Tanpa kategori',
                'jumlah' => (int) $r->jumlah,
                'nilai' => (float) $r->nilai,
                'persen' => $totalValue > 0 ? round((float) $r->nilai / $totalValue * 100, 1) : 0,
            ]);

        return [
            'year' => $year,
            'total_batches' => ProcurementBatch::count(),
            'total_items' => PurchaseRealization::whereNotNull('procurement_batch_id')->count(),
            'total_value' => $totalValue,
            'pagu_tahun_ini' => $paguTahunIni,
            'nilai_tahun_ini' => $nilaiTahunIni,
            'serapan_persen' => $paguTahunIni > 0 ? round($nilaiTahunIni / $paguTahunIni * 100, 1) : null,
            'per_category' => $perCategory,
        ];
    }

    public function create(): View
    {
        return view('procurement-batches.form', ['batch' => null, 'vendors' => \App\Models\Vendor::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        ProcurementBatch::create($data);

        return redirect()->route('procurement-batches.index')->with('message', 'Pengadaan baru berhasil dibuat.');
    }

    public function edit(ProcurementBatch $procurementBatch): View
    {
        return view('procurement-batches.form', ['batch' => $procurementBatch, 'vendors' => \App\Models\Vendor::orderBy('name')->get()]);
    }

    public function update(Request $request, ProcurementBatch $procurementBatch): RedirectResponse
    {
        $procurementBatch->update($this->validated($request));

        return redirect()->route('procurement-batches.index')->with('message', 'Pengadaan berhasil diperbarui.');
    }

    public function show(Request $request, ProcurementBatch $procurementBatch): View
    {
        $orphans = PurchaseRealization::whereNull('procurement_batch_id')
            ->with(['vendor', 'unit'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where('item_name', 'like', "%{$search}%");
            })
            ->latest('purchase_date')
            ->limit(100)
            ->get();

        $items = $procurementBatch->realizations()->with(['unit', 'category'])->withCount('assets')->latest('purchase_date')->get();

        return view('procurement-batches.show', [
            'batch' => $procurementBatch,
            'items' => $items,
            'orphans' => $orphans,
        ]);
    }

    /**
     * Tarik realisasi yang belum punya pengadaan (final maupun belum) ke pengadaan ini.
     * Sengaja terpisah dari update() biasa supaya bisa dipakai walau realisasinya sudah final
     * (assign ke pengadaan itu cuma pengelompokan administratif, bukan perubahan data keuangan).
     */
    public function attachRealizations(Request $request, ProcurementBatch $procurementBatch): RedirectResponse
    {
        $data = $request->validate([
            'realization_ids' => 'required|array|min:1',
            'realization_ids.*' => 'exists:purchase_realizations,id',
        ]);

        $count = PurchaseRealization::whereNull('procurement_batch_id')
            ->whereIn('id', $data['realization_ids'])
            ->update(['procurement_batch_id' => $procurementBatch->id]);

        return redirect()->route('procurement-batches.show', $procurementBatch->id)
            ->with('message', "{$count} realisasi berhasil ditambahkan ke pengadaan ini.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => 'required|string|max:255',
            'vendor_id' => 'required|exists:vendors,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'nomor_dokumen' => 'nullable|string|max:255',
            'status' => 'required|in:berjalan,selesai',
            'catatan' => 'nullable|string',
        ]);
    }
}
