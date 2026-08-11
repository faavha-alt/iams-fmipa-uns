<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $asset->name }} · IAMS FMIPA UNS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">
    <style>
        body { background: var(--cream); min-height: 100vh; }
        .pub-header {
            background: linear-gradient(135deg, var(--cerulean) 0%, var(--cerulean-dark) 100%);
            color: white; padding: 28px 20px 22px; text-align: center;
        }
        .pub-header__badge {
            display: inline-block; font-family: var(--font-mono); font-size: 11px;
            border: 1px solid rgba(255,255,255,0.4); border-radius: 999px; padding: 3px 12px;
            opacity: 0.9; margin-bottom: 10px;
        }
        .pub-header h1 { font-family: 'Montserrat', sans-serif; font-size: 1.4rem; margin: 4px 0 6px; font-weight: 800; }
        .pub-header p { margin: 0; font-size: 0.85rem; opacity: 0.9; }
        .pub-wrap { max-width: 640px; margin: -14px auto 0; padding: 0 16px 32px; }
        .pub-card {
            background: var(--surface); border-radius: var(--radius); padding: 18px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06), 0 4px 14px rgba(10,61,82,0.06);
            margin-bottom: 14px;
        }
        .pub-card__title { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); font-weight: 700; margin: 0 0 12px; }
        .pub-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 16px; font-size: 0.83rem; }
        .pub-meta__label { color: var(--muted); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; }
        .pub-meta__value { font-weight: 600; color: var(--ink); }
        .pub-footer { text-align: center; font-size: 0.72rem; color: var(--muted); margin-top: 20px; }
    </style>
</head>
<body>
    <div class="pub-header">
        <span class="pub-header__badge">IAMS FMIPA UNS</span>
        <h1>{{ $asset->name }}</h1>
        <p><span class="code-chip" style="background: rgba(255,255,255,0.18); color: white;">{{ $asset->asset_code }}</span></p>
    </div>

    <div class="pub-wrap">
        <div class="pub-card">
            <div class="pub-card__title">Identitas Barang</div>
            <div class="pub-meta">
                <div><div class="pub-meta__label">Merk</div><div class="pub-meta__value">{{ $asset->brand ?? '-' }}</div></div>
                <div><div class="pub-meta__label">Tipe/Seri</div><div class="pub-meta__value">{{ collect([$asset->model, $asset->serial_number])->filter()->implode(' / ') ?: '-' }}</div></div>
                <div><div class="pub-meta__label">Kategori</div><div class="pub-meta__value">{{ $asset->category?->name ?? '-' }}</div></div>
                <div><div class="pub-meta__label">Kondisi</div><div class="pub-meta__value"><span class="badge badge-{{ $asset->condition }}">{{ str_replace('_', ' ', $asset->condition) }}</span></div></div>
                <div><div class="pub-meta__label">Status</div><div class="pub-meta__value">{{ str_replace('_', ' ', $asset->status) }}</div></div>
                <div><div class="pub-meta__label">Tahun Perolehan</div><div class="pub-meta__value">{{ $asset->simak_tahun_perolehan ?? $asset->acquisition_date?->format('Y') ?? '-' }}</div></div>
            </div>
        </div>

        <div class="pub-card">
            <div class="pub-card__title">Penempatan</div>
            <div class="pub-meta">
                <div><div class="pub-meta__label">Unit</div><div class="pub-meta__value">{{ $asset->unit?->name ?? '-' }}</div></div>
                <div><div class="pub-meta__label">Lokasi</div><div class="pub-meta__value">{{ $asset->location?->name ?? '-' }}</div></div>
            </div>
        </div>

        @if ($asset->simak_kode_barang || $asset->simak_nup)
            <div class="pub-card">
                <div class="pub-card__title">SIMAK BMN</div>
                <div class="pub-meta">
                    <div><div class="pub-meta__label">Kode Barang</div><div class="pub-meta__value">{{ $asset->simak_kode_barang ?? '-' }}</div></div>
                    <div><div class="pub-meta__label">Nomor Urut (NUP)</div><div class="pub-meta__value">{{ $asset->simak_nup ?? '-' }}</div></div>
                </div>
            </div>
        @endif

        <div class="pub-footer">
            Data ditampilkan langsung dari sistem, selalu terkini. Halaman ini bisa diakses siapa saja lewat scan QR di sticker aset, tanpa perlu login.
        </div>
    </div>
</body>
</html>
