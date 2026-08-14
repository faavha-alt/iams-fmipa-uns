<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Situs Publik</div>
            <h1>{{ $announcement ? 'Edit Pengumuman' : 'Tulis Pengumuman Baru' }}</h1>
        </div>
        <a href="{{ route('announcements.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ $announcement ? route('announcements.update', $announcement->id) : route('announcements.store') }}">
            @csrf
            @if ($announcement) @method('PUT') @endif

            <div class="form-group">
                <label>Judul</label>
                <input type="text" name="title" value="{{ old('title', $announcement?->title ?? '') }}" autofocus>
                @error('title') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Isi Pengumuman</label>
                <textarea name="body" rows="8">{{ old('body', $announcement?->body ?? '') }}</textarea>
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Ganti baris di sini akan tampil apa adanya di halaman publik (tanpa perlu format HTML/Markdown).</p>
                @error('body') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group" style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="is_published" id="is_published" style="width:auto;" @checked(old('is_published', $announcement?->is_published ?? true))>
                <label for="is_published" style="margin:0; font-weight:400;">Tampilkan ke publik sekarang</label>
            </div>
            <p style="font-size: 0.78rem; color: var(--muted); margin: -8px 0 16px;">Kalau tidak dicentang, pengumuman disimpan sebagai draft — bisa ditampilkan belakangan lewat tombol "Tampilkan" di daftar pengumuman.</p>

            <button type="submit" class="btn">{{ $announcement ? 'Simpan Perubahan' : 'Publikasikan' }}</button>
        </form>
    </div>
</x-layouts.app>
