<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetRequest;
use App\Models\Budget;
use App\Models\PurchaseRealization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetRequestController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';

        $requests = AssetRequest::query()
            ->with(['unit', 'requestedBy', 'category'])
            ->when(! $isAdmin, fn ($q) => $q->where('unit_id', $user->unit_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        // Konteks sisa pagu per kombinasi unit+tahun (karena tiap pengajuan bisa target tahun anggaran berbeda)
        $paguContext = [];
        if ($isAdmin) {
            foreach ($requests as $req) {
                $year = $req->fiscal_year ?? now()->year;
                $key = $req->unit_id.'-'.$year;

                if (! isset($paguContext[$key])) {
                    $paguContext[$key] = $this->hitungSisaPagu($req->unit_id, $year);
                }
            }
        }

        return view('requests.index', [
            'requests' => $requests,
            'isAdmin' => $isAdmin,
            'paguContext' => $paguContext,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $year = (int) $request->input('year', now()->year);

        return view('requests.form', [
            'categories' => AssetCategory::orderBy('name')->get(),
            'availableYears' => range(now()->year + 1, now()->year - 1),
            'selectedYear' => $year,
            'sisaPagu' => $this->hitungSisaPagu($user->unit_id, $year),
            'hasPagu' => Budget::where('unit_id', $user->unit_id)->where('fiscal_year', $year)->exists(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fiscal_year' => 'required|integer|min:2020|max:2100',
            'item_name' => 'required|string|max:255',
            'asset_category_id' => 'nullable|exists:asset_categories,id',
            'quantity' => 'required|integer|min:1',
            'estimated_unit_price' => 'nullable|numeric|min:0',
            'purchase_link' => 'required|url|max:255',
            'supporting_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'reason' => 'required|string|min:10',
        ]);

        if (! empty($data['estimated_unit_price'])) {
            $data['estimated_cost'] = $data['quantity'] * $data['estimated_unit_price'];
        }
        unset($data['estimated_unit_price']);

        $data['supporting_image'] = $request->file('supporting_image')->store('pengajuan', 'public');

        $user = $request->user();

        AssetRequest::create([
            ...$data,
            'unit_id' => $user->unit_id,
            'requested_by' => $user->id,
            'status' => 'diajukan',
        ]);

        return redirect()->route('requests.index')->with('message', 'Pengajuan berhasil dikirim, menunggu tinjauan admin.');
    }

    public function decide(Request $request, AssetRequest $assetRequest): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin', 403);

        $data = $request->validate([
            'decision' => 'required|in:setuju,tolak',
            'review_notes' => 'nullable|string',
        ]);

        $assetRequest->update([
            'status' => $data['decision'] === 'setuju' ? 'disetujui' : 'ditolak',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $data['review_notes'] ?? null,
        ]);

        $message = $data['decision'] === 'setuju' ? 'Pengajuan disetujui.' : 'Pengajuan ditolak.';

        return redirect()->route('requests.index')->with('message', $message);
    }

    private function hitungSisaPagu(int $unitId, int $year): float
    {
        $pagu = Budget::where('unit_id', $unitId)->where('fiscal_year', $year)->value('amount') ?? 0;
        $realisasiAset = Asset::where('unit_id', $unitId)->whereYear('acquisition_date', $year)->sum('acquisition_value');
        $realisasiBelumFinal = PurchaseRealization::where('unit_id', $unitId)->where('status', 'belum_final')->whereYear('purchase_date', $year)->sum('cost');

        return $pagu - ($realisasiAset + $realisasiBelumFinal);
    }
}
