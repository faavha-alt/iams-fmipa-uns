<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Keuangan</div>
            <h1>Finalisasi jadi Aset</h1>
            <p>Lengkapi detail supaya "{{ $realization->item_name }}" tercatat resmi sebagai aset dengan kode & QR Code.</p>
        </div>
        <a href="{{ route('realizations.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card" style="background: var(--sky-pale); margin-bottom: 16px;">
        <strong>{{ $realization->item_name }}</strong> — {{ $realization->quantity }}x, total Rp {{ number_format($realization->cost, 0, ',', '.') }}
        (Rp {{ number_format($realization->cost / max(1, $realization->quantity), 0, ',', '.') }}/unit)
        <br>
        <span style="font-size: 0.85rem; color: var(--muted);">Unit: {{ $realization->unit->name }} · Tanggal: {{ $realization->purchase_date->format('d M Y') }}</span>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('realizations.finalize', $realization->id) }}">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label>Kategori Aset</label>
                    <select name="asset_category_id">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('asset_category_id', $realization->asset_category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('asset_category_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Lokasi</label>
                    @if ($locations->count() === 0)
                        <select name="location_id" disabled>
                            <option>-- Unit ini belum punya lokasi terdaftar --</option>
                        </select>
                        <p style="font-size: 0.78rem; color: var(--danger); margin-top: 4px;">
                            Belum ada lokasi untuk {{ $realization->unit->name }}.
                            <a href="{{ route('locations.create') }}" target="_blank">Tambah lokasi dulu →</a>
                            (lokasi opsional, boleh dilewati dan diisi belakangan lewat halaman Edit Aset)
                        </p>
                    @else
                        <select name="location_id">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected(old('location_id') == $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Merk (opsional)</label>
                    <input type="text" name="brand" value="{{ old('brand') }}">
                </div>
                <div class="form-group">
                    <label>Model (opsional)</label>
                    <input type="text" name="model" value="{{ old('model') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Kondisi</label>
                    <select name="condition">
                        @foreach (['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'hilang' => 'Hilang'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('condition', 'baik') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        @foreach (['aktif' => 'Aktif', 'dalam_perbaikan' => 'Dalam Perbaikan', 'dipinjamkan' => 'Dipinjamkan', 'dihapuskan' => 'Dihapuskan'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'aktif') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($realization->quantity > 1)
                <p style="font-size: 0.8rem; color: var(--muted); margin-bottom: 16px;">Karena jumlahnya {{ $realization->quantity }}, sistem akan membuat {{ $realization->quantity }} record aset terpisah (masing-masing dapat kode & QR Code sendiri) dengan detail yang sama seperti di atas.</p>
            @endif

            <button type="submit" class="btn">Finalisasi jadi {{ $realization->quantity > 1 ? "{$realization->quantity} Aset" : 'Aset' }}</button>
        </form>
    </div>
</x-layouts.app>
