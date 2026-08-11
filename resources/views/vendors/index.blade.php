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
                                <a href="{{ route('vendors.edit', $vendor->id) }}">Edit</a>
                                &nbsp;·&nbsp;
                                <form method="POST" action="{{ route('vendors.toggle-active', $vendor->id) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="link-danger" style="color: {{ $vendor->is_active ? 'var(--danger)' : 'var(--ok)' }};">
                                        {{ $vendor->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                &nbsp;·&nbsp;
                                <form method="POST" action="{{ route('vendors.destroy', $vendor->id) }}" style="display:inline" data-confirm="Yakin hapus vendor {{ $vendor->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-danger">Hapus</button>
                                </form>
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
