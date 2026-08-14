<?php

namespace App\Http\Controllers;

use App\Models\ProcurementBatch;
use App\Models\PurchaseRealization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcurementBatchController extends Controller
{
    public function index(Request $request): View
    {
        $batches = ProcurementBatch::query()
            ->withCount('realizations')
            ->withSum('realizations', 'cost')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest('tanggal_mulai')
            ->paginate(15)
            ->appends($request->query());

        return view('procurement-batches.index', [
            'batches' => $batches,
            'orphanCount' => PurchaseRealization::whereNull('procurement_batch_id')->count(),
        ]);
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

        return redirect()->route('procurement-batches.index')->with('message', 'Periode pengadaan baru berhasil dibuat.');
    }

    public function edit(ProcurementBatch $procurementBatch): View
    {
        return view('procurement-batches.form', ['batch' => $procurementBatch, 'vendors' => \App\Models\Vendor::orderBy('name')->get()]);
    }

    public function update(Request $request, ProcurementBatch $procurementBatch): RedirectResponse
    {
        $procurementBatch->update($this->validated($request));

        return redirect()->route('procurement-batches.index')->with('message', 'Periode pengadaan berhasil diperbarui.');
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
     * Tarik realisasi yang belum punya periode (final maupun belum) ke periode ini.
     * Sengaja terpisah dari update() biasa supaya bisa dipakai walau realisasinya sudah final
     * (assign ke periode itu cuma pengelompokan administratif, bukan perubahan data keuangan).
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
            ->with('message', "{$count} realisasi berhasil ditambahkan ke periode ini.");
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
