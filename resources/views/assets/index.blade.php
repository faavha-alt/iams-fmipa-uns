<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Inventaris</div>
            <h1>Daftar Aset</h1>
            <p>{{ $isAdmin ? 'Seluruh unit di FMIPA UNS.' : 'Aset yang tercatat di unit Anda: ' . auth()->user()->unit?->name }}</p>
        </div>
        @if ($isAdmin)
            <a href="{{ route('assets.import') }}" class="btn btn-outline">⬆ Import CSV</a>
            <a href="{{ route('assets.create') }}" class="btn">+ Tambah Aset</a>
        @endif
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('assets.index') }}" class="filters">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode aset, no. seri...">

            @if ($isAdmin)
                <select name="unit_id" onchange="this.form.submit()">
                    <option value="">Semua Unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>{{ $unit->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="category_id" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aktif" @selected(request('status') == 'aktif')>Aktif</option>
                <option value="dalam_perbaikan" @selected(request('status') == 'dalam_perbaikan')>Dalam Perbaikan</option>
                <option value="dipinjamkan" @selected(request('status') == 'dipinjamkan')>Dipinjamkan</option>
                <option value="dihapuskan" @selected(request('status') == 'dihapuskan')>Dihapuskan</option>
            </select>

            <button type="submit" class="btn btn-outline btn-sm">Cari</button>
            @if (request()->anyFilled(['search', 'unit_id', 'category_id', 'status']))
                <a href="{{ route('assets.index') }}" class="btn btn-outline btn-sm">Reset</a>
            @endif
        </form>

        @if ($assets->count() === 0)
            <div class="empty-state">Belum ada aset yang cocok dengan pencarian ini.</div>
        @else
            <div class="table-responsive">
            <table class="table-assets">
                <thead>
                    <tr>
                        <th class="col-name">Nama</th>
                        <th class="col-brand">Merk</th>
                        <th class="col-serial">Tipe/Seri</th>
                        <th class="col-category">Kategori</th>
                        @if ($isAdmin)
                            <th class="col-unit">Unit</th>
                        @endif
                        <th class="col-location">Lokasi</th>
                        <th class="col-badge">Kondisi</th>
                        <th class="col-badge">Status</th>
                        @if ($isAdmin)
                            <th class="col-actions"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $asset)
                        <tr>
                            <td class="col-name">{{ $asset->name }}</td>
                            <td class="col-brand">{{ $asset->brand ?? '-' }}</td>
                            <td class="col-serial">{{ collect([$asset->model, $asset->serial_number])->filter()->implode(' / ') ?: '-' }}</td>
                            <td class="col-category">{{ $asset->category?->name ?? '-' }}</td>
                            @if ($isAdmin)
                                <td class="col-unit">{{ $asset->unit?->name ?? '-' }}</td>
                            @endif
                            <td class="col-location">{{ $asset->location?->name ?? '-' }}</td>
                            <td class="col-badge"><span class="badge badge-{{ $asset->condition }}">{{ str_replace('_', ' ', $asset->condition) }}</span></td>
                            <td class="col-badge">{{ str_replace('_', ' ', $asset->status) }}</td>
                            @if ($isAdmin)
                                <td class="col-actions">
                                    <a href="{{ route('assets.edit', $asset->id) }}">Edit</a>
                                    &nbsp;·&nbsp;
                                    <form method="POST" action="{{ route('assets.destroy', $asset->id) }}"
                                          style="display:inline" data-confirm="Yakin hapus aset ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="link-danger">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <div class="pagination">
                {{ $assets->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
