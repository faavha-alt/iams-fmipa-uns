<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Inventaris</div>
            <h1>Import Aset Massal</h1>
            <p>Upload banyak aset sekaligus lewat file CSV.</p>
        </div>
        <a href="{{ route('assets.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <div class="card__header"><h2 class="card__title">1. Download Template</h2></div>
        <p style="font-size: 0.87rem; color: var(--muted); margin-bottom: 14px;">
            Buka file ini di Excel/Google Sheets, isi data aset per baris, simpan/export lagi sebagai CSV, lalu upload di bawah.
        </p>
        <a href="{{ route('assets.import.template') }}" class="btn">⬇ Download Template CSV</a>

        <details class="review-panel" style="margin-top: 18px;">
            <summary>Lihat kode kategori, unit, lokasi & vendor yang tersedia</summary>
            <div class="review-panel__body">
                <div>
                    <p style="font-size: 0.82rem; font-weight: 600; margin-bottom: 8px;">Kode kategori:</p>
                    <p style="font-size: 0.8rem; color: var(--muted);">
                        @foreach ($categories as $c)
                            <span class="code-chip" style="margin: 2px;">{{ $c->code }}</span> {{ $c->name }}@if(!$loop->last) &nbsp;·&nbsp; @endif
                        @endforeach
                    </p>
                </div>
                <div style="margin-top: 14px;">
                    <p style="font-size: 0.82rem; font-weight: 600; margin-bottom: 8px;">Kode unit:</p>
                    <p style="font-size: 0.8rem; color: var(--muted);">
                        @foreach ($units as $u)
                            <span class="code-chip" style="margin: 2px;">{{ $u->code }}</span> {{ $u->name }}@if(!$loop->last) &nbsp;·&nbsp; @endif
                        @endforeach
                    </p>
                </div>
                <div style="margin-top: 14px;">
                    <p style="font-size: 0.82rem; font-weight: 600; margin-bottom: 8px;">Nama lokasi (isi persis sama di kolom lokasi_nama, harus sesuai unitnya):</p>
                    @forelse ($locationsByUnit as $unitName => $locs)
                        <p style="font-size: 0.8rem; margin-bottom: 4px;">
                            <strong>{{ $unitName }}:</strong>
                            <span style="color: var(--muted);">
                                @foreach ($locs as $loc)
                                    {{ $loc->name }}@if(!$loop->last) &nbsp;·&nbsp; @endif
                                @endforeach
                            </span>
                        </p>
                    @empty
                        <p style="font-size: 0.8rem; color: var(--muted);">Belum ada lokasi terdaftar — <a href="{{ route('locations.create') }}" target="_blank">tambah dulu di sini</a>.</p>
                    @endforelse
                </div>
                <div style="margin-top: 14px;">
                    <p style="font-size: 0.82rem; font-weight: 600; margin-bottom: 8px;">Nama vendor yang sudah terdaftar:</p>
                    <p style="font-size: 0.8rem; color: var(--muted);">
                        @forelse (\App\Models\Vendor::orderBy('name')->get() as $v)
                            {{ $v->name }}@if(!$loop->last) &nbsp;·&nbsp; @endif
                        @empty
                            Belum ada vendor terdaftar.
                        @endforelse
                    </p>
                    <p style="font-size: 0.78rem; color: var(--muted); margin-top: 6px;">Tidak perlu persis sama — kalau beda tulisan tipis (mis. "CV Risc" vs "CV. RISC Computer"), nanti ditawarkan buat dicocokkan di halaman kroscek sebelum tersimpan.</p>
                </div>
            </div>
        </details>
    </div>

    <div class="card">
        <div class="card__header"><h2 class="card__title">2. Upload File CSV</h2></div>

        <form method="POST" action="{{ route('assets.import.process') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>File CSV</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv">
                @error('file') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn">Lanjut ke Kroscek →</button>
        </form>

        <details class="review-panel" style="margin-top: 18px;">
            <summary>Lihat format kolom</summary>
            <div class="review-panel__body">
                <p><code>nama_aset, kategori_kode, unit_kode, lokasi_nama, merk, model, no_seri, tanggal_perolehan (YYYY-MM-DD), sumber_perolehan (pengadaan/hibah/bantuan/lainnya), nilai_perolehan, kondisi (baik/rusak_ringan/rusak_berat/hilang), status (aktif/dalam_perbaikan/dipinjamkan/dihapuskan), kode_barang_simak, vendor_nama, nomor_urut_simak</code></p>
                <p style="margin-top: 8px;"><code>lokasi_nama</code>, <code>vendor_nama</code>, <code>kode_barang_simak</code>, dan <code>nomor_urut_simak</code> boleh dikosongkan. Kode aset & QR Code dibuat otomatis, tidak perlu diisi di CSV.</p>
            </div>
        </details>
    </div>

    <div class="card" style="background: var(--sky-pale);">
        <p style="font-size: 0.85rem; margin: 0; color: var(--navy);">
            <strong>3. Kroscek &amp; Konfirmasi</strong> — setelah upload, belum langsung masuk database. Kamu akan diajak ke halaman kroscek buat lihat baris mana yang siap/error, dan buat vendor yang namanya mirip/baru bisa pilih mau dicocokkan ke yang sudah ada atau dibuatkan baru.
        </p>
    </div>

    @if (session('importErrors') && count(session('importErrors')) > 0)
        <div class="card" style="background: var(--danger-soft);">
            <div class="card__header"><h2 class="card__title" style="color: var(--danger);">Baris yang Gagal Diimport</h2></div>
            <ul style="font-size: 0.85rem; color: var(--danger); padding-left: 20px;">
                @foreach (session('importErrors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</x-layouts.app>
