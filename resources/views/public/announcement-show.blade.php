<x-layouts.public>
    <section class="public-section">
        <div class="public-section__inner" style="max-width: 760px;">
            <a href="{{ route('announcements.public-index') }}" style="font-size: 13px; color: var(--cerulean); text-decoration: none; font-weight: 600;">← Semua Pengumuman</a>

            <div class="announcement-detail" style="margin-top: 16px;">
                <div class="announcement-card__date">{{ $announcement->created_at->translatedFormat('d F Y') }} &middot; {{ $announcement->author?->name ?? 'Admin' }}</div>
                <h1>{{ $announcement->title }}</h1>
                <div class="announcement-detail__body">{{ $announcement->body }}</div>
            </div>
        </div>
    </section>
</x-layouts.public>
