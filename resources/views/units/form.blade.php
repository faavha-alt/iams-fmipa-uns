<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Administrasi</div>
            <h1>{{ $unit ? 'Edit Unit' : 'Tambah Unit Baru' }}</h1>
        </div>
        <a href="{{ route('units.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ $unit ? route('units.update', $unit->id) : route('units.store') }}">
            @csrf
            @if ($unit) @method('PUT') @endif

            <div class="form-row">
                @if ($unit)
                    <div class="form-group">
                        <label>Kode Unit</label>
                        <input type="text" value="{{ $unit->code }}" disabled style="background: var(--surface-sunken); color: var(--muted);">
                        <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Kode tidak bisa diubah setelah dibuat.</p>
                    </div>
                @endif
                <div class="form-group">
                    <label>Nama Unit</label>
                    <input type="text" name="name" value="{{ old('name', $unit->name ?? '') }}" placeholder="Contoh: Program Studi Informatika">
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            @unless ($unit)
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: -8px; margin-bottom: 16px;">Kode unit akan dibuat otomatis mengikuti tipe & urutan (contoh: PRODI-006).</p>
            @endunless

            <div class="form-row">
                <div class="form-group">
                    <label>Tipe</label>
                    <select name="type">
                        @foreach (['fakultas' => 'Fakultas', 'departemen' => 'Departemen', 'program_studi' => 'Program Studi', 'laboratorium' => 'Laboratorium', 'unit_kerja' => 'Unit Kerja'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $unit->type ?? 'program_studi') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Unit Induk (opsional)</label>
                    <select name="parent_id">
                        <option value="">-- Tidak ada (unit tertinggi) --</option>
                        @foreach ($parentOptions as $option)
                            <option value="{{ $option->id }}" @selected(old('parent_id', $unit->parent_id ?? '') == $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                    @error('parent_id') <div class="form-error">{{ $message }}</div> @enderror
                    <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Contoh: Laboratorium Fisika Dasar induknya Program Studi Fisika.</p>
                </div>
            </div>

            <div class="form-group">
                <label>Kepala Unit (opsional)</label>
                <select name="head_user_id">
                    <option value="">-- Belum ditentukan --</option>
                    @foreach ($userOptions as $option)
                        <option value="{{ $option->id }}" @selected(old('head_user_id', $unit->head_user_id ?? '') == $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Muncul sebagai penanggung jawab unit ini di laporan.</p>
            </div>

            <button type="submit" class="btn">{{ $unit ? 'Simpan Perubahan' : 'Tambah Unit' }}</button>
        </form>
    </div>
</x-layouts.app>
