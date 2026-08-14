<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Unit;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Halaman depan publik — tanpa login. Statistik cuma jumlah/persentase, sengaja TIDAK
     * ada nilai rupiah, karena halaman ini bisa dibuka siapa saja.
     */
    public function home(): View
    {
        $stats = Cache::remember('public_home_stats', 300, function () {
            $totalAssets = Asset::count();

            $conditionCounts = Asset::select('condition')
                ->selectRaw('count(*) as total')
                ->groupBy('condition')
                ->pluck('total', 'condition');

            return [
                'totalAssets' => $totalAssets,
                'totalUnits' => Unit::count(),
                'totalLocations' => Location::count(),
                'totalCategories' => AssetCategory::count(),
                'goodConditionPercent' => $totalAssets > 0 ? round((($conditionCounts['baik'] ?? 0) / $totalAssets) * 100) : 0,
            ];
        });

        $announcements = Announcement::where('is_published', true)->latest()->take(3)->get();

        return view('public.home', [
            'stats' => $stats,
            'announcements' => $announcements,
        ]);
    }

    public function documentation(): View
    {
        return view('public.documentation');
    }
}
