<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $location->name }} · IAMS FMIPA UNS</title>
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
        .pub-header h1 { font-family: 'Montserrat', sans-serif; font-size: 1.5rem; margin: 4px 0 2px; font-weight: 800; }
        .pub-header p { margin: 0; font-size: 0.85rem; opacity: 0.9; }
        .pub-wrap { max-width: 640px; margin: -14px auto 0; padding: 0 16px 32px; }
        .pub-card {
            background: var(--surface); border-radius: var(--radius); padding: 18px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06), 0 4px 14px rgba(10,61,82,0.06);
            margin-bottom: 14px;
        }
        .pub-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 16px; font-size: 0.83rem; }
        .pub-meta__label { color: var(--muted); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; }
        .pub-meta__value { font-weight: 600; color: var(--ink); }
        .pub-count { font-size: 0.8rem; color: var(--muted); font-weight: 600; margin: 18px 2px 8px; }
        .asset-card { padding: 13px 16px; }
        .asset-card + .asset-card { margin-top: 8px; }
        .asset-card__name { font-weight: 700; font-size: 0.92rem; color: var(--navy); margin-bottom: 6px; }
        .asset-card__row { display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 0.78rem; color: var(--muted); }
        .asset-card__row strong { color: var(--ink); font-weight: 600; }
        .pub-footer { text-align: center; font-size: 0.72rem; color: var(--muted); margin-top: 20px; }
        .pub-empty { text-align: center; padding: 30px 16px; color: var(--muted); font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="pub-header">
        <span class="pub-header__badge">IAMS FMIPA UNS</span>
        <h1>{{ $location->name }}</h1>
        <p>@if ($location->unit) {{ $location->unit->name }} @endif</p>
    </div>

    <div class="pub-wrap">
        <div class="pub-card">
            <div class="pub-meta">
                <div><div class="pub-meta__label">Gedung</div><div class="pub-meta__value">{{ $location->building ?? '-' }}</div></div>
                <div><div class="pub-meta__label">Lantai</div><div class="pub-meta__value">{{ $location->floor ?? '-' }}</div></div>
                <div><div class="pub-meta__label">Kode Ruang</div><div class="pub-meta__value">{{ $location->room_code ?? '-' }}</div></div>
                <div><div class="pub-meta__label">Jumlah Barang</div><div class="pub-meta__value">{{ $assets->count() }} aset</div></div>
            </div>
        </div>

        <div class="pub-count">Daftar Barang Ruangan</div>

        @if ($assets->count() === 0)
            <div class="pub-card pub-empty">Belum ada aset tercatat di ruangan ini.</div>
        @else
            @foreach ($assets as $asset)
                <div class="pub-card asset-card">
                    <div class="asset-card__name">{{ $asset->name }}</div>
                    <div class="asset-card__row">
                        <span><strong>Merk:</strong> {{ $asset->brand ?? '-' }}</span>
                        <span><strong>Tipe/Seri:</strong> {{ collect([$asset->model, $asset->serial_number])->filter()->implode(' / ') ?: '-' }}</span>
                        <span><strong>Kategori:</strong> {{ $asset->category?->name ?? '-' }}</span>
                        <span class="badge badge-{{ $asset->condition }}">{{ str_replace('_', ' ', $asset->condition) }}</span>
                    </div>
                </div>
            @endforeach
        @endif

        <div class="pub-footer">
            Data ditampilkan langsung dari sistem, selalu terkini. Halaman ini bisa diakses siapa saja lewat scan QR di ruangan, tanpa perlu login.
        </div>
    </div>
</body>
</html>
