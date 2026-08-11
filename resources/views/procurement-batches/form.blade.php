<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Pengadaan</div>
            <h1>{{ $batch ? 'Edit Pengadaan' : 'Buat Pengadaan Baru' }}</h1>
        </div>
        <a href="{{ route('procurement-batches.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ $batch ? route('procurement-batches.update', $batch->id) : route('procurement-batches.store') }}">
            @csrf
            @if ($batch) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group">
                    <label>Nama Pengadaan</label>
                    <input type="text" name="nama" value="{{ old('nama', $batch->nama ?? '') }}" placeholder="Contoh: Pengadaan Alat Lab - CV Maju Jaya">
                    @error('nama') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Vendor</label>
                    <select name="vendor_id">
                        <option value="">-- Pilih Vendor --</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}" @selected(old('vendor_id', $batch->vendor_id ?? '') == $v->id)>{{ $v->name }}</option>
                        @endforeach
                    </select>
                    @error('vendor_id') <div class="form-error">{{ $message }}</div> @enderror
                    <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Belum ada vendornya? <a href="{{ route('vendors.create') }}" target="_blank">Tambah vendor baru →</a></p>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Tanggal Mulai (opsional)</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $batch?->tanggal_mulai?->format('Y-m-d') ?? '') }}">
                </div>
                <div class="form-group">
                    <label>Tanggal Selesai (opsional)</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $batch?->tanggal_selesai?->format('Y-m-d') ?? '') }}">
                    @error('tanggal_selesai') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nomor Dokumen (opsional)</label>
                    <input type="text" name="nomor_dokumen" value="{{ old('nomor_dokumen', $batch->nomor_dokumen ?? '') }}" placeholder="Diisi nanti kalau sudah ada nomor surat pengadaan resmi">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="berjalan" @selected(old('status', $batch->status ?? 'berjalan') == 'berjalan')>Berjalan</option>
                        <option value="selesai" @selected(old('status', $batch->status ?? 'berjalan') == 'selesai')>Selesai</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="catatan" rows="3">{{ old('catatan', $batch->catatan ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn">{{ $batch ? 'Simpan Perubahan' : 'Buat Pengadaan' }}</button>
        </form>
    </div>
</x-layouts.app>
