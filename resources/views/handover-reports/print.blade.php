<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $report->nomor_bast }}</title>
    <style>
        @page { size: 215mm 330mm; margin: 2cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; color: #000; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 24px; padding-bottom: 12px; }
        .header h1 { font-size: 16px; margin: 0 0 4px; text-transform: uppercase; }
        .header p { margin: 0; font-size: 12px; }
        .title { text-align: center; margin: 24px 0; }
        .title h2 { font-size: 15px; text-decoration: underline; margin: 0 0 4px; text-transform: uppercase; }
        .title p { margin: 0; font-size: 12px; }
        .intro { margin: 20px 0; text-align: justify; }
        table.bast-info { width: 100%; margin: 12px 0 20px; }
        table.bast-info td { padding: 2px 0; vertical-align: top; }
        table.bast-info td:first-child { width: 180px; }
        table.items { width: 100%; border-collapse: collapse; margin: 16px 0; }
        table.items th, table.items td { border: 1px solid #000; padding: 6px 8px; font-size: 12px; }
        table.items th { background: #eee; text-align: left; }
        .signatures { display: flex; justify-content: space-between; margin-top: 60px; }
        .signature-block { width: 45%; text-align: center; }
        .signature-space { height: 80px; }
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

    <div class="title">
        <h2>Berita Acara Serah Terima Barang</h2>
        <p>Nomor: {{ $report->nomor_bast }}</p>
    </div>

    <div class="intro">
        Pada hari ini, {{ \Carbon\Carbon::parse($report->tanggal_serah_terima)->translatedFormat('l') }}, tanggal {{ \Carbon\Carbon::parse($report->tanggal_serah_terima)->translatedFormat('d F Y') }}, telah dilaksanakan serah terima barang hasil pengadaan sebagaimana rincian di bawah ini, dari:
    </div>

    <table class="bast-info">
        <tr><td>Nama Menyerahkan</td><td>: {{ $report->nama_menyerahkan }}</td></tr>
        <tr><td>Jabatan</td><td>: {{ $report->jabatan_menyerahkan }}</td></tr>
        <tr><td>&nbsp;</td><td></td></tr>
        <tr><td>Nama Menerima</td><td>: {{ $report->nama_menerima }}</td></tr>
        <tr><td>Jabatan</td><td>: {{ $report->jabatan_menerima }}</td></tr>
        <tr><td>Unit Penerima</td><td>: {{ $report->unit->name }}</td></tr>
    </table>

    <p>Dengan rincian barang sebagai berikut:</p>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Barang</th>
                <th>Merk</th>
                <th>Type/Seri</th>
                <th>Jenis</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report->assets as $i => $asset)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $asset->name }}</td>
                    <td>{{ $asset->brand ?? '-' }}</td>
                    <td>{{ collect([$asset->model, $asset->serial_number])->filter()->implode(' / ') ?: '-' }}</td>
                    <td>{{ $asset->category?->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($report->catatan)
        <p><strong>Catatan:</strong> {{ $report->catatan }}</p>
    @endif

    <p>Demikian Berita Acara Serah Terima ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

    <div class="signatures">
        <div class="signature-block">
            <p>Yang Menyerahkan,</p>
            <div class="signature-space"></div>
            <p><strong>{{ $report->nama_menyerahkan }}</strong><br>{{ $report->jabatan_menyerahkan }}</p>
        </div>
        <div class="signature-block">
            <p>Yang Menerima,</p>
            <div class="signature-space"></div>
            <p><strong>{{ $report->nama_menerima }}</strong><br>{{ $report->jabatan_menerima }}</p>
        </div>
    </div>
</body>
</html>
