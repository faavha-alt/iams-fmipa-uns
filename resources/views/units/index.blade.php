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
                            <td><a href="{{ route('units.show', $unit->id) }}"><span class="code-chip">{{ $unit->code }}</span></a></td>
                            <td><a href="{{ route('units.show', $unit->id) }}" style="color: var(--ink); text-decoration: none; font-weight: 500;">{{ $unit->name }}</a></td>
                            <td>{{ str_replace('_', ' ', $unit->type) }}</td>
                            <td>{{ $unit->parent?->name ?? '-' }}</td>
                            <td>{{ $unit->head?->name ?? '-' }}</td>
                            <td>
                                @if ($unit->assets_count > 0)
                                    <a href="{{ route('units.show', $unit->id) }}" class="badge badge-baik">{{ $unit->assets_count }} aset</a>
                                @else
                                    <span class="badge badge-hilang">0 aset</span>
                                @endif
                            </td>
                            <td>
                                @if ($unit->is_active)
                                    <span class="badge badge-baik">Aktif</span>
                                @else
                                    <span class="badge badge-hilang">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('units.show', $unit->id) }}" class="icon-btn" title="Lihat Detail" aria-label="Lihat Detail">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="{{ route('units.edit', $unit->id) }}" class="icon-btn" title="Edit" aria-label="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('units.toggle-active', $unit->id) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="action-pill {{ $unit->is_active ? 'action-pill--danger' : 'action-pill--ok' }}">
                                            {{ $unit->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('units.destroy', $unit->id) }}" style="display:inline" data-confirm="Yakin hapus {{ $unit->name }}? Ini permanen.">
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
                {{ $units->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
