<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Serah Terima</div>
            <h1>{{ $report->nomor_bast }}</h1>
            <p>{{ $report->unit->name }} — {{ $report->tanggal_serah_terima->format('d M Y') }}</p>
        </div>
        <div style="display:flex; gap:8px;">
            @if ($report->status === 'draft')
                <a href="{{ route('handover-reports.edit', $report->id) }}" class="btn btn-outline">✏ Edit</a>
            @endif
            <a href="{{ route('handover-reports.print', $report->id) }}" target="_blank" class="btn btn-outline">🖨 Cetak</a>
            <a href="{{ route('handover-reports.index') }}" class="btn btn-outline">← Kembali</a>
        </div>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Info Serah Terima</h2>
            @if ($report->status === 'final')
                <span class="badge badge-baik">Final — sudah ada scan bertanda tangan</span>
            @else
                <span class="badge badge-diajukan">Draft — masih bisa diedit</span>
            @endif
        </div>
        <table>
            <tbody>
                <tr><td style="width: 220px; color: var(--muted);">Menyerahkan</td><td>{{ $report->nama_menyerahkan }} — {{ $report->jabatan_menyerahkan }}</td></tr>
                <tr><td style="color: var(--muted);">Menerima</td><td>{{ $report->nama_menerima }} — {{ $report->jabatan_menerima }}</td></tr>
                <tr><td style="color: var(--muted);">Catatan</td><td>{{ $report->catatan ?: '-' }}</td></tr>
                <tr><td style="color: var(--muted);">Dibuat oleh</td><td>{{ $report->createdBy->name }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card__header"><h2 class="card__title">Daftar Barang ({{ $report->assets->count() }})</h2></div>
        <table>
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Merk</th>
                    <th>Type/Seri</th>
                    <th>Jenis</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report->assets as $asset)
                    <tr>
                        <td>{{ $asset->name }}</td>
                        <td>{{ $asset->brand ?? '-' }}</td>
                        <td>{{ collect([$asset->model, $asset->serial_number])->filter()->implode(' / ') ?: '-' }}</td>
                        <td>{{ $asset->category?->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card__header"><h2 class="card__title">{{ $report->status === 'final' ? 'Dokumen Terlampir' : 'Upload Dokumen yang Sudah Ditandatangani' }}</h2></div>

        @if ($report->dokumen_scan)
            <a href="{{ asset('storage/'.$report->dokumen_scan) }}" target="_blank" class="btn btn-outline">📄 Lihat Dokumen</a>
        @else
            <p style="font-size: 0.85rem; color: var(--muted); margin-bottom: 14px;">
                Cetak BAST dulu (tombol di atas), tanda tangani kedua pihak, scan, lalu upload di sini. Setelah upload, BAST tidak bisa diedit lagi.
            </p>
            <form method="POST" action="{{ route('handover-reports.upload-scan', $report->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <input type="file" name="dokumen_scan" accept=".pdf,.jpg,.jpeg,.png">
                    @error('dokumen_scan') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn">Upload & Jadikan Final</button>
            </form>
        @endif
    </div>
</x-layouts.app>
