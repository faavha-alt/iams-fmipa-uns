<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Keuangan</div>
            <h1>Anggaran Aset</h1>
            <p>Pagu dan realisasi belanja aset per prodi/unit, tahun anggaran {{ $year }}.</p>
        </div>
        <form method="GET" action="{{ route('budgets.index') }}">
            <select name="year" onchange="this.form.submit()">
                @foreach ($availableYears as $y)
                    <option value="{{ $y }}" @selected($y == $year)>Tahun {{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <div class="stat-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--accent">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v20M2 12h20" stroke-linecap="round"/></svg>
            </div>
            <div class="stat-card__value">Rp {{ number_format($totalPagu, 0, ',', '.') }}</div>
            <div class="stat-card__label">Total Pagu {{ $year }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--warn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="7" width="18" height="13" rx="1.5"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </div>
            <div class="stat-card__value">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</div>
            <div class="stat-card__label">Total Realisasi {{ $year }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <div class="stat-card__value">Rp {{ number_format($totalPagu - $totalRealisasi, 0, ',', '.') }}</div>
            <div class="stat-card__label">Sisa Anggaran {{ $year }}</div>
        </div>
    </div>

    @if (session('warning'))
        <div class="alert-success" style="background: var(--warn-soft); color: var(--warn); border-left-color: var(--warn);">{{ session('warning') }}</div>
    @endif

    @foreach ($faculties as $f)
        <div class="card">
            <div class="card__header">
                <h2 class="card__title"><a href="{{ route('budgets.show', $f['unit']->id) }}?year={{ $year }}" style="color: inherit;">{{ $f['unit']->name }}</a> — Ringkasan Fakultas</h2>
            </div>

            <div class="stat-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 16px;">
                <div class="stat-card" style="box-shadow: none; border: 1px solid var(--border); padding: 14px;">
                    <div class="stat-card__value" style="font-size: 1.1rem;">Rp {{ number_format($f['pagu_total'], 0, ',', '.') }}</div>
                    <div class="stat-card__label">Pagu Total Fakultas</div>
                </div>
                <div class="stat-card" style="box-shadow: none; border: 1px solid var(--border); padding: 14px;">
                    <div class="stat-card__value" style="font-size: 1.1rem;">Rp {{ number_format($f['alokasi_prodi'], 0, ',', '.') }}</div>
                    <div class="stat-card__label">Alokasi Prodi</div>
                </div>
                <div class="stat-card" style="box-shadow: none; border: 1px solid var(--border); padding: 14px;">
                    <div class="stat-card__value" style="font-size: 1.1rem; color: {{ $f['over_alokasi'] ? 'var(--danger)' : 'var(--navy)' }};">Rp {{ number_format($f['alokasi_fakultas'], 0, ',', '.') }}</div>
                    <div class="stat-card__label">Alokasi Fakultas (pagu total − alokasi prodi)</div>
                </div>
                <div class="stat-card" style="box-shadow: none; border: 1px solid var(--border); padding: 14px;">
                    <div class="stat-card__value" style="font-size: 1.1rem; color: {{ $f['over_realisasi'] ? 'var(--danger)' : 'var(--ok)' }};">Rp {{ number_format($f['sisa_riil'], 0, ',', '.') }}</div>
                    <div class="stat-card__label">Sisa Riil (Pagu Total − Realisasi)</div>
                </div>
            </div>

            <div class="bar-row">
                <span class="bar-row__label">Porsi alokasi prodi</span>
                <div class="bar-track">
                    <div class="bar-fill bar-fill--accent" style="width: {{ min(100, $f['percent_alokasi_prodi']) }}%;"></div>
                </div>
                <span class="bar-row__value">{{ $f['percent_alokasi_prodi'] }}%</span>
            </div>
            <div class="bar-row">
                <span class="bar-row__label">Realisasi riil</span>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ min(100, $f['percent_realisasi']) }}%; background: {{ $f['over_realisasi'] ? 'var(--danger)' : 'var(--green)' }};"></div>
                </div>
                <span class="bar-row__value">{{ $f['percent_realisasi'] }}%</span>
            </div>

            <p style="font-size: 0.78rem; color: var(--muted); margin-top: 14px; margin-bottom: 14px;">
                Realisasi langsung fakultas (bukan milik satu prodi): <strong>Rp {{ number_format($f['realisasi_sendiri'], 0, ',', '.') }}</strong>
                &nbsp;·&nbsp; Realisasi seluruh prodi di bawahnya: <strong>Rp {{ number_format($f['realisasi_anak'], 0, ',', '.') }}</strong>
            </p>

            @if (auth()->user()->isAdmin())
            <details class="review-panel" style="padding: 0;">
                <summary>Atur Pagu Fakultas</summary>
                <div class="review-panel__body">
                    <form method="POST" action="{{ route('budgets.store', $f['unit']->id) }}">
                        @csrf
                        <input type="hidden" name="fiscal_year" value="{{ $year }}">
                        <div class="form-group" style="max-width: 280px;">
                            <label>Pagu Total Fakultas {{ $year }} (Rp)</label>
                            <input type="number" step="0.01" name="amount" value="{{ $f['pagu_total'] }}">
                        </div>
                        <p style="font-size: 0.78rem; color: var(--muted); margin: 4px 0 10px;">Angka ini mencakup alokasi prodi + belanja langsung fakultas. Alokasi fakultas dihitung otomatis = pagu total − jumlah pagu semua prodi.</p>
                        <button type="submit" class="btn btn-sm">Simpan</button>
                    </form>
                </div>
            </details>
            @endif
        </div>
    @endforeach

    <h2 style="font-family: 'Montserrat', sans-serif; font-size: 1rem; font-weight: 700; color: var(--navy); margin: 24px 0 12px;">Per Prodi / Unit</h2>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Prodi / Unit</th>
                    <th>Pagu</th>
                    <th>Realisasi</th>
                    <th>Sisa</th>
                    <th>Penyerapan</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($units as $row)
                    <tr>
                        <td><a href="{{ route('budgets.show', $row['unit']->id) }}?year={{ $year }}">{{ $row['unit']->name }}</a></td>
                        <td>Rp {{ number_format($row['pagu'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($row['realisasi'], 0, ',', '.') }}</td>
                        <td style="color: {{ $row['over_budget'] ? 'var(--danger)' : 'var(--ink)' }}; font-weight: {{ $row['over_budget'] ? '700' : '400' }};">
                            Rp {{ number_format($row['sisa'], 0, ',', '.') }}
                            @if ($row['over_budget'])
                                <span class="badge badge-rusak_berat">lebih pagu</span>
                            @endif
                        </td>
                        <td style="min-width: 120px;">
                            <div class="bar-track">
                                <div class="bar-fill {{ $row['percent'] >= 100 ? 'bar-fill--accent' : 'bar-fill--neutral' }}"
                                     style="width: {{ min(100, $row['percent']) }}%; background: {{ $row['over_budget'] ? 'var(--danger)' : ($row['percent'] >= 80 ? 'var(--warn)' : 'var(--accent)') }};"></div>
                            </div>
                            <span style="font-size: 0.72rem; color: var(--ink-muted);">{{ $row['percent'] }}%</span>
                        </td>
                        <td>
                            @if (auth()->user()->isAdmin())
                            <details class="review-panel">
                                <summary>Atur Pagu</summary>
                                <div class="review-panel__body">
                                    <form method="POST" action="{{ route('budgets.store', $row['unit']->id) }}">
                                        @csrf
                                        <input type="hidden" name="fiscal_year" value="{{ $year }}">
                                        <div class="form-group">
                                            <label>Pagu Anggaran {{ $year }} (Rp)</label>
                                            <input type="number" step="0.01" name="amount" value="{{ $row['pagu'] }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Catatan (opsional)</label>
                                            <input type="text" name="notes" placeholder="Sumber dana, no. SK, dll.">
                                        </div>
                                        <button type="submit" class="btn btn-sm">Simpan</button>
                                    </form>
                                </div>
                            </details>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.app>
