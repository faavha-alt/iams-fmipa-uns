<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Sticker Aset</title>
    <style>
        @page { size: 215mm 330mm; margin: 8mm; }
        * { box-sizing: border-box; }
        body { font-family: 'Poppins', Arial, sans-serif; margin: 0; padding: 20px; color: #000; }
        .toolbar { margin-bottom: 16px; }
        .toolbar p { font-size: 13px; color: #555; margin: 0 0 10px; }
        .print-btn { padding: 10px 18px; background: #0E7DA7; color: white; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; }
        @media print { .toolbar { display: none; } body { padding: 0; } }

        .sticker-sheet { width: 199mm; display: flex; flex-wrap: wrap; }
        .sticker {
            width: 49.75mm; height: 61.5mm;
            border: 1px dashed #bbb; padding: 2.5mm;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1.2mm;
            text-align: center; break-inside: avoid; page-break-inside: avoid;
        }
        .sticker__qr svg { width: 19mm; height: 19mm; display: block; }
        .sticker__name {
            font-size: 7.5px; font-weight: 700; line-height: 1.15; color: #000; width: 100%;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .sticker__row { font-size: 6.5px; color: #333; font-family: 'Courier New', monospace; letter-spacing: 0.01em; }
    </style>
</head>
<body>
    <div class="toolbar">
        <p>{{ $items->count() }} sticker akan dicetak — muat sekitar 20 sticker per lembar F4.</p>
        <button class="print-btn" onclick="window.print()">🖨 Cetak / Simpan PDF</button>
    </div>

    <div class="sticker-sheet">
        @foreach ($items as $item)
            <div class="sticker">
                <div class="sticker__qr">{!! $item['qrSvg'] !!}</div>
                <div class="sticker__name">{{ $item['asset']->name }}</div>
                <div class="sticker__row">{{ $item['asset']->simak_tahun_perolehan ?? $item['asset']->acquisition_date?->format('Y') ?? '-' }}</div>
                @if ($item['asset']->simak_kode_barang)
                    <div class="sticker__row">{{ $item['asset']->simak_kode_barang }}</div>
                @endif
                @if ($item['asset']->simak_nup)
                    <div class="sticker__row">NUP {{ $item['asset']->simak_nup }}</div>
                @endif
            </div>
        @endforeach
    </div>
</body>
</html>
