<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Master Data</div>
            <h1>Vendor</h1>
            <p>Daftar penyedia/rekanan pengadaan.</p>
        </div>
        <a href="{{ route('vendors.create') }}" class="btn">+ Tambah Vendor</a>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('vendors.index') }}" class="filters">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama vendor atau kontak...">
            <button type="submit" class="btn btn-outline btn-sm">Cari</button>
        </form>

        @if ($vendors->count() === 0)
            <div class="empty-state">Belum ada vendor yang cocok.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kontak</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Aset</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vendors as $vendor)
                        <tr>
                            <td>{{ $vendor->name }}</td>
                            <td>{{ $vendor->contact_person ?? '-' }}</td>
                            <td>{{ $vendor->phone ?? '-' }}</td>
                            <td>{{ $vendor->email ?? '-' }}</td>
                            <td>{{ $vendor->assets_count }}</td>
                            <td>
                                @if ($vendor->is_active)
                                    <span class="badge badge-baik">Aktif</span>
                                @else
                                    <span class="badge badge-hilang">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('vendors.edit', $vendor->id) }}" class="icon-btn" title="Edit" aria-label="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('vendors.toggle-active', $vendor->id) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="action-pill {{ $vendor->is_active ? 'action-pill--danger' : 'action-pill--ok' }}">
                                            {{ $vendor->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('vendors.destroy', $vendor->id) }}" style="display:inline" data-confirm="Yakin hapus vendor {{ $vendor->name }}?">
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
                {{ $vendors->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
