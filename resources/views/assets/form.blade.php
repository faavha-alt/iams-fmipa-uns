<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Inventaris</div>
            <h1>{{ $asset ? 'Edit Aset' : 'Tambah Aset Baru' }}</h1>
            @if ($asset)
                <p>Kode: <span class="code-chip">{{ $asset->asset_code }}</span> &nbsp; QR: <span class="code-chip">{{ $asset->qr_code }}</span></p>
            @endif
        </div>
        <a href="{{ route('assets.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ $asset ? route('assets.update', $asset->id) : route('assets.store') }}">
            @csrf
            @if ($asset) @method('PUT') @endif

            @if ($openRequests->count() > 0)
                <div class="form-group">
                    <label>Realisasi dari Pengajuan (opsional)</label>
                    <select name="asset_request_id">
                        <option value="">-- Tidak terkait pengajuan manapun --</option>
                        @foreach ($openRequests as $req)
                            <option value="{{ $req->id }}" @selected(old('asset_request_id', $asset->asset_request_id ?? '') == $req->id)>
                                {{ $req->unit->name }} — {{ $req->item_name }} ({{ $req->quantity }}x)
                            </option>
                        @endforeach
                    </select>
                    <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Pilih ini kalau aset yang kamu input adalah realisasi pembelian dari pengajuan yang sudah disetujui, supaya tercatat dari awal sampai akhir.</p>
                </div>
            @endif

            <div class="form-row">
                <div class="form-group">
                    <label>Nama Aset</label>
                    <input type="text" name="name" value="{{ old('name', $asset->name ?? '') }}">
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Kategori</label>
                    <select name="asset_category_id">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('asset_category_id', $asset->asset_category_id ?? '') == $category->id)>{{ $category->name }}{{ $category->unit_satuan ? ' ('.$category->unit_satuan.')' : '' }}</option>
                        @endforeach
                    </select>
                    @error('asset_category_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Merk</label>
                    <input type="text" name="brand" value="{{ old('brand', $asset->brand ?? '') }}">
                </div>
                <div class="form-group">
                    <label>Model</label>
                    <input type="text" name="model" value="{{ old('model', $asset->model ?? '') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nomor Seri</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number', $asset->serial_number ?? '') }}">
                </div>
                <div class="form-group">
                    <label>Unit Pemilik</label>
                    <select name="unit_id">
                        <option value="">-- Pilih Unit --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $asset->unit_id ?? '') == $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    @error('unit_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Lokasi</label>
                    <select name="location_id">
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected(old('location_id', $asset->location_id ?? '') == $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Vendor (opsional)</label>
                    <select name="vendor_id">
                        <option value="">-- Tidak Ada --</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected(old('vendor_id', $asset->vendor_id ?? '') == $vendor->id)>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Penanggung Jawab (opsional)</label>
                    <select name="responsible_user_id">
                        <option value="">-- Belum ditentukan --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(old('responsible_user_id', $asset->responsible_user_id ?? '') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Pengguna Saat Ini (opsional)</label>
                    <select name="current_user_id">
                        <option value="">-- Belum ditentukan --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(old('current_user_id', $asset->current_user_id ?? '') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Tanggal Perolehan</label>
                    <input type="date" name="acquisition_date" value="{{ old('acquisition_date', optional($asset->acquisition_date ?? null)->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label>Sumber Perolehan</label>
                    <select name="acquisition_source">
                        @foreach (['pengadaan' => 'Pengadaan', 'hibah' => 'Hibah', 'bantuan' => 'Bantuan', 'lainnya' => 'Lainnya'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('acquisition_source', $asset->acquisition_source ?? 'pengadaan') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nilai Perolehan (Rp)</label>
                    <input type="number" step="0.01" name="acquisition_value" value="{{ old('acquisition_value', $asset->acquisition_value ?? '0') }}">
                    @error('acquisition_value') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Kondisi</label>
                    <select name="condition">
                        @foreach (['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'hilang' => 'Hilang'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('condition', $asset->condition ?? 'baik') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    @foreach (['aktif' => 'Aktif', 'dalam_perbaikan' => 'Dalam Perbaikan', 'dipinjamkan' => 'Dipinjamkan', 'dihapuskan' => 'Dihapuskan'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $asset->status ?? 'aktif') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Catatan</label>
                <textarea name="notes" rows="3">{{ old('notes', $asset->notes ?? '') }}</textarea>
            </div>

            <details class="review-panel" style="padding: 0; margin-bottom: 20px;">
                <summary>Data SIMAK BMN (opsional, isi kalau sudah ada data resminya)</summary>
                <div class="review-panel__body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Kode Barang BMN</label>
                            <input type="text" name="simak_kode_barang" list="bmn-code-list" value="{{ old('simak_kode_barang', $asset->simak_kode_barang ?? '') }}" placeholder="Ketik buat cari, contoh: kursi atau 3.05.02.01.001">
                            <datalist id="bmn-code-list">
                                @foreach (\App\Models\BmnCodeReference::orderBy('kode')->get() as $bmn)
                                    <option value="{{ $bmn->kode }}">{{ $bmn->nama }}</option>
                                @endforeach
                            </datalist>
                            <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Mulai ketik buat cari dari daftar Kode BMN yang sudah didaftarkan di <a href="{{ route('bmn-codes.index') }}" target="_blank">Master Kode BMN</a>, atau ketik manual kalau belum ada.</p>
                            @error('simak_kode_barang') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label>NUP (Nomor Urut Pendaftaran)</label>
                            <input type="number" name="simak_nup" value="{{ old('simak_nup', $asset->simak_nup ?? '') }}">
                            @error('simak_nup') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Kode Lokasi BMN</label>
                            <input type="text" name="simak_kode_lokasi" value="{{ old('simak_kode_lokasi', $asset->simak_kode_lokasi ?? '') }}" placeholder="Beda dari lokasi fisik di atas — ini kode administratif DJKN">
                        </div>
                        <div class="form-group">
                            <label>Tahun Perolehan (versi BMN)</label>
                            <input type="number" name="simak_tahun_perolehan" value="{{ old('simak_tahun_perolehan', $asset->simak_tahun_perolehan ?? '') }}" placeholder="Bisa beda dari tanggal perolehan di atas">
                            @error('simak_tahun_perolehan') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    @if ($asset && $asset->simak_full_code)
                        <p style="font-size: 0.8rem; color: var(--muted);">Kode BMN lengkap: <span class="code-chip">{{ $asset->simak_full_code }}</span></p>
                    @endif
                </div>
            </details>

            <button type="submit" class="btn">{{ $asset ? 'Simpan Perubahan' : 'Tambah Aset' }}</button>
        </form>
    </div>
</x-layouts.app>
