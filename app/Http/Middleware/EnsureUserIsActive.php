<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pastikan user yang sedang login masih berstatus aktif & disetujui pada SETIAP request,
 * bukan cuma saat login. Kalau admin menonaktifkan (is_active=false) atau mencabut
 * persetujuan (is_approved=false) seorang user di tengah sesi, sesi langsung dibatalkan
 * dan user dialihkan ke halaman login — tidak bisa melanjutkan akses.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (! $user->is_active || ! $user->is_approved)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi admin.']);
        }

        return $next($request);
    }
}
