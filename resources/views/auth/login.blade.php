<x-layouts.guest>
    <div class="guest-panel__inner">
        <h2>Masuk</h2>
        <p class="subtitle">Gunakan akun yang terdaftar untuk unit/prodi Anda.</p>

        <form method="POST" action="{{ route('login.authenticate') }}">
            @csrf

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" autofocus>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Kata Sandi</label>
                <input type="password" name="password">
            </div>

            <div class="form-group" style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="remember" id="remember" style="width:auto;">
                <label for="remember" style="margin:0; font-weight:400;">Ingat saya</label>
            </div>

            <button type="submit" class="btn" style="width:100%; justify-content:center;">Masuk</button>
        </form>

        <div style="display:flex; align-items:center; gap:10px; margin:18px 0; color: var(--muted); font-size:12px;">
            <div style="flex:1; height:1px; background:rgba(0,0,0,0.08);"></div>
            atau
            <div style="flex:1; height:1px; background:rgba(0,0,0,0.08);"></div>
        </div>

        <a href="{{ route('auth.google.redirect') }}" class="btn btn-outline" style="width:100%; justify-content:center; gap:8px;">
            <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true">
                <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84c-.21 1.13-.84 2.09-1.8 2.73v2.27h2.9c1.7-1.57 2.7-3.87 2.7-6.64z"/>
                <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.27c-.81.54-1.85.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.34C2.47 15.98 5.48 18 9 18z"/>
                <path fill="#FBBC05" d="M3.95 10.69A5.4 5.4 0 0 1 3.66 9c0-.59.1-1.16.29-1.69V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.34z"/>
                <path fill="#EA4335" d="M9 3.58c1.32 0 2.51.46 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0 5.48 0 2.47 2.02.98 4.97l2.97 2.34C4.66 5.17 6.65 3.58 9 3.58z"/>
            </svg>
            Masuk dengan Google
        </a>
    </div>
</x-layouts.guest>
