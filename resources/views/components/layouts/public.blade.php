<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'IAMS FMIPA UNS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">
</head>
<body class="public-body">
    <header class="public-nav">
        <div class="public-nav__inner">
            <a href="{{ route('home') }}" class="public-nav__brand">
                <span class="public-nav__brand-mark">IAMS</span>
                <span class="public-nav__brand-sub">FMIPA UNS</span>
            </a>

            <nav class="public-nav__links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">Beranda</a>
                <a href="{{ route('announcements.public-index') }}" class="{{ request()->routeIs('announcements.public-*') ? 'is-active' : '' }}">Pengumuman</a>
                <a href="{{ route('documentation') }}" class="{{ request()->routeIs('documentation') ? 'is-active' : '' }}">Dokumentasi</a>
            </nav>

            <a href="{{ route('login') }}" class="btn btn-sm">Masuk</a>
        </div>
    </header>

    <main class="public-main">
        {{ $slot }}
    </main>

    <footer class="public-footer">
        <p>&copy; {{ now()->year }} IAMS FMIPA UNS &middot; Fakultas MIPA, Universitas Sebelas Maret</p>
    </footer>

    <script src="{{ asset('js/frontend.js') }}"></script>
</body>
</html>
