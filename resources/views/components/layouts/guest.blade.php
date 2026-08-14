<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Masuk · IAMS FMIPA UNS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">
</head>
<body class="guest-body">
    <div class="guest-shell">
        <div class="guest-mark">
            <span class="guest-mark__code">FMIPA-2026</span>
            <h1>IAMS</h1>
            <p>Integrated Asset Management System — Fakultas MIPA, Universitas Sebelas Maret</p>

            <div class="guest-stats">
                <div class="guest-stat">
                    <div class="guest-stat__value">{{ number_format($publicStats['totalAssets']) }}</div>
                    <div class="guest-stat__label">Aset Terdata</div>
                </div>
                <div class="guest-stat">
                    <div class="guest-stat__value">{{ number_format($publicStats['totalUnits']) }}</div>
                    <div class="guest-stat__label">Unit / Prodi</div>
                </div>
                <div class="guest-stat">
                    <div class="guest-stat__value">{{ number_format($publicStats['totalLocations']) }}</div>
                    <div class="guest-stat__label">Lokasi Terdata</div>
                </div>
                <div class="guest-stat">
                    <div class="guest-stat__value">{{ number_format($publicStats['totalCategories']) }}</div>
                    <div class="guest-stat__label">Kategori Aset</div>
                </div>
                <div class="guest-stat guest-stat--wide">
                    <div class="guest-stat__value">{{ $publicStats['goodConditionPercent'] }}%</div>
                    <div class="guest-stat__label">Aset dalam Kondisi Baik</div>
                </div>
            </div>

            <p class="guest-mark__updated">Data per {{ now()->translatedFormat('d F Y') }}</p>
        </div>

        <div class="guest-panel">
            {{ $slot }}
        </div>
    </div>

    <script src="{{ asset('js/frontend.js') }}"></script>
</body>
</html>
