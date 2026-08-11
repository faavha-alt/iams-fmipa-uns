<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Master Data</div>
            <h1>Lokasi</h1>
            <p>Ruangan/gedung tempat aset ditempatkan.</p>
        </div>
        <a href="{{ route('locations.create') }}" class="btn">+ Tambah Lokasi</a>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif
    @if (session('error'))
        <div class="alert-success" style="background: var(--danger-soft); color: var(--danger); border-left-color: var(--danger);">{{ session('error') }}</div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('locations.index') }}" class="filters">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama lokasi...">
            <select name="unit_id" onchange="this.form.submit()">
                <option value="">Semua Unit</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>{{ $unit->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Cari</button>
        </form>

        @if ($locations->count() === 0)
            <div class="empty-state">Belum ada lokasi yang cocok.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Nama Lokasi</th>
                        <th>Unit</th>
                        <th>Gedung</th>
                        <th>Lantai</th>
                        <th>Kode Ruang</th>
                        <th>Aset</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($locations as $location)
                        <tr>
                            <td>{{ $location->name }}</td>
                            <td>{{ $location->unit->name }}</td>
                            <td>{{ $location->building ?? '-' }}</td>
                            <td>{{ $location->floor ?? '-' }}</td>
                            <td>{{ $location->room_code ?? '-' }}</td>
                            <td>{{ $location->assets_count }}</td>
                            <td>
                                <a href="{{ route('locations.edit', $location->id) }}">Edit</a>
                                &nbsp;·&nbsp;
                                <form method="POST" action="{{ route('locations.destroy', $location->id) }}" style="display:inline" data-confirm="Yakin hapus lokasi {{ $location->name }}?">
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
                {{ $locations->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
