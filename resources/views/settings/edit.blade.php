<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Pengaturan</div>
            <h1>Kop Surat Dokumen</h1>
            <p>Dipakai di header cetakan BAST.</p>
        </div>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Gambar Kop Surat (opsional)</label>
                @if ($kopLogo)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ asset('storage/'.$kopLogo) }}" alt="Kop surat" style="max-width: 100%; max-height: 140px; border: 1px solid var(--border); border-radius: 8px; padding: 8px;">
                        <label style="display:flex; align-items:center; gap:6px; margin-top: 8px; font-weight: 400;">
                            <input type="checkbox" name="remove_logo" value="1" style="width:auto;"> Hapus gambar ini (pakai teks biasa lagi)
                        </label>
                    </div>
                @endif
                <input type="file" name="bast_kop_logo" accept=".jpg,.jpeg,.png">
                @error('bast_kop_logo') <div class="form-error">{{ $message }}</div> @enderror
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Kalau diisi, gambar ini yang tampil sebagai kop surat (misal scan kop resmi FMIPA UNS dengan logo). Kalau tidak diisi, dipakai teks di bawah ini.</p>
            </div>

            <div class="form-group">
                <label>Baris 1 (dipakai kalau tidak ada gambar kop)</label>
                <input type="text" name="bast_kop_baris1" value="{{ old('bast_kop_baris1', $kopBaris1) }}">
                @error('bast_kop_baris1') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Baris 2 (opsional)</label>
                <input type="text" name="bast_kop_baris2" value="{{ old('bast_kop_baris2', $kopBaris2) }}">
            </div>

            <button type="submit" class="btn">Simpan</button>
        </form>
    </div>
</x-layouts.app>
