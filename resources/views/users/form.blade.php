<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Administrasi</div>
            <h1>{{ $user ? 'Edit User' : 'Tambah User Baru' }}</h1>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ $user ? route('users.update', $user->id) : route('users.store') }}">
            @csrf
            @if ($user) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}">
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}">
                    @error('email') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>{{ $user ? 'Kata Sandi Baru (kosongkan jika tidak diubah)' : 'Kata Sandi' }}</label>
                    <input type="password" name="password">
                    @error('password') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role">
                        @foreach (['admin' => 'Admin', 'kepala_unit' => 'Kepala Unit', 'staff' => 'Staff', 'pimpinan' => 'Pimpinan'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $user->role ?? 'staff') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Unit / Prodi</label>
                    <select name="unit_id">
                        <option value="">-- Tidak terikat unit (misal admin fakultas) --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $user->unit_id ?? '') == $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Wajib diisi untuk role Kepala Unit/Staff — menentukan aset & pengajuan mana yang bisa mereka lihat.</p>
                </div>
                <div class="form-group">
                    <label>NIP (opsional)</label>
                    <input type="text" name="nip" value="{{ old('nip', $user->nip ?? '') }}">
                    @error('nip') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label>No. HP (opsional)</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
            </div>

            <button type="submit" class="btn">{{ $user ? 'Simpan Perubahan' : 'Tambah User' }}</button>
        </form>
    </div>
</x-layouts.app>
