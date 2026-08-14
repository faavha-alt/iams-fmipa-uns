<x-layouts.public>
    <section class="public-hero">
        <div class="public-hero__inner">
            <span class="public-hero__badge">FMIPA-2026</span>
            <h1>Sistem Manajemen Aset &amp; Pengadaan Terpadu Fakultas MIPA</h1>
            <p>IAMS memantau inventaris, pengadaan, dan proses aset di lingkungan FMIPA UNS secara terpusat — dari pencatatan barang sampai serah terima antar unit.</p>
            <div class="public-hero__actions">
                <a href="{{ route('login') }}" class="btn">Masuk ke Sistem</a>
                <a href="{{ route('announcements.public-index') }}" class="btn btn-outline">Lihat Pengumuman</a>
            </div>
        </div>
    </section>

    <section class="public-section">
        <div class="public-section__inner">
            <h2>Sekilas Data</h2>
            <div class="stat-grid stat-grid--auto">
                <div class="stat-card">
                    <div class="stat-card__value">{{ number_format($stats['totalAssets']) }}</div>
                    <div class="stat-card__label">Aset Terdata</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__value">{{ number_format($stats['totalUnits']) }}</div>
                    <div class="stat-card__label">Unit / Prodi</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__value">{{ number_format($stats['totalLocations']) }}</div>
                    <div class="stat-card__label">Lokasi Terdata</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__value">{{ number_format($stats['totalCategories']) }}</div>
                    <div class="stat-card__label">Kategori Aset</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__value">{{ $stats['goodConditionPercent'] }}%</div>
                    <div class="stat-card__label">Aset Kondisi Baik</div>
                </div>
            </div>
            <p class="public-section__note">Data per {{ now()->translatedFormat('d F Y') }}. Rincian nilai perolehan aset hanya bisa dilihat oleh pengguna yang sudah masuk ke sistem.</p>
        </div>
    </section>

    <section class="public-section public-section--alt">
        <div class="public-section__inner">
            <div class="public-section__header">
                <h2>Pengumuman Terbaru</h2>
                <a href="{{ route('announcements.public-index') }}">Lihat semua pengumuman →</a>
            </div>

            @forelse ($announcements as $announcement)
                <a href="{{ route('announcements.public-show', $announcement) }}" class="announcement-card">
                    <div class="announcement-card__date">{{ $announcement->created_at->translatedFormat('d M Y') }}</div>
                    <h3>{{ $announcement->title }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit($announcement->body, 160) }}</p>
                </a>
            @empty
                <div class="empty-state">Belum ada pengumuman.</div>
            @endforelse
        </div>
    </section>
</x-layouts.public>
