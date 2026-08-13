<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $units = Unit::query()
            ->with(['parent', 'head'])
            ->withCount('assets')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->input('search').'%'))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->query());

        return view('units.index', ['units' => $units]);
    }

    /**
     * Detail satu unit/prodi — rekap & daftar aset yang tercatat di unit itu, bisa langsung diedit dari sini.
     */
    public function show(Request $request, Unit $unit): View
    {
        $unit->load(['parent', 'head', 'children']);

        $assetBase = $unit->assets();
        $totalAssets = (clone $assetBase)->count();
        $totalValue = (clone $assetBase)->sum('acquisition_value');

        $conditionCounts = (clone $assetBase)
            ->select('condition')->selectRaw('count(*) as total')
            ->groupBy('condition')->pluck('total', 'condition');

        $conditions = collect(['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])
            ->map(fn ($key) => [
                'key' => $key,
                'total' => $conditionCounts[$key] ?? 0,
                'percent' => $totalAssets > 0 ? round((($conditionCounts[$key] ?? 0) / $totalAssets) * 100) : 0,
            ]);

        $categoryRecap = (clone $assetBase)
            ->select('asset_category_id')->selectRaw('count(*) as total')
            ->groupBy('asset_category_id')
            ->with('category')
            ->get()
            ->sortByDesc('total')
            ->map(fn ($row) => [
                'name' => $row->category?->name ?? 'Tanpa Kategori',
                'total' => $row->total,
                'percent' => $totalAssets > 0 ? round(($row->total / $totalAssets) * 100) : 0,
            ])
            ->values();

        // Kolom yang boleh dipakai sorting, dipetakan ke nama kolom SQL supaya tidak bisa disuntik lewat query string.
        $sortColumns = [
            'name' => 'assets.name',
            'brand' => 'assets.brand',
            'condition' => 'assets.condition',
            'status' => 'assets.status',
            'category' => 'asset_categories.name',
        ];
        $sort = array_key_exists($request->input('sort'), $sortColumns) ? $request->input('sort') : 'name';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $assetsQuery = $unit->assets()->with(['category', 'location']);
        if ($sort === 'category') {
            $assetsQuery->leftJoin('asset_categories', 'assets.asset_category_id', '=', 'asset_categories.id')->select('assets.*');
        }

        $assets = $assetsQuery
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('assets.name', 'like', "%{$search}%")
                        ->orWhere('assets.brand', 'like', "%{$search}%")
                        ->orWhere('assets.serial_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), fn ($q) => $q->where('assets.asset_category_id', $request->input('category_id')))
            ->when($request->filled('condition'), fn ($q) => $q->where('assets.condition', $request->input('condition')))
            ->when($request->filled('status'), fn ($q) => $q->where('assets.status', $request->input('status')))
            ->orderBy($sortColumns[$sort], $direction)
            ->get();

        return view('units.show', [
            'unit' => $unit,
            'assets' => $assets,
            'totalAssets' => $totalAssets,
            'totalValue' => $totalValue,
            'conditions' => $conditions,
            'categoryRecap' => $categoryRecap,
            'categories' => AssetCategory::orderBy('name')->get(),
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('units.form', [
            'unit' => null,
            'parentOptions' => Unit::orderBy('name')->get(),
            'userOptions' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['code'] = Unit::generateCode($data['type']);

        Unit::create($data);

        return redirect()->route('units.index')->with('message', "Unit baru berhasil ditambahkan dengan kode {$data['code']}.");
    }

    public function edit(Unit $unit): View
    {
        return view('units.form', [
            'unit' => $unit,
            'parentOptions' => Unit::where('id', '!=', $unit->id)->orderBy('name')->get(),
            'userOptions' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $data = $this->validated($request, $unit);
        // Kode tidak diubah setelah dibuat, supaya referensi lama (aset, kode BMN, dll) tetap valid.
        $unit->update($data);

        return redirect()->route('units.index')->with('message', 'Data unit berhasil diperbarui.');
    }

    public function toggleActive(Unit $unit): RedirectResponse
    {
        $unit->update(['is_active' => ! $unit->is_active]);

        $message = $unit->is_active ? 'Unit diaktifkan kembali.' : 'Unit dinonaktifkan.';

        return redirect()->route('units.index')->with('message', $message);
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $blockers = [];

        if ($unit->assets()->exists()) {
            $blockers[] = 'masih ada aset';
        }
        if ($unit->children()->exists()) {
            $blockers[] = 'masih punya sub-unit';
        }
        if ($unit->locations()->exists()) {
            $blockers[] = 'masih ada lokasi';
        }
        if ($unit->budgets()->exists()) {
            $blockers[] = 'masih ada data pagu anggaran';
        }
        if ($unit->users()->exists()) {
            $blockers[] = 'masih ada user terdaftar di unit ini';
        }
        if (\App\Models\AssetRequest::where('unit_id', $unit->id)->exists()) {
            $blockers[] = 'masih ada riwayat pengajuan';
        }

        if (! empty($blockers)) {
            return redirect()->route('units.index')
                ->with('error', "Tidak bisa menghapus {$unit->name}: ".implode(', ', $blockers).'. Nonaktifkan saja kalau sudah tidak dipakai.');
        }

        $unit->delete();

        return redirect()->route('units.index')->with('message', "{$unit->name} berhasil dihapus.");
    }

    private function validated(Request $request, ?Unit $unit = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:fakultas,departemen,program_studi,laboratorium,unit_kerja',
            'parent_id' => ['nullable', 'exists:units,id', Rule::notIn([$unit?->id])],
            'head_user_id' => 'nullable|exists:users,id',
        ]);
    }
}
