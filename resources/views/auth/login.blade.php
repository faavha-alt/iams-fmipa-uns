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
    </div>
</x-layouts.guest>
