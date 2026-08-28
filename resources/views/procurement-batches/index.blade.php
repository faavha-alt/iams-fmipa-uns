<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Pengadaan</div>
            <h1>Daftar Pengadaan</h1>
            <p>Tiap pengadaan punya satu vendor dan daftar barangnya sendiri.</p>
        </div>
        <a href="{{ route('procurement-batches.create') }}" class="btn">+ Buat Pengadaan</a>
    </div>

    <div style="display:flex; gap:6px; margin-bottom: 20px;">
        <a href="{{ route('procurement-batches.index') }}" class="btn btn-sm" style="background: var(--navy); border-color: var(--navy);">Daftar Pengadaan</a>
        <a href="{{ route('realizations.index') }}" class="btn btn-outline btn-sm">Semua Barang (lintas vendor)</a>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    @if ($orphanCount > 0)
        <div class="alert-success" style="background: var(--gold-pale); color: #92660F; border-left-color: var(--gold);">
            Ada <strong>{{ $orphanCount }}</strong> realisasi belanja yang belum masuk periode manapun. Buka salah satu periode di bawah, lalu tambahkan lewat panel "Tambahkan Realisasi yang Sudah Ada".
        </div>
    @endif

    <div class="stat-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card">
            <div class="stat-card__value">{{ $stats['total_batches'] }}</div>
            <div class="stat-card__label">Pengadaan</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value">{{ number_format($stats['total_items'], 0, ',', '.') }}</div>
            <div class="stat-card__label">Total Barang</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value">Rp {{ number_format($stats['total_value'], 0, ',', '.') }}</div>
            <div class="stat-card__label">Total Nilai Pengadaan</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value">{{ $stats['serapan_persen'] !== null ? $stats['serapan_persen'] . '%' : '—' }}</div>
            <div class="stat-card__label">Serapan Pagu {{ $stats['year'] }}</div>
        </div>
    </div>

    @if ($stats['pagu_tahun_ini'] > 0)
        <p style="font-size: 0.8rem; color: var(--muted); margin: -8px 0 18px;">
            Nilai pengadaan {{ $stats['year'] }}: <strong>Rp {{ number_format($stats['nilai_tahun_ini'], 0, ',', '.') }}</strong>
            dari pagu <strong>Rp {{ number_format($stats['pagu_tahun_ini'], 0, ',', '.') }}</strong>.
        </p>
    @endif

    @if ($stats['per_category']->isNotEmpty())
        <div class="card">
            <div class="card__header"><h2 class="card__title">Penyerapan Anggaran per Kategori Alat</h2></div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Kategori Alat</th>
                            <th class="num">Barang</th>
                            <th class="num">Total Nilai</th>
                            <th class="num">% Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stats['per_category'] as $c)
                            <tr>
                                <td>{{ $c['nama'] }}</td>
                                <td class="num">{{ number_format($c['jumlah'], 0, ',', '.') }}</td>
                                <td class="num">Rp {{ number_format($c['nilai'], 0, ',', '.') }}</td>
                                <td class="num">
                                    <div class="cell-bar">
                                        <div class="bar-track"><div class="bar-fill bar-fill--accent" style="width: {{ min(100, $c['persen']) }}%;"></div></div>
                                        <span class="cell-bar__val">{{ $c['persen'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('procurement-batches.index') }}" class="filters">
            <select name="status" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="berjalan" @selected(request('status') == 'berjalan')>Berjalan</option>
                <option value="selesai" @selected(request('status') == 'selesai')>Selesai</option>
            </select>
        </form>

        @if ($batches->count() === 0)
            <div class="empty-state">Belum ada periode pengadaan. Buat satu buat mulai mengelompokkan realisasi belanja.</div>
        @else
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nama Periode</th>
                        <th>Vendor / CV</th>
                        <th class="nowrap">Tanggal</th>
                        <th class="num">Barang</th>
                        <th class="num">Total Nilai</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($batches as $batch)
                        <tr>
                            <td>{{ $batch->nama }}</td>
                            <td>@if ($batch->vendor){{ $batch->vendor->name }}@else<span class="cell-dim">—</span>@endif</td>
                            <td class="nowrap">
                                {{ $batch->tanggal_mulai?->format('d M Y') ?? '-' }}
                                @if ($batch->tanggal_selesai)
                                    &ndash; {{ $batch->tanggal_selesai->format('d M Y') }}
                                @endif
                            </td>
                            <td class="num">{{ number_format($batch->realizations_count, 0, ',', '.') }}</td>
                            <td class="num">Rp {{ number_format($batch->realizations_sum_cost ?? 0, 0, ',', '.') }}</td>
                            <td>
                                @if ($batch->status === 'selesai')
                                    <span class="badge badge-baik">Selesai</span>
                                @else
                                    <span class="badge badge-diajukan">Berjalan</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('procurement-batches.show', $batch->id) }}" class="icon-btn" title="Lihat" aria-label="Lihat">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="{{ route('procurement-batches.edit', $batch->id) }}" class="icon-btn" title="Edit" aria-label="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <div class="pagination">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
