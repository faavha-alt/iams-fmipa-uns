<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>DBR - {{ $location->name }}</title>
    <style>
        @page { size: 215mm 330mm; margin: 2cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; color: #000; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 24px; padding-bottom: 12px; }
        .header h1 { font-size: 16px; margin: 0 0 4px; text-transform: uppercase; }
        .header p { margin: 0; font-size: 12px; }
        .title-row { display: flex; justify-content: space-between; align-items: flex-start; margin: 24px 0; gap: 20px; }
        .title { text-align: center; flex: 1; }
        .title h2 { font-size: 15px; text-decoration: underline; margin: 0 0 4px; text-transform: uppercase; }
        .title p { margin: 0; font-size: 12px; }
        .qr-block { text-align: center; font-size: 10px; color: #444; }
        .qr-block p { margin: 4px 0 0; max-width: 130px; }
        table.dbr-info { width: 100%; margin: 12px 0 20px; }
        table.dbr-info td { padding: 2px 0; vertical-align: top; }
        table.dbr-info td:first-child { width: 160px; }
        table.items { width: 100%; border-collapse: collapse; margin: 16px 0; }
        table.items th, table.items td { border: 1px solid #000; padding: 6px 8px; font-size: 12px; }
        table.items th { background: #eee; text-align: left; }
        .footer-note { margin-top: 40px; font-size: 11px; color: #444; display: flex; justify-content: space-between; }
        .print-btn { position: fixed; top: 16px; right: 16px; padding: 10px 18px; background: #0E7DA7; color: white; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨 Cetak / Simpan PDF</button>

    <div class="header">
        @if ($kopLogo)
            <img src="{{ asset('storage/'.$kopLogo) }}" alt="Kop Surat" style="max-width: 100%; max-height: 130px;">
        @else
            <h1>{{ $kopBaris1 }}</h1>
            @if ($kopBaris2)
                <p>{{ $kopBaris2 }}</p>
            @endif
        @endif
    </div>

    <div class="title-row">
        <div style="width: 130px;"></div>
        <div class="title">
            <h2>Daftar Barang Ruangan (DBR)</h2>
            <p>{{ $location->name }}</p>
        </div>
        <div class="qr-block">
            {!! $qrSvg !!}
            <p>Scan untuk data terkini</p>
        </div>
    </div>

    <table class="dbr-info">
        <tr><td>Unit / Program Studi</td><td>: {{ $location->unit->name }}</td></tr>
        <tr><td>Gedung</td><td>: {{ $location->building ?? '-' }}</td></tr>
        <tr><td>Lantai</td><td>: {{ $location->floor ?? '-' }}</td></tr>
        <tr><td>Kode Ruang</td><td>: {{ $location->room_code ?? '-' }}</td></tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 26px;">No</th>
                <th>Kode Aset</th>
                <th>Nama Barang</th>
                <th>Merk</th>
                <th>Tipe/Seri</th>
                <th>Kategori</th>
                <th>Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($assets as $i => $asset)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $asset->asset_code }}</td>
                    <td>{{ $asset->name }}</td>
                    <td>{{ $asset->brand ?? '-' }}</td>
                    <td>{{ collect([$asset->model, $asset->serial_number])->filter()->implode(' / ') ?: '-' }}</td>
                    <td>{{ $asset->category?->name ?? '-' }}</td>
                    <td>{{ str_replace('_', ' ', $asset->condition) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #666;">Belum ada aset tercatat di ruangan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        <span>Total: {{ $assets->count() }} barang</span>
        <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
    </div>
</body>
</html>
