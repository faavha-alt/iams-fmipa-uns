<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse|View
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        }

        if (! $user) {
            $email = strtolower(trim($googleUser->getEmail() ?? ''));
            $raw = $googleUser->getRaw() ?: $googleUser->user ?: [];
            $verified = filter_var($raw['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // Hanya pendaftaran mandiri (user baru) yang dibatasi. Email yang sudah terdaftar
            // oleh admin, atau user yang tinggal menautkan google_id, tidak lewat sini.
            if (! $verified) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Akun Google belum memverifikasi email. Gunakan email yang sudah terverifikasi.']);
            }

            if (! $this->isInstitutionalEmail($email)) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Pendaftaran mandiri hanya untuk email institusi (uns.ac.id). Hubungi admin jika perlu akun.']);
            }

            $user = new User([
                'name' => $googleUser->getName() ?: $email,
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(40)),
            ]);
            // Kolom sensitif di-set eksplisit (tidak di $fillable) — user baru dari Google
            // selalu role staff, aktif, menunggu persetujuan admin.
            $user->role = 'staff';
            $user->is_active = true;
            $user->is_approved = false;
            $user->save();
        }

        if (! $user->is_active) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi admin.']);
        }

        if (! $user->is_approved) {
            return view('auth.pending', ['user' => $user]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('assets.index'));
    }

    /**
     * Apakah email termasuk domain institusi kampus (mengizinkan subdomain student.*).
     */
    private function isInstitutionalEmail(string $email): bool
    {
        return (bool) preg_match('/@(?:student\.)?uns\.ac\.id$/i', trim($email));
    }
}
