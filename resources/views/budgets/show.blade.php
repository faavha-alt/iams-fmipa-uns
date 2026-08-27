<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Keuangan · {{ $unit->name }}</div>
            <h1>Detail Anggaran & Barang</h1>
            <p>Kode unit: <span class="code-chip">{{ $unit->code }}</span></p>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <form method="GET" action="{{ route('budgets.show', $unit->id) }}">
                <select name="year" onchange="this.form.submit()">
                    @foreach ($availableYears as $y)
                        <option value="{{ $y }}" @selected($y == $year)>Tahun {{ $y }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('budgets.index', ['year' => $year]) }}" class="btn btn-outline">← Kembali</a>
        </div>
    </div>

    <div class="stat-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card">
            <div class="stat-card__value">Rp {{ number_format($pagu, 0, ',', '.') }}</div>
            <div class="stat-card__label">Pagu {{ $year }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</div>
            <div class="stat-card__label">Realisasi {{ $year }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value" style="color: {{ $sisa < 0 ? 'var(--danger)' : 'var(--navy)' }};">Rp {{ number_format($sisa, 0, ',', '.') }}</div>
            <div class="stat-card__label">Sisa Anggaran</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value">{{ $assets->count() }}</div>
            <div class="stat-card__label">Aset Tercatat {{ $year }}</div>
        </div>
    </div>

    @if ($unit->type === 'fakultas' && $children->count() > 0)
        <div class="card">
            <div class="card__header"><h2 class="card__title">Breakdown per Prodi</h2></div>
            <table>
                <thead>
                    <tr>
                        <th>Prodi/Unit</th>
                        <th>Pagu</th>
                        <th>Realisasi</th>
                        <th>Sisa</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($children as $row)
                        <tr>
                            <td>{{ $row['unit']->name }}</td>
                            <td>Rp {{ number_format($row['pagu'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($row['realisasi'], 0, ',', '.') }}</td>
                            <td style="color: {{ $row['over_budget'] ? 'var(--danger)' : 'var(--ink)' }};">Rp {{ number_format($row['sisa'], 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('budgets.show', $row['unit']->id) }}?year={{ $year }}" class="icon-btn" title="Detail" aria-label="Detail">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="card">
        <div class="card__header"><h2 class="card__title">Aset Tercatat ({{ $year }}){{ $isFakultas ? ' · seluruh prodi' : '' }}</h2></div>
        @if ($assets->isEmpty())
            <div class="empty-state">Belum ada aset tercatat tahun ini.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Kode Aset</th>
                        @if ($isFakultas)<th>Prodi/Unit</th>@endif
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $asset)
                        <tr>
                            <td><span class="code-chip">{{ $asset->asset_code }}</span></td>
                            @if ($isFakultas)<td>{{ $asset->unit?->name ?? '-' }}</td>@endif
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->category?->name ?? '-' }}</td>
                            <td>{{ $asset->acquisition_date?->format('d M Y') ?? '-' }}</td>
                            <td>Rp {{ number_format($asset->acquisition_value, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <div class="card__header"><h2 class="card__title">Realisasi Belanja ({{ $year }}){{ $isFakultas ? ' · seluruh prodi' : '' }}</h2></div>
        @if ($realizations->isEmpty())
            <div class="empty-state">Belum ada realisasi belanja tahun ini.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        @if ($isFakultas)<th>Prodi/Unit</th>@endif
                        <th>Jumlah</th>
                        <th>Biaya</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($realizations as $r)
                        <tr>
                            <td>{{ $r->item_name }}</td>
                            @if ($isFakultas)<td>{{ $r->unit?->name ?? '-' }}</td>@endif
                            <td>{{ $r->quantity }}</td>
                            <td>Rp {{ number_format($r->cost, 0, ',', '.') }}</td>
                            <td>{{ $r->purchase_date->format('d M Y') }}</td>
                            <td>
                                @if ($r->status === 'sudah_final')
                                    <span class="badge badge-baik">Sudah Final</span>
                                @else
                                    <span class="badge badge-diajukan">Belum Final</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <div class="card__header"><h2 class="card__title">Riwayat Pengajuan ({{ $year }}){{ $isFakultas ? ' · seluruh prodi' : '' }}</h2></div>
        @if ($requests->isEmpty())
            <div class="empty-state">Belum ada pengajuan tahun ini.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        @if ($isFakultas)<th>Prodi/Unit</th>@endif
                        <th>Jumlah</th>
                        <th>Estimasi Biaya</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $req)
                        <tr>
                            <td>{{ $req->item_name }}</td>
                            @if ($isFakultas)<td>{{ $req->unit?->name ?? '-' }}</td>@endif
                            <td>{{ $req->quantity }}</td>
                            <td>{{ $req->estimated_cost ? 'Rp '.number_format($req->estimated_cost, 0, ',', '.') : '-' }}</td>
                            <td><span class="badge badge-{{ $req->status }}">{{ $req->status }}</span></td>
                            <td>{{ $req->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.app>
