<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Budget;
use App\Models\PurchaseRealization;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);

        $unitsCollection = Unit::whereIn('type', ['program_studi', 'laboratorium', 'departemen', 'unit_kerja'])
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $facultiesCollection = Unit::where('type', 'fakultas')->orderBy('name')->get();
        $facultyIds = $facultiesCollection->pluck('id')->all();

        $childUnits = $facultyIds ? Unit::whereIn('parent_id', $facultyIds)->get(['id', 'parent_id']) : collect();
        $childIdsByParent = $childUnits->groupBy('parent_id')->map(fn ($c) => $c->pluck('id')->all());

        // Semua unit yang butuh pagu/realisasi diambil sekaligus (bukan per-unit di dalam map())
        // supaya jumlah query tidak bertumbuh linear dengan jumlah prodi/fakultas.
        $allUnitIds = $unitsCollection->pluck('id')
            ->merge($facultiesCollection->pluck('id'))
            ->merge($childUnits->pluck('id'))
            ->unique()
            ->values()
            ->all();

        $paguMap = $this->paguMap($allUnitIds, $year);
        $realisasiMap = $this->realisasiMap($allUnitIds, $year);

        $units = $unitsCollection->map(fn (Unit $unit) => $this->buildUnitRow($unit, $paguMap, $realisasiMap));

        $faculties = $facultiesCollection->map(function (Unit $fakultas) use ($paguMap, $realisasiMap, $childIdsByParent) {
            $childIds = $childIdsByParent[$fakultas->id] ?? [];

            // Pagu total fakultas = alokasi yang dipegang fakultas sendiri (belanja langsung)
            // + jumlah alokasi semua prodi di bawahnya. Bukan satu angka plafon dikurangi alokasi
            // prodi — jadi tidak ada lagi konsep "sisa belum dialokasikan" yang bisa minus.
            $alokasiFakultas = $paguMap[$fakultas->id] ?? 0;
            $alokasiProdi = collect($childIds)->sum(fn ($id) => $paguMap[$id] ?? 0);
            $paguTotal = $alokasiFakultas + $alokasiProdi;

            $realisasiSendiri = $realisasiMap[$fakultas->id] ?? 0;
            $realisasiAnak = collect($childIds)->sum(fn ($id) => $realisasiMap[$id] ?? 0);
            $totalRealisasi = $realisasiSendiri + $realisasiAnak;

            return [
                'unit' => $fakultas,
                'alokasi_fakultas' => $alokasiFakultas,
                'alokasi_prodi' => $alokasiProdi,
                'pagu_total' => $paguTotal,
                'realisasi_sendiri' => $realisasiSendiri,
                'realisasi_anak' => $realisasiAnak,
                'total_realisasi' => $totalRealisasi,
                'sisa_riil' => $paguTotal - $totalRealisasi,
                'over_realisasi' => $totalRealisasi > $paguTotal,
                'percent_alokasi_prodi' => $paguTotal > 0 ? round(($alokasiProdi / $paguTotal) * 100) : 0,
                'percent_realisasi' => $paguTotal > 0 ? min(100, round(($totalRealisasi / $paguTotal) * 100)) : 0,
            ];
        });

        $availableYears = range(now()->year + 1, now()->year - 3);

        // Total pagu/realisasi = semua alokasi (prodi/unit + fakultas), tanpa dobel hitung.
        $allBudgetUnitIds = $unitsCollection->pluck('id')->merge($facultiesCollection->pluck('id'))->unique();

        return view('budgets.index', [
            'units' => $units,
            'faculties' => $faculties,
            'year' => $year,
            'availableYears' => $availableYears,
            'totalPagu' => $allBudgetUnitIds->sum(fn ($id) => $paguMap[$id] ?? 0),
            'totalRealisasi' => $allBudgetUnitIds->sum(fn ($id) => $realisasiMap[$id] ?? 0),
        ]);
    }

    public function store(Request $request, Unit $unit): RedirectResponse
    {
        $data = $request->validate([
            'fiscal_year' => 'required|integer|min:2020|max:2100',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        Budget::updateOrCreate(
            ['unit_id' => $unit->id, 'fiscal_year' => $data['fiscal_year']],
            ['amount' => $data['amount'], 'notes' => $data['notes'] ?? null, 'created_by' => $request->user()->id]
        );

        return redirect()->route('budgets.index', ['year' => $data['fiscal_year']])
            ->with('message', "Pagu {$unit->name} tahun {$data['fiscal_year']} berhasil disimpan.");
    }

    public function show(Request $request, Unit $unit): View
    {
        $year = (int) $request->input('year', now()->year);
        $yearRange = ["{$year}-01-01", "{$year}-12-31"];

        $isFakultas = $unit->type === 'fakultas';

        $childUnits = $isFakultas
            ? Unit::where('parent_id', $unit->id)->orderBy('name')->get()
            : collect();

        // Untuk fakultas, daftar aset/realisasi/pengajuan dikumpulkan dari fakultas + semua prodi
        // di bawahnya. Kalau cuma difilter unit_id fakultas sendiri, listnya hampir selalu kosong
        // karena aset/realisasi nyaris selalu menempel di prodi, bukan di unit fakultas.
        $scopeUnitIds = collect([$unit->id])->merge($childUnits->pluck('id'))->unique()->values()->all();

        $pagu = Budget::where('unit_id', $unit->id)->where('fiscal_year', $year)->value('amount') ?? 0;

        $assets = Asset::whereIn('unit_id', $scopeUnitIds)
            ->whereBetween('acquisition_date', $yearRange)
            ->with(['category', 'unit'])
            ->latest('acquisition_date')
            ->get();

        $realizations = PurchaseRealization::whereIn('unit_id', $scopeUnitIds)
            ->whereBetween('purchase_date', $yearRange)
            ->with(['category', 'unit'])
            ->latest('purchase_date')
            ->get();

        $requests = \App\Models\AssetRequest::whereIn('unit_id', $scopeUnitIds)
            ->whereBetween('created_at', ["{$year}-01-01 00:00:00", "{$year}-12-31 23:59:59"])
            ->with(['category', 'unit'])
            ->latest()
            ->get();

        $realisasiAset = $assets->sum('acquisition_value');
        $realisasiBelumFinal = $realizations->where('status', 'belum_final')->sum('cost');
        $totalRealisasi = $realisasiAset + $realisasiBelumFinal;

        $children = collect();
        $recap = null;
        $paguTotal = $pagu; // prodi/unit biasa: pagu unit itu sendiri
        if ($isFakultas) {
            $childIds = $childUnits->pluck('id')->all();
            $childPaguMap = $this->paguMap($childIds, $year);
            $childRealisasiMap = $this->realisasiMap($childIds, $year);

            $children = $childUnits->map(fn (Unit $child) => $this->buildUnitRow($child, $childPaguMap, $childRealisasiMap));

            // $pagu di sini = alokasi fakultas (belanja langsung). Pagu total fakultas = alokasi
            // fakultas + jumlah alokasi semua prodi. Realisasi dipisah: langsung-fakultas vs prodi.
            $ownAssets = $assets->where('unit_id', $unit->id);
            $ownRealizations = $realizations->where('unit_id', $unit->id);
            $realisasiSendiri = $ownAssets->sum('acquisition_value')
                + $ownRealizations->where('status', 'belum_final')->sum('cost');
            $alokasiProdi = collect($childPaguMap)->sum();
            $paguTotal = $pagu + $alokasiProdi;

            $recap = [
                'alokasi_fakultas' => $pagu,
                'alokasi_prodi' => $alokasiProdi,
                'pagu_total' => $paguTotal,
                'realisasi_sendiri' => $realisasiSendiri,
                'realisasi_prodi' => $totalRealisasi - $realisasiSendiri,
                'aset_sendiri_count' => $ownAssets->count(),
                'realisasi_sendiri_count' => $ownRealizations->count(),
            ];
        }

        return view('budgets.show', [
            'unit' => $unit,
            'year' => $year,
            'isFakultas' => $isFakultas,
            'availableYears' => range(now()->year + 1, now()->year - 3),
            'pagu' => $paguTotal,
            'realisasiAset' => $realisasiAset,
            'realisasiBelumFinal' => $realisasiBelumFinal,
            'totalRealisasi' => $totalRealisasi,
            'sisa' => $paguTotal - $totalRealisasi,
            'assets' => $assets,
            'realizations' => $realizations,
            'requests' => $requests,
            'children' => $children,
            'recap' => $recap,
        ]);
    }

    /** Pagu tiap unit (tahun tertentu), diambil sekaligus untuk banyak unit sekaligus. Key: unit_id. */
    private function paguMap(array $unitIds, int $year): array
    {
        if (empty($unitIds)) {
            return [];
        }

        return Budget::whereIn('unit_id', $unitIds)->where('fiscal_year', $year)->pluck('amount', 'unit_id')->all();
    }

    /** Realisasi (aset resmi + realisasi belum-final) tiap unit, diambil sekaligus. Key: unit_id. */
    private function realisasiMap(array $unitIds, int $year): array
    {
        if (empty($unitIds)) {
            return [];
        }

        $yearRange = ["{$year}-01-01", "{$year}-12-31"];

        $assetSums = Asset::whereIn('unit_id', $unitIds)
            ->whereBetween('acquisition_date', $yearRange)
            ->selectRaw('unit_id, SUM(acquisition_value) as total')
            ->groupBy('unit_id')
            ->pluck('total', 'unit_id');

        $realizationSums = PurchaseRealization::whereIn('unit_id', $unitIds)
            ->where('status', 'belum_final')
            ->whereBetween('purchase_date', $yearRange)
            ->selectRaw('unit_id, SUM(cost) as total')
            ->groupBy('unit_id')
            ->pluck('total', 'unit_id');

        $map = [];
        foreach ($unitIds as $id) {
            $map[$id] = (float) ($assetSums[$id] ?? 0) + (float) ($realizationSums[$id] ?? 0);
        }

        return $map;
    }

    private function buildUnitRow(Unit $unit, array $paguMap, array $realisasiMap): array
    {
        $pagu = $paguMap[$unit->id] ?? 0;
        $realisasi = $realisasiMap[$unit->id] ?? 0;
        $sisa = $pagu - $realisasi;

        return [
            'unit' => $unit,
            'pagu' => $pagu,
            'realisasi' => $realisasi,
            'sisa' => $sisa,
            'percent' => $pagu > 0 ? min(100, round(($realisasi / $pagu) * 100)) : 0,
            'over_budget' => $sisa < 0,
        ];
    }
}
