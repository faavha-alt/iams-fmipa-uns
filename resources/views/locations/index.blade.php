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
                            <td><a href="{{ route('locations.show', $location->id) }}">{{ $location->name }}</a></td>
                            <td>{{ $location->unit->name }}</td>
                            <td>{{ $location->building ?? '-' }}</td>
                            <td>{{ $location->floor ?? '-' }}</td>
                            <td>{{ $location->room_code ?? '-' }}</td>
                            <td>{{ $location->assets_count }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('locations.show', $location->id) }}" class="icon-btn" title="Detail" aria-label="Detail">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="{{ route('locations.edit', $location->id) }}" class="icon-btn" title="Edit" aria-label="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('locations.destroy', $location->id) }}" style="display:inline" data-confirm="Yakin hapus lokasi {{ $location->name }}?">
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
                {{ $locations->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
