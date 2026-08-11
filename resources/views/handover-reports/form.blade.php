<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Serah Terima</div>
            <h1>{{ $report ? 'Edit BAST — '.$report->nomor_bast : 'Buat BAST — '.$unit->name }}</h1>
            <p>Centang barang yang mau digabung dalam satu BAST ini.</p>
        </div>
        <a href="{{ $report ? route('handover-reports.show', $report->id) : route('handover-reports.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ $report ? route('handover-reports.update', $report->id) : route('handover-reports.store', $unit->id) }}">
            @csrf
            @if ($report) @method('PUT') @endif

            <div class="form-group">
                <label>Pilih Barang</label>
                <div style="border: 1px solid var(--border); border-radius: 8px; padding: 4px; max-height: 320px; overflow-y: auto;">
                    <table style="margin: 0;">
                        <thead>
                            <tr>
                                <th style="width: 36px;">
                                    <input type="checkbox" onclick="document.querySelectorAll('.asset-check').forEach(c => c.checked = this.checked)">
                                </th>
                                <th>Nama Barang</th>
                                <th>Merk</th>
                                <th>Type/Seri</th>
                                <th>Jenis</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($availableAssets as $asset)
                                <tr>
                                    <td><input type="checkbox" class="asset-check" name="asset_ids[]" value="{{ $asset->id }}" @checked(in_array($asset->id, $selectedAssetIds))></td>
                                    <td>{{ $asset->name }}</td>
                                    <td>{{ $asset->brand ?? '-' }}</td>
                                    <td>{{ collect([$asset->model, $asset->serial_number])->filter()->implode(' / ') ?: '-' }}</td>
                                    <td>{{ $asset->category?->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @error('asset_ids') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group" style="max-width: 240px;">
                <label>Tanggal Serah Terima</label>
                <input type="date" name="tanggal_serah_terima" value="{{ old('tanggal_serah_terima', $report?->tanggal_serah_terima?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                @error('tanggal_serah_terima') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <p style="font-size: 0.82rem; font-weight: 600; margin-bottom: 8px;">Pihak yang Menyerahkan</p>
            <div class="form-row">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="nama_menyerahkan" value="{{ old('nama_menyerahkan', $report->nama_menyerahkan ?? '') }}" placeholder="Contoh: Budi Santoso">
                    @error('nama_menyerahkan') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Jabatan</label>
                    <input type="text" name="jabatan_menyerahkan" value="{{ old('jabatan_menyerahkan', $report->jabatan_menyerahkan ?? '') }}" placeholder="Contoh: Kasubbag Umum & Pengadaan">
                    @error('jabatan_menyerahkan') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <p style="font-size: 0.82rem; font-weight: 600; margin-bottom: 8px;">Pihak yang Menerima</p>
            <div class="form-row">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="nama_menerima" value="{{ old('nama_menerima', $report->nama_menerima ?? '') }}" placeholder="Contoh: Dr. Ani Wijaya">
                    @error('nama_menerima') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Jabatan</label>
                    <input type="text" name="jabatan_menerima" value="{{ old('jabatan_menerima', $report->jabatan_menerima ?? '') }}" placeholder="Contoh: Kepala Prodi Informatika">
                    @error('jabatan_menerima') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="catatan" rows="3">{{ old('catatan', $report->catatan ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn">{{ $report ? 'Simpan Perubahan' : 'Buat BAST' }}</button>
        </form>
    </div>
</x-layouts.app>
