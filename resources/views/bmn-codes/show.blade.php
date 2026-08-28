<x-layouts.app>
    @php
        $sortLink = fn ($col) => route('bmn-codes.show', $bmnCode->id).'?'.http_build_query(array_merge(
            request()->except(['sort', 'direction']),
            ['sort' => $col, 'direction' => $sort === $col && $direction === 'asc' ? 'desc' : 'asc']
        ));
        $sortIcon = fn ($col) => $sort !== $col ? '' : ($direction === 'asc' ? '▲' : '▼');
    @endphp

    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Kode Barang SIMAK BMN</div>
            <h1><span class="code-chip">{{ $bmnCode->kode }}</span> {{ $bmnCode->nama }}</h1>
            <p>Semua aset yang tercatat memakai kode BMN ini.</p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('bmn-codes.edit', $bmnCode->id) }}" class="btn btn-outline">✏ Edit Kode</a>
            <a href="{{ route('bmn-codes.index') }}" class="btn btn-outline">← Kembali</a>
        </div>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <div class="stat-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--accent">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="7" width="18" height="13" rx="1.5"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </div>
            <div class="stat-card__value">{{ number_format($totalAssets) }}</div>
            <div class="stat-card__label">Total Aset dengan Kode Ini</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v20M2 12h20" stroke-linecap="round"/></svg>
            </div>
            <div class="stat-card__value">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
            <div class="stat-card__label">Total Nilai Perolehan</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--accent">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <div class="stat-card__value">{{ $conditions->firstWhere('key', 'baik')['percent'] ?? 0 }}%</div>
            <div class="stat-card__label">Kondisi Baik</div>
        </div>
    </div>

    <div class="card">
        <div class="card__header"><h2 class="card__title">Rekap Kondisi</h2></div>
        @if ($totalAssets === 0)
            <div class="empty-state">Belum ada aset yang pakai kode ini.</div>
        @else
            @foreach ($conditions as $row)
                <div class="bar-row">
                    <span class="bar-row__label">{{ str_replace('_', ' ', $row['key']) }}</span>
                    <div class="bar-track">
                        <div class="bar-fill badge-{{ $row['key'] }}-bar" style="width: {{ $row['percent'] }}%"></div>
                    </div>
                    <span class="bar-row__value">{{ $row['total'] }}</span>
                </div>
            @endforeach
        @endif
    </div>

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Daftar Aset</h2>
            <span style="font-size: 0.85rem; color: var(--muted); font-weight: 600;">{{ $assets->count() }} dari {{ $totalAssets }} aset</span>
        </div>

        <form method="GET" action="{{ route('bmn-codes.show', $bmnCode->id) }}" class="filters">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, merk, no. seri...">
            <select name="condition" onchange="this.form.submit()">
                <option value="">Semua Kondisi</option>
                <option value="baik" @selected(request('condition') == 'baik')>Baik</option>
                <option value="rusak_ringan" @selected(request('condition') == 'rusak_ringan')>Rusak Ringan</option>
                <option value="rusak_berat" @selected(request('condition') == 'rusak_berat')>Rusak Berat</option>
                <option value="hilang" @selected(request('condition') == 'hilang')>Hilang</option>
            </select>
            <select name="status" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aktif" @selected(request('status') == 'aktif')>Aktif</option>
                <option value="dalam_perbaikan" @selected(request('status') == 'dalam_perbaikan')>Dalam Perbaikan</option>
                <option value="dipinjamkan" @selected(request('status') == 'dipinjamkan')>Dipinjamkan</option>
                <option value="dihapuskan" @selected(request('status') == 'dihapuskan')>Dihapuskan</option>
            </select>
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <button type="submit" class="btn btn-outline btn-sm">Cari</button>
            @if (request()->anyFilled(['search', 'condition', 'status']))
                <a href="{{ route('bmn-codes.show', $bmnCode->id) }}" class="btn btn-outline btn-sm">Reset</a>
            @endif
        </form>

        @if ($assets->count() === 0)
            <div class="empty-state">Tidak ada aset yang cocok dengan filter ini.</div>
        @else
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th><a href="{{ $sortLink('name') }}" class="sort-link">Nama {{ $sortIcon('name') }}</a></th>
                        <th><a href="{{ $sortLink('brand') }}" class="sort-link">Merk {{ $sortIcon('brand') }}</a></th>
                        <th>Tipe/Seri</th>
                        <th><a href="{{ $sortLink('unit') }}" class="sort-link">Unit {{ $sortIcon('unit') }}</a></th>
                        <th>Lokasi</th>
                        <th><a href="{{ $sortLink('condition') }}" class="sort-link">Kondisi {{ $sortIcon('condition') }}</a></th>
                        <th><a href="{{ $sortLink('status') }}" class="sort-link">Status {{ $sortIcon('status') }}</a></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $asset)
                        <tr>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->brand ?? '-' }}</td>
                            <td>{{ collect([$asset->model, $asset->serial_number])->filter()->implode(' / ') ?: '-' }}</td>
                            <td>{{ $asset->unit?->name ?? '-' }}</td>
                            <td>{{ $asset->location?->name ?? '-' }}</td>
                            <td><span class="badge badge-{{ $asset->condition }}">{{ str_replace('_', ' ', $asset->condition) }}</span></td>
                            <td><span class="badge badge-{{ $asset->status }}">{{ str_replace('_', ' ', $asset->status) }}</span></td>
                            <td>
                                <a href="{{ route('assets.edit', $asset->id) }}" class="icon-btn" title="Edit Aset" aria-label="Edit Aset">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </a>
                            </td>
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
