<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Situs Publik</div>
            <h1>Pengumuman</h1>
            <p>Tampil di halaman depan publik (sebelum login) — cuma yang berstatus "Tampil" yang bisa dilihat umum.</p>
        </div>
        <a href="{{ route('announcements.create') }}" class="btn">+ Tulis Pengumuman</a>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <div class="card">
        @if ($announcements->count() === 0)
            <div class="empty-state">Belum ada pengumuman. Klik "+ Tulis Pengumuman" buat bikin yang pertama.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Ditulis Oleh</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($announcements as $announcement)
                        <tr>
                            <td>{{ $announcement->title }}</td>
                            <td>{{ $announcement->author?->name ?? '-' }}</td>
                            <td>{{ $announcement->created_at->translatedFormat('d M Y') }}</td>
                            <td>
                                @if ($announcement->is_published)
                                    <span class="badge badge-baik">Tampil</span>
                                @else
                                    <span class="badge badge-hilang">Disembunyikan</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('announcements.edit', $announcement->id) }}" class="icon-btn" title="Edit" aria-label="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('announcements.toggle-published', $announcement->id) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="action-pill {{ $announcement->is_published ? 'action-pill--danger' : 'action-pill--ok' }}">
                                            {{ $announcement->is_published ? 'Sembunyikan' : 'Tampilkan' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('announcements.destroy', $announcement->id) }}" style="display:inline" data-confirm="Yakin hapus pengumuman &quot;{{ $announcement->title }}&quot;?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn icon-btn--danger" title="Hapus" aria-label="Hapus">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
