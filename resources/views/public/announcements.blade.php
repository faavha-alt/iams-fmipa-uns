<x-layouts.public>
    <section class="public-section">
        <div class="public-section__inner">
            <div class="page-header" style="margin-bottom: 28px;">
                <div>
                    <div class="page-header__eyebrow">IAMS FMIPA UNS</div>
                    <h1 style="font-family: 'Montserrat', sans-serif; color: var(--navy); font-size: 1.6rem; margin: 0;">Pengumuman</h1>
                </div>
            </div>

            @forelse ($announcements as $announcement)
                <a href="{{ route('announcements.public-show', $announcement) }}" class="announcement-card">
                    <div class="announcement-card__date">{{ $announcement->created_at->translatedFormat('d M Y') }}</div>
                    <h3>{{ $announcement->title }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit($announcement->body, 200) }}</p>
                </a>
            @empty
                <div class="empty-state">Belum ada pengumuman.</div>
            @endforelse

            {{ $announcements->links() }}
        </div>
    </section>
</x-layouts.public>
