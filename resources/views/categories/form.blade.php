<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Master Data</div>
            <h1>{{ $category ? 'Edit Kategori' : 'Tambah Kategori Baru' }}</h1>
        </div>
        <a href="{{ route('categories.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ $category ? route('categories.update', $category->id) : route('categories.store') }}">
            @csrf
            @if ($category) @method('PUT') @endif

            <div class="form-row">
                @if ($category)
                    <div class="form-group">
                        <label>Kode</label>
                        <input type="text" value="{{ $category->code }}" disabled style="background: var(--surface-sunken); color: var(--muted);">
                    </div>
                @endif
                <div class="form-group">
                    <label>Nama Kategori</label>
                    <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" placeholder="Contoh: Laptop, Kursi Kuliah">
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
            @unless ($category)
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: -8px; margin-bottom: 16px;">Kode dibuat otomatis.</p>
            @endunless

            <div class="form-row">
                <div class="form-group">
                    <label>Kategori Induk (opsional)</label>
                    <select name="parent_id">
                        <option value="">-- Kategori utama (tidak punya induk) --</option>
                        @foreach ($parentOptions as $option)
                            <option value="{{ $option->id }}" @selected(old('parent_id', $category->parent_id ?? '') == $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                    <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Contoh: "Laptop" induknya "Elektronik".</p>
                </div>
                <div class="form-group">
                    <label>Satuan Barang</label>
                    <input type="text" name="unit_satuan" value="{{ old('unit_satuan', $category->unit_satuan ?? '') }}" placeholder="Unit, Buah, Set, Paket, Meter...">
                    @error('unit_satuan') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label>Masa Manfaat (tahun, opsional)</label>
                <input type="number" name="useful_life_years" value="{{ old('useful_life_years', $category->useful_life_years ?? '') }}" style="max-width: 160px;" placeholder="Contoh: 4">
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Untuk perhitungan penyusutan nilai aset di masa depan.</p>
            </div>

            <div class="form-group">
                <label>Spesifikasi Standar (opsional)</label>
                <textarea name="specification" rows="3" placeholder="Contoh: RAM min 8GB, prosesor Intel i5 ke atas">{{ old('specification', $category->specification ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn">{{ $category ? 'Simpan Perubahan' : 'Tambah Kategori' }}</button>
        </form>
    </div>
</x-layouts.app>
