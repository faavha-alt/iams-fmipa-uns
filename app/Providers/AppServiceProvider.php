<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetRequest;
use App\Models\Location;
use App\Models\Unit;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Authenticate::redirectUsing(fn () => route('login'));
        Paginator::defaultView('vendor.pagination.custom');

        // Sidebar badge query dipindah dari layout ke composer + cache 60s (dipanggil di
        // setiap page load admin sebelumnya) — lihat App\Http\Controllers\AssetRequestController
        // untuk Cache::forget() yang menjaga badge tetap akurat setelah pengajuan baru/diputuskan.
        View::composer('components.layouts.app', function ($view) {
            $user = auth()->user();

            $view->with('pendingRequestCount', $user && $user->role === 'admin'
                ? Cache::remember('pending_request_count', 60, fn () => AssetRequest::where('status', 'diajukan')->count())
                : 0);
        });

        // Statistik ringkas buat halaman login/pending (publik, belum login) — sengaja cuma
        // jumlah/persentase, TIDAK ada nilai rupiah, biar aman ditampilkan ke siapa saja yang buka
        // halaman depan. Di-cache 5 menit karena halaman ini bisa sering dibuka tanpa login.
        View::composer('components.layouts.guest', function ($view) {
            $view->with('publicStats', Cache::remember('guest_public_stats', 300, function () {
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
            }));
        });
    }
}