<x-layouts.app>
    @php
        $sortLink = fn ($col) => route('locations.show', $location->id).'?'.http_build_query(array_merge(
            request()->except(['sort', 'direction']),
            ['sort' => $col, 'direction' => $sort === $col && $direction === 'asc' ? 'desc' : 'asc']
        ));
        $sortIcon = fn ($col) => $sort !== $col ? '' : ($direction === 'asc' ? '▲' : '▼');
    @endphp

    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Lokasi @if ($location->unit) — {{ $location->unit->name }} @endif</div>
            <h1>{{ $location->name }}</h1>
            <p>
                {{ $location->building ?? '-' }}
                @if ($location->floor) &nbsp;·&nbsp; Lantai {{ $location->floor }} @endif
                @if ($location->room_code) &nbsp;·&nbsp; Kode Ruang {{ $location->room_code }} @endif
            </p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('locations.dbr', $location->id) }}" target="_blank" class="btn btn-outline">🖨 Cetak DBR</a>
            @if (auth()->user()->isAdmin())
            <a href="{{ route('locations.edit', $location->id) }}" class="btn btn-outline">✏ Edit Lokasi</a>
            @endif
            <a href="{{ route('locations.index') }}" class="btn btn-outline">← Kembali</a>
        </div>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif
    @if (session('error'))
        <div class="alert-success" style="background: var(--danger-soft); color: var(--danger); border-left-color: var(--danger);">{{ session('error') }}</div>
    @endif

    @if ($location->notes)
        <div class="card" style="background: var(--sky-pale);">{{ $location->notes }}</div>
    @endif

    <div style="display:flex; gap:18px; flex-wrap:wrap; align-items: stretch; margin-bottom: 18px;">
        <div class="stat-grid" style="flex: 1; min-width: 280px; margin-bottom: 0; grid-template-columns: repeat(2, 1fr);">
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--accent">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="7" width="18" height="13" rx="1.5"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </div>
                <div class="stat-card__value">{{ number_format($totalAssets) }}</div>
                <div class="stat-card__label">Total Aset di Ruangan</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--ok">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v20M2 12h20" stroke-linecap="round"/></svg>
                </div>
                <div class="stat-card__value">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
                <div class="stat-card__label">Total Nilai Perolehan</div>
            </div>
        </div>

        <div class="card" style="width: 210px; flex-shrink: 0; margin-bottom: 0; text-align: center;">
            <div style="font-size: 11px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px;">QR Ruangan</div>
            <div style="display:flex; justify-content:center;">{!! $qrSvg !!}</div>
            <p style="font-size: 0.72rem; color: var(--muted); margin: 10px 0 8px;">Scan untuk buka info ruangan ini secara publik, tanpa login.</p>
            <a href="{{ route('locations.public-info', $location->id) }}" target="_blank" style="font-size: 0.75rem;">Buka halaman publik →</a>
        </div>
    </div>

    <div class="dash-columns">
        <div class="card">
            <div class="card__header"><h2 class="card__title">Rekap Kategori</h2></div>
            @if ($categoryRecap->isEmpty())
                <div class="empty-state">Belum ada aset untuk direkap.</div>
            @else
                @foreach ($categoryRecap as $row)
                    <div class="bar-row">
                        <span class="bar-row__label">{{ $row['name'] }}</span>
                        <div class="bar-track">
                            <div class="bar-fill bar-fill--accent" style="width: {{ $row['percent'] }}%"></div>
                        </div>
                        <span class="bar-row__value">{{ $row['total'] }}</span>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="card">
            <div class="card__header"><h2 class="card__title">Rekap Kondisi</h2></div>
            @if ($totalAssets === 0)
                <div class="empty-state">Belum ada aset untuk direkap.</div>
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
    </div>

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Aset di Ruangan Ini</h2>
            <span style="font-size: 0.85rem; color: var(--muted); font-weight: 600;">{{ $assets->count() }} dari {{ $totalAssets }} aset</span>
        </div>

        <form method="GET" action="{{ route('locations.show', $location->id) }}" class="filters">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, merk, no. seri...">
            <select name="category_id" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
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
            @if (request()->anyFilled(['search', 'category_id', 'condition', 'status']))
                <a href="{{ route('locations.show', $location->id) }}" class="btn btn-outline btn-sm">Reset</a>
            @endif
        </form>

        @if ($assets->count() === 0)
            <div class="empty-state">Tidak ada aset yang cocok dengan filter ini.</div>
        @else
            <form method="POST" action="{{ route('assets.bulk-destroy') }}">
                @csrf

                <div style="display:flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
                    @if (auth()->user()->isAdmin())
                    <p style="font-size: 0.8rem; color: var(--muted); margin: 0;">
                        Centang aset yang mau diproses — cetak sticker atau hapus sekaligus, tidak perlu satu per satu.
                    </p>
                    <div style="display:flex; gap:8px;">
                        <button type="submit" formaction="{{ route('assets.stickers') }}" formtarget="_blank" class="btn btn-sm btn-outline">🏷 Cetak Sticker</button>
                        <button type="submit" formaction="{{ route('assets.bulk-destroy') }}"
                                onclick="return confirm('Yakin hapus semua aset yang dicentang? Tindakan ini tidak bisa dibatalkan.')"
                                class="btn btn-sm btn-danger">🗑 Hapus yang Dicentang</button>
                    </div>
                    @endif
                </div>
                @error('asset_ids') <div class="form-error" style="margin-bottom: 10px;">{{ $message }}</div> @enderror

                <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            @if (auth()->user()->isAdmin())
                            <th style="width: 36px;">
                                <input type="checkbox" onclick="document.querySelectorAll('.asset-check').forEach(c => c.checked = this.checked)">
                            </th>
                            @endif
                            <th><a href="{{ $sortLink('name') }}" class="sort-link">Nama {{ $sortIcon('name') }}</a></th>
                            <th><a href="{{ $sortLink('brand') }}" class="sort-link">Merk {{ $sortIcon('brand') }}</a></th>
                            <th>Tipe/Seri</th>
                            <th><a href="{{ $sortLink('category') }}" class="sort-link">Kategori {{ $sortIcon('category') }}</a></th>
                            <th><a href="{{ $sortLink('condition') }}" class="sort-link">Kondisi {{ $sortIcon('condition') }}</a></th>
                            <th><a href="{{ $sortLink('status') }}" class="sort-link">Status {{ $sortIcon('status') }}</a></th>
                            @if (auth()->user()->isAdmin())
                            <th></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assets as $asset)
                            <tr>
                                @if (auth()->user()->isAdmin())
                                <td><input type="checkbox" class="asset-check" name="asset_ids[]" value="{{ $asset->id }}"></td>
                                @endif
                                <td>{{ $asset->name }}</td>
                                <td>{{ $asset->brand ?? '-' }}</td>
                                <td>{{ collect([$asset->model, $asset->serial_number])->filter()->implode(' / ') ?: '-' }}</td>
                                <td>{{ $asset->category?->name ?? '-' }}</td>
                                <td><span class="badge badge-{{ $asset->condition }}">{{ str_replace('_', ' ', $asset->condition) }}</span></td>
                                <td>{{ str_replace('_', ' ', $asset->status) }}</td>
                                @if (auth()->user()->isAdmin())
                                <td>
                                    <a href="{{ route('assets.edit', $asset->id) }}" class="icon-btn" title="Edit" aria-label="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </a>
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
            </form>
        @endif
    </div>
</x-layouts.app>
