<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Administrasi</div>
            <h1>Program Studi & Unit</h1>
            <p>Struktur fakultas, departemen, program studi, dan laboratorium.</p>
        </div>
        <a href="{{ route('units.create') }}" class="btn">+ Tambah Unit</a>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    @if (session('error'))
        <div class="alert-success" style="background: var(--danger-soft); color: var(--danger); border-left-color: var(--danger);">{{ session('error') }}</div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('units.index') }}" class="filters">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama unit...">
            <select name="type" onchange="this.form.submit()">
                <option value="">Semua Tipe</option>
                <option value="fakultas" @selected(request('type') == 'fakultas')>Fakultas</option>
                <option value="departemen" @selected(request('type') == 'departemen')>Departemen</option>
                <option value="program_studi" @selected(request('type') == 'program_studi')>Program Studi</option>
                <option value="laboratorium" @selected(request('type') == 'laboratorium')>Laboratorium</option>
                <option value="unit_kerja" @selected(request('type') == 'unit_kerja')>Unit Kerja</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Cari</button>
        </form>

        @if ($units->count() === 0)
            <div class="empty-state">Belum ada unit yang cocok.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Induk</th>
                        <th>Kepala Unit</th>
                        <th>Aset</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                        <tr>
                            <td><span class="code-chip">{{ $unit->code }}</span></td>
                            <td>{{ $unit->name }}</td>
                            <td>{{ str_replace('_', ' ', $unit->type) }}</td>
                            <td>{{ $unit->parent?->name ?? '-' }}</td>
                            <td>{{ $unit->head?->name ?? '-' }}</td>
                            <td>{{ $unit->assets_count }}</td>
                            <td>
                                @if ($unit->is_active)
                                    <span class="badge badge-baik">Aktif</span>
                                @else
                                    <span class="badge badge-hilang">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('units.edit', $unit->id) }}">Edit</a>
                                &nbsp;·&nbsp;
                                <form method="POST" action="{{ route('units.toggle-active', $unit->id) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="link-danger" style="color: {{ $unit->is_active ? 'var(--danger)' : 'var(--ok)' }};">
                                        {{ $unit->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                &nbsp;·&nbsp;
                                <form method="POST" action="{{ route('units.destroy', $unit->id) }}" style="display:inline" data-confirm="Yakin hapus {{ $unit->name }}? Ini permanen.">
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
                {{ $units->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
