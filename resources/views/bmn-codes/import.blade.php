<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Master Data</div>
            <h1>Import Kode BMN Massal</h1>
            <p>Upload daftar kode dan nama barang resmi SIMAK BMN sekaligus lewat CSV.</p>
        </div>
        <a href="{{ route('bmn-codes.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <div class="card__header"><h2 class="card__title">1. Download Template</h2></div>
        <p style="font-size: 0.87rem; color: var(--muted); margin-bottom: 14px;">
            Cuma 2 kolom: kode dan nama barang. Isi sebanyak apapun barisnya, lalu upload.
        </p>
        <a href="{{ route('bmn-codes.import.template') }}" class="btn">⬇ Download Template CSV</a>
    </div>

    <div class="card">
        <div class="card__header"><h2 class="card__title">2. Upload File CSV</h2></div>

        <form method="POST" action="{{ route('bmn-codes.import.process') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>File CSV</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv">
                @error('file') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn">Import Sekarang</button>
        </form>

        <div style="margin-top: 20px; font-size: 0.8rem; color: var(--muted);">
            <p style="font-weight: 600; margin-bottom: 6px;">Format kolom: <code>kode, nama</code></p>
            <p>Kalau kode sudah ada sebelumnya, namanya akan diperbarui (bukan dobel) — aman diupload berkali-kali.</p>
        </div>
    </div>

    @if (session('importErrors') && count(session('importErrors')) > 0)
        <div class="card" style="background: var(--danger-soft);">
            <div class="card__header"><h2 class="card__title" style="color: var(--danger);">Baris yang Gagal</h2></div>
            <ul style="font-size: 0.85rem; color: var(--danger); padding-left: 20px;">
                @foreach (session('importErrors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</x-layouts.app>
