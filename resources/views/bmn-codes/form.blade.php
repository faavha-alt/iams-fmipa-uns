<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Master Data</div>
            <h1>{{ $code ? 'Edit Kode BMN' : 'Tambah Kode BMN' }}</h1>
        </div>
        <a href="{{ route('bmn-codes.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ $code ? route('bmn-codes.update', $code->id) : route('bmn-codes.store') }}">
            @csrf
            @if ($code) @method('PUT') @endif

            <div class="form-group">
                <label>Kode BMN</label>
                <input type="text" name="kode" value="{{ old('kode', $code->kode ?? '') }}" placeholder="Contoh: 3.05.02.01.001">
                @error('kode') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="nama" value="{{ old('nama', $code->nama ?? '') }}" placeholder="Contoh: Kursi Besi/Metal">
                @error('nama') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn">{{ $code ? 'Simpan Perubahan' : 'Tambah Kode' }}</button>
        </form>
    </div>
</x-layouts.app>
