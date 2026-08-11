<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Ringkasan</div>
            <h1>Dashboard</h1>
            <p>{{ $isAdmin ? 'Kondisi aset di seluruh unit FMIPA UNS, per hari ini.' : 'Kondisi aset di unit Anda: ' . auth()->user()->unit?->name }}</p>
        </div>
    </div>

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
            <div class="stat-card__icon stat-card__icon--warn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v5" stroke-linecap="round"/><circle cx="12" cy="16.5" r="0.5" fill="currentColor"/><path d="M10.3 3.9 2.5 17.5A1.8 1.8 0 0 0 4 20.2h16a1.8 1.8 0 0 0 1.5-2.7L13.7 3.9a1.8 1.8 0 0 0-3.4 0Z"/></svg>
            </div>
            <div class="stat-card__value">{{ $pendingRequests }}</div>
            <div class="stat-card__label">Pengajuan Menunggu</div>
            <a href="{{ route('requests.index') }}" class="stat-card__link">Lihat semua →</a>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--accent">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <div class="stat-card__value">{{ $conditions->firstWhere('key', 'baik')['percent'] ?? 0 }}%</div>
            <div class="stat-card__label">Kondisi Baik</div>
        </div>
    </div>

    @if ($budgetSummary)
        <div class="card">
            <div class="card__header">
                <h2 class="card__title">Anggaran Aset {{ now()->year }}</h2>
                <a href="{{ route('budgets.index') }}" class="btn btn-outline btn-sm">Kelola Pagu</a>
            </div>
            <div class="bar-row" style="grid-template-columns: 90px 1fr auto;">
                <span class="bar-row__label">Terpakai</span>
                <div class="bar-track">
                    <div class="bar-fill bar-fill--accent" style="width: {{ $budgetSummary['pagu'] > 0 ? min(100, round(($budgetSummary['realisasi'] / $budgetSummary['pagu']) * 100)) : 0 }}%"></div>
                </div>
                <span class="bar-row__value" style="width: auto;">Rp {{ number_format($budgetSummary['realisasi'], 0, ',', '.') }} / Rp {{ number_format($budgetSummary['pagu'], 0, ',', '.') }}</span>
            </div>
        </div>
    @endif

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

    @if ($isAdmin && $unitBreakdown->count() > 0)
        <div class="card">
            <div class="card__header"><h2 class="card__title">Aset per Unit</h2></div>
            @foreach ($unitBreakdown as $row)
                <div class="bar-row">
                    <span class="bar-row__label">{{ $row['name'] }}</span>
                    <div class="bar-track">
                        <div class="bar-fill bar-fill--accent" style="width: {{ $row['percent'] }}%"></div>
                    </div>
                    <span class="bar-row__value">{{ $row['total'] }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="dash-columns">
        <div class="card">
            <div class="card__header">
                <h2 class="card__title">Aset Terbaru</h2>
                <a href="{{ route('assets.index') }}" class="btn btn-outline btn-sm">Lihat semua</a>
            </div>
            @if ($recentAssets->isEmpty())
                <div class="empty-state">Belum ada aset.</div>
            @else
                <div class="mini-list">
                    @foreach ($recentAssets as $asset)
                        <div class="mini-list__row">
                            <div>
                                <span class="code-chip">{{ $asset->asset_code }}</span>
                                <div class="mini-list__title">{{ $asset->name }}</div>
                            </div>
                            <span class="badge badge-{{ $asset->condition }}">{{ str_replace('_', ' ', $asset->condition) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card__header">
                <h2 class="card__title">Pengajuan Terbaru</h2>
                <a href="{{ route('requests.index') }}" class="btn btn-outline btn-sm">Lihat semua</a>
            </div>
            @if ($recentRequests->isEmpty())
                <div class="empty-state">Belum ada pengajuan.</div>
            @else
                <div class="mini-list">
                    @foreach ($recentRequests as $req)
                        <div class="mini-list__row">
                            <div>
                                <div class="mini-list__title">{{ $req->item_name }}</div>
                                <span style="color: var(--ink-muted); font-size: 0.78rem;">{{ $req->unit->name }}</span>
                            </div>
                            <span class="badge badge-{{ $req->status }}">{{ $req->status }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
