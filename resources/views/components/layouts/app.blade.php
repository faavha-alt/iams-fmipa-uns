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
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="sidebar__brand">
                <span class="sidebar__brand-mark">IAMS</span>
                <span class="sidebar__brand-sub">FMIPA UNS</span>
            </div>

            <nav class="sidebar__nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('assets.index') }}" class="{{ request()->routeIs('assets.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3.5 7 8.5-4 8.5 4-8.5 4-8.5-4Z"/><path d="M3.5 7v10l8.5 4 8.5-4V7"/><path d="M12 11v10"/></svg>
                    Aset
                </a>
                <a href="{{ route('requests.index') }}" class="{{ request()->routeIs('requests.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="3" width="14" height="18" rx="1.8"/><path d="M9 3v2h6V3M8.5 10h7M8.5 14h5" stroke-linecap="round"/></svg>
                    Pengajuan
                    @if ($pendingRequestCount > 0)
                        <span class="nav-badge">{{ $pendingRequestCount }}</span>
                    @endif
                </a>
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('budgets.index') }}" class="{{ request()->routeIs('budgets.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Anggaran
                    </a>
                    <a href="{{ route('procurement-batches.index') }}" class="{{ request()->routeIs('procurement-batches.*') || request()->routeIs('realizations.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7h-9M14 17H5M5 7l3-3-3 3 3 3M20 17l-3 3 3-3-3-3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Pengadaan
                    </a>
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20c0-3.3 2.5-6 5.5-6s5.5 2.7 5.5 6" stroke-linecap="round"/><path d="M16 8.5a3 3 0 1 1 0-6M17.5 14c2.5 0.3 4.5 2.6 4.5 6" stroke-linecap="round"/></svg>
                        Pengguna
                    </a>
                    <a href="{{ route('units.index') }}" class="{{ request()->routeIs('units.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 2 8l10 5 10-5-10-5Z"/><path d="M6 10.5v5c0 1.4 2.7 2.5 6 2.5s6-1.1 6-2.5v-5" stroke-linecap="round"/></svg>
                        Program Studi
                    </a>
                    <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></svg>
                        Kategori Aset
                    </a>
                    <a href="{{ route('vendors.index') }}" class="{{ request()->routeIs('vendors.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V9l7-5 7 5v12" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 21v-6h6v6" stroke-linecap="round"/></svg>
                        Vendor
                    </a>
                    <a href="{{ route('locations.index') }}" class="{{ request()->routeIs('locations.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-6.5 7-11.5A7 7 0 0 0 5 9.5C5 14.5 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.3"/></svg>
                        Lokasi
                    </a>
                    <a href="{{ route('bmn-codes.index') }}" class="{{ request()->routeIs('bmn-codes.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h10M7 16h6" stroke-linecap="round"/></svg>
                        Kode BMN
                    </a>
                    <a href="{{ route('handover-reports.index') }}" class="{{ request()->routeIs('handover-reports.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12.5l2 2 4-4.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                        BAST
                    </a>
                    <a href="{{ route('settings.edit') }}" class="{{ request()->routeIs('settings.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Pengaturan
                    </a>
                @endif
            </nav>

            <div class="sidebar__user">
                <div class="sidebar__avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="sidebar__user-info">
                    <span class="sidebar__user-name">{{ auth()->user()->name }}</span>
                    <span class="role-tag role-tag--{{ auth()->user()->role }}">{{ auth()->user()->role === 'admin' ? 'Admin' : 'Pengaju · ' . auth()->user()->unit?->name }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="sidebar__logout-form">
                @csrf
                <button type="submit" class="sidebar__logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke-linecap="round"/><path d="M16 17l5-5-5-5M21 12H9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Keluar
                </button>
            </form>
        </aside>

        <div class="content">
            <main class="content__body">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script src="{{ asset('js/frontend.js') }}"></script>
</body>
</html>
