<?php

namespace App\Providers;

use App\Models\AssetRequest;
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
    }
}