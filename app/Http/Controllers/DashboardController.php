<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';

        $assetBase = Asset::query()->when(! $isAdmin, fn ($q) => $q->where('unit_id', $user->unit_id));
        $requestBase = AssetRequest::query()->when(! $isAdmin, fn ($q) => $q->where('unit_id', $user->unit_id));

        $totalAssets = (clone $assetBase)->count();
        $totalValue = (clone $assetBase)->sum('acquisition_value');

        $conditionCounts = (clone $assetBase)
            ->select('condition')
            ->selectRaw('count(*) as total')
            ->groupBy('condition')
            ->pluck('total', 'condition');

        $statusCounts = (clone $assetBase)
            ->select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $conditions = collect(['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])
            ->map(fn ($key) => [
                'key' => $key,
                'total' => $conditionCounts[$key] ?? 0,
                'percent' => $totalAssets > 0 ? round((($conditionCounts[$key] ?? 0) / $totalAssets) * 100) : 0,
            ]);

        $statuses = collect(['aktif', 'dalam_perbaikan', 'dipinjamkan', 'dihapuskan'])
            ->map(fn ($key) => [
                'key' => $key,
                'total' => $statusCounts[$key] ?? 0,
                'percent' => $totalAssets > 0 ? round((($statusCounts[$key] ?? 0) / $totalAssets) * 100) : 0,
            ]);

        $unitBreakdown = collect();
        if ($isAdmin) {
            $unitBreakdown = Unit::withCount('assets')
                ->whereHas('assets')
                ->orderByDesc('assets_count')
                ->take(6)
                ->get()
                ->map(fn ($unit) => [
                    'name' => $unit->name,
                    'total' => $unit->assets_count,
                    'percent' => $totalAssets > 0 ? round(($unit->assets_count / $totalAssets) * 100) : 0,
                ]);
        }

        $pendingRequests = (clone $requestBase)->where('status', 'diajukan')->count();

        $budgetSummary = null;
        if ($isAdmin) {
            $year = now()->year;
            $yearRange = ["{$year}-01-01", "{$year}-12-31"];
            $totalPagu = \App\Models\Budget::whereHas('unit', fn ($q) => $q->where('type', 'fakultas'))
                ->where('fiscal_year', $year)
                ->sum('amount');
            $totalRealisasiAset = (clone $assetBase)->whereBetween('acquisition_date', $yearRange)->sum('acquisition_value');
            $totalRealisasiBelumFinal = \App\Models\PurchaseRealization::where('status', 'belum_final')->whereBetween('purchase_date', $yearRange)->sum('cost');
            $totalRealisasi = $totalRealisasiAset + $totalRealisasiBelumFinal;
            $budgetSummary = [
                'pagu' => $totalPagu,
                'realisasi' => $totalRealisasi,
                'sisa' => $totalPagu - $totalRealisasi,
            ];
        }

        $recentAssets = (clone $assetBase)->with(['category', 'unit'])->latest()->take(5)->get();
        $recentRequests = (clone $requestBase)->with(['unit', 'requestedBy'])->latest()->take(5)->get();

        return view('dashboard', [
            'isAdmin' => $isAdmin,
            'totalAssets' => $totalAssets,
            'totalValue' => $totalValue,
            'conditions' => $conditions,
            'statuses' => $statuses,
            'unitBreakdown' => $unitBreakdown,
            'pendingRequests' => $pendingRequests,
            'budgetSummary' => $budgetSummary,
            'recentAssets' => $recentAssets,
            'recentRequests' => $recentRequests,
        ]);
    }
}
