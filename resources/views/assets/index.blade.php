<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Inventaris</div>
            <h1>Daftar Aset</h1>
            <p>{{ $isAdmin ? 'Seluruh unit di FMIPA UNS.' : 'Aset yang tercatat di unit Anda: ' . auth()->user()->unit?->name }}</p>
        </div>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('assets.import') }}" class="btn btn-outline">⬆ Import CSV</a>
            <a href="{{ route('assets.create') }}" class="btn">+ Tambah Aset</a>
        @endif
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--accent">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="7" width="18" height="13" rx="1.5"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </div>
            <div class="stat-card__value">{{ number_format($totalAssets) }}</div>
            <div class="stat-card__label">Total Aset</div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v20M2 12h20" stroke-linecap="round"/></svg>
            </div>
            <div class="stat-card__value">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
            <div class="stat-card__label">Nilai Perolehan</div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--accent">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <div class="stat-card__value">{{ $goodConditionPercent }}%</div>
            <div class="stat-card__label">Kondisi Baik</div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--warn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v5" stroke-linecap="round"/><circle cx="12" cy="16.5" r="0.5" fill="currentColor"/><path d="M10.3 3.9 2.5 17.5A1.8 1.8 0 0 0 4 20.2h16a1.8 1.8 0 0 0 1.5-2.7L13.7 3.9a1.8 1.8 0 0 0-3.4 0Z"/></svg>
            </div>
            <div class="stat-card__value">{{ $needsAttentionCount }}</div>
            <div class="stat-card__label">Perlu Perhatian</div>
        </div>
    </div>

    <div class="dash-columns">
        <div class="card">
            <div class="card__header"><h2 class="card__title">Kondisi Aset</h2></div>
            @foreach ($conditions as $row)
                <div class="bar-row">
                    <span class="bar-row__label">{{ str_replace('_', ' ', $row['key']) }}</span>
                    <div class="bar-track">
                        <div class="bar-fill badge-{{ $row['key'] }}-bar" style="width: {{ $row['percent'] }}%"></div>
                    </div>
                    <span class="bar-row__value">{{ $row['total'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="card__header"><h2 class="card__title">Status Pemakaian</h2></div>
            @foreach ($statuses as $row)
                <div class="bar-row">
                    <span class="bar-row__label">{{ str_replace('_', ' ', $row['key']) }}</span>
                    <div class="bar-track">
                        <div class="bar-fill bar-fill--neutral" style="width: {{ $row['percent'] }}%"></div>
                    </div>
                    <span class="bar-row__value">{{ $row['total'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

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
                        @if (auth()->user()->isAdmin())
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
                            <td class="col-badge"><span class="badge badge-{{ $asset->status }}">{{ str_replace('_', ' ', $asset->status) }}</span></td>
                            @if (auth()->user()->isAdmin())
                                <td class="col-actions">
                                    <div class="row-actions">
                                        <a href="{{ route('assets.edit', $asset->id) }}" class="icon-btn" title="Edit" aria-label="Edit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('assets.destroy', $asset->id) }}"
                                              style="display:inline" data-confirm="Yakin hapus aset ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn icon-btn--danger" title="Hapus" aria-label="Hapus">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                            </button>
                                        </form>
                                    </div>
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
