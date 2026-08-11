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

        $units = Unit::whereIn('type', ['program_studi', 'laboratorium', 'departemen', 'unit_kerja'])
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(fn (Unit $unit) => $this->buildUnitRow($unit, $year));

        $faculties = Unit::where('type', 'fakultas')
            ->orderBy('name')
            ->get()
            ->map(function (Unit $fakultas) use ($year) {
                $childIds = Unit::where('parent_id', $fakultas->id)->pluck('id');

                $pagu = Budget::where('unit_id', $fakultas->id)->where('fiscal_year', $year)->value('amount') ?? 0;
                $dialokasikan = Budget::whereIn('unit_id', $childIds)->where('fiscal_year', $year)->sum('amount');
                $realisasiSendiri = $this->hitungRealisasi($fakultas->id, $year);
                $realisasiAnak = Asset::whereIn('unit_id', $childIds)->whereYear('acquisition_date', $year)->sum('acquisition_value')
                    + PurchaseRealization::whereIn('unit_id', $childIds)->where('status', 'belum_final')->whereYear('purchase_date', $year)->sum('cost');
                $totalRealisasi = $realisasiSendiri + $realisasiAnak;

                return [
                    'unit' => $fakultas,
                    'pagu' => $pagu,
                    'dialokasikan' => $dialokasikan,
                    'sisa_alokasi' => $pagu - $dialokasikan,
                    'over_alokasi' => $dialokasikan > $pagu,
                    'realisasi_sendiri' => $realisasiSendiri,
                    'realisasi_anak' => $realisasiAnak,
                    'total_realisasi' => $totalRealisasi,
                    'sisa_riil' => $pagu - $totalRealisasi,
                    'over_realisasi' => $totalRealisasi > $pagu,
                    'percent_alokasi' => $pagu > 0 ? min(100, round(($dialokasikan / $pagu) * 100)) : 0,
                    'percent_realisasi' => $pagu > 0 ? min(100, round(($totalRealisasi / $pagu) * 100)) : 0,
                ];
            });

        $availableYears = range(now()->year + 1, now()->year - 3);

        return view('budgets.index', [
            'units' => $units,
            'faculties' => $faculties,
            'year' => $year,
            'availableYears' => $availableYears,
            'totalPagu' => $units->sum('pagu'),
            'totalRealisasi' => $units->sum('realisasi'),
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

        $warning = null;
        if ($unit->parent_id) {
            $fakultasPagu = Budget::where('unit_id', $unit->parent_id)->where('fiscal_year', $data['fiscal_year'])->value('amount') ?? 0;
            $siblingIds = Unit::where('parent_id', $unit->parent_id)->pluck('id');
            $totalAlokasi = Budget::whereIn('unit_id', $siblingIds)->where('fiscal_year', $data['fiscal_year'])->sum('amount');

            if ($fakultasPagu > 0 && $totalAlokasi > $fakultasPagu) {
                $warning = 'Perhatian: total pagu semua prodi sekarang ('.number_format($totalAlokasi, 0, ',', '.').') sudah melebihi pagu fakultas ('.number_format($fakultasPagu, 0, ',', '.').').';
            }
        }

        $redirect = redirect()->route('budgets.index', ['year' => $data['fiscal_year']])
            ->with('message', "Pagu {$unit->name} tahun {$data['fiscal_year']} berhasil disimpan.");

        if ($warning) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    public function show(Request $request, Unit $unit): View
    {
        $year = (int) $request->input('year', now()->year);

        $pagu = Budget::where('unit_id', $unit->id)->where('fiscal_year', $year)->value('amount') ?? 0;

        $assets = Asset::where('unit_id', $unit->id)
            ->whereYear('acquisition_date', $year)
            ->with('category')
            ->latest('acquisition_date')
            ->get();

        $realizations = PurchaseRealization::where('unit_id', $unit->id)
            ->whereYear('purchase_date', $year)
            ->with('category')
            ->latest('purchase_date')
            ->get();

        $requests = \App\Models\AssetRequest::where('unit_id', $unit->id)
            ->whereYear('created_at', $year)
            ->with('category')
            ->latest()
            ->get();

        $realisasiAset = $assets->sum('acquisition_value');
        $realisasiBelumFinal = $realizations->where('status', 'belum_final')->sum('cost');
        $totalRealisasi = $realisasiAset + $realisasiBelumFinal;

        $children = collect();
        if ($unit->type === 'fakultas') {
            $children = Unit::where('parent_id', $unit->id)
                ->orderBy('name')
                ->get()
                ->map(fn (Unit $child) => $this->buildUnitRow($child, $year));
        }

        return view('budgets.show', [
            'unit' => $unit,
            'year' => $year,
            'availableYears' => range(now()->year + 1, now()->year - 3),
            'pagu' => $pagu,
            'realisasiAset' => $realisasiAset,
            'realisasiBelumFinal' => $realisasiBelumFinal,
            'totalRealisasi' => $totalRealisasi,
            'sisa' => $pagu - $totalRealisasi,
            'assets' => $assets,
            'realizations' => $realizations,
            'requests' => $requests,
            'children' => $children,
        ]);
    }

    private function hitungRealisasi(int $unitId, int $year): float
    {
        $realisasiAset = Asset::where('unit_id', $unitId)->whereYear('acquisition_date', $year)->sum('acquisition_value');
        $realisasiBelumFinal = PurchaseRealization::where('unit_id', $unitId)->where('status', 'belum_final')->whereYear('purchase_date', $year)->sum('cost');

        return $realisasiAset + $realisasiBelumFinal;
    }

    private function buildUnitRow(Unit $unit, int $year): array
    {
        $pagu = Budget::where('unit_id', $unit->id)->where('fiscal_year', $year)->value('amount') ?? 0;
        $realisasi = $this->hitungRealisasi($unit->id, $year);
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
