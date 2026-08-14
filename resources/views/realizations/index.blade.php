<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Pengadaan</div>
            <h1>Semua Barang {{ $year }}</h1>
            <p>Laporan lintas-vendor — cari & pantau status finalisasi semua barang sekaligus.</p>
        </div>
        <a href="{{ route('realizations.create') }}" class="btn">+ Tambah Barang</a>
    </div>

    <div style="display:flex; gap:6px; margin-bottom: 20px;">
        <a href="{{ route('procurement-batches.index') }}" class="btn btn-outline btn-sm">Daftar Pengadaan</a>
        <a href="{{ route('realizations.index') }}" class="btn btn-sm" style="background: var(--navy); border-color: var(--navy);">Semua Barang (lintas vendor)</a>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <div class="stat-grid" style="grid-template-columns: repeat(2, 1fr);">
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--warn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v5" stroke-linecap="round"/><circle cx="12" cy="16.5" r="0.5" fill="currentColor"/><path d="M10.3 3.9 2.5 17.5A1.8 1.8 0 0 0 4 20.2h16a1.8 1.8 0 0 0 1.5-2.7L13.7 3.9a1.8 1.8 0 0 0-3.4 0Z"/></svg>
            </div>
            <div class="stat-card__value">Rp {{ number_format($totalBelumFinal, 0, ',', '.') }}</div>
            <div class="stat-card__label">Belum Difinalisasi Jadi Aset</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <div class="stat-card__value">Rp {{ number_format($totalSudahFinal, 0, ',', '.') }}</div>
            <div class="stat-card__label">Sudah Jadi Aset Resmi</div>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('realizations.index') }}" class="filters">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang...">
            <select name="year" onchange="this.form.submit()">
                @foreach (range(now()->year + 1, now()->year - 3) as $y)
                    <option value="{{ $y }}" @selected($y == $year)>Tahun {{ $y }}</option>
                @endforeach
            </select>
            <select name="unit_id" onchange="this.form.submit()">
                <option value="">Semua Unit</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>{{ $unit->name }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="belum_final" @selected(request('status') == 'belum_final')>Belum Final</option>
                <option value="sudah_final" @selected(request('status') == 'sudah_final')>Sudah Final</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Cari</button>
        </form>
    </div>

    @if ($grouped->count() === 0)
        <div class="card">
            <div class="empty-state">Belum ada realisasi belanja tahun {{ $year }} yang cocok dengan filter ini.</div>
        </div>
    @else
        @foreach ($grouped as $vendorName => $items)
            <details class="card" style="padding: 0;">
                <summary style="cursor: pointer; padding: 20px 22px; display: flex; justify-content: space-between; align-items: center; list-style: none;">
                    <span style="font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 1rem; color: var(--navy);">{{ $vendorName }}</span>
                    <span style="font-size: 0.85rem; color: var(--muted); font-weight: 600;">
                        {{ $items->count() }} item · Rp {{ number_format($items->sum('cost'), 0, ',', '.') }}
                    </span>
                </summary>

                <div style="padding: 0 22px 20px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Barang</th>
                                <th>Unit</th>
                                <th>Jml</th>
                                <th>Biaya</th>
                                <th>Periode</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $r)
                                <tr>
                                    <td>{{ $r->purchase_date->format('d M Y') }}</td>
                                    <td>{{ $r->item_name }}</td>
                                    <td>{{ $r->unit->name }}</td>
                                    <td>{{ $r->quantity }}</td>
                                    <td>Rp {{ number_format($r->cost, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($r->procurementBatch)
                                            <a href="{{ route('procurement-batches.show', $r->procurementBatch->id) }}" style="font-size: 0.78rem;">{{ $r->procurementBatch->nama }}</a>
                                        @else
                                            <span class="badge badge-hilang">Belum dikelompokkan</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($r->status === 'sudah_final')
                                            <span class="badge badge-baik">Sudah Final</span>
                                        @else
                                            <span class="badge badge-diajukan">Belum Final</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($r->status === 'belum_final')
                                            <div class="row-actions">
                                                <a href="{{ route('realizations.finalize-form', $r->id) }}" class="action-pill action-pill--ok" title="Finalisasi jadi Aset">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                                    Finalisasi
                                                </a>
                                                <a href="{{ route('realizations.edit', $r->id) }}" class="icon-btn" title="Edit" aria-label="Edit">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                                </a>
                                                <form method="POST" action="{{ route('realizations.destroy', $r->id) }}" style="display:inline" data-confirm="Yakin hapus realisasi {{ $r->item_name }}? Sisa anggaran akan dikembalikan.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="icon-btn icon-btn--danger" title="Hapus" aria-label="Hapus">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span style="color: var(--muted); font-size: 0.8rem;">{{ $r->assets_count }} aset dibuat</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @endforeach
    @endif
</x-layouts.app>
