<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Master Data</div>
            <h1>Kode Barang SIMAK BMN</h1>
            <p>Daftar kode dan nama barang resmi, dipakai saat isi data SIMAK BMN di form aset.</p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('bmn-codes.import') }}" class="btn btn-outline">⬆ Import CSV</a>
            <a href="{{ route('bmn-codes.create') }}" class="btn">+ Tambah Kode</a>
        </div>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--accent">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
            </div>
            <div class="stat-card__value">{{ number_format($totalCodes) }}</div>
            <div class="stat-card__label">Total Kode BMN</div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <div class="stat-card__value">{{ number_format($codesInUse) }}</div>
            <div class="stat-card__label">Kode Terpakai</div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--accent">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="7" width="18" height="13" rx="1.5"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </div>
            <div class="stat-card__value">{{ number_format($assetsWithCode) }}</div>
            <div class="stat-card__label">Aset Sudah Berkode</div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--warn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v5" stroke-linecap="round"/><circle cx="12" cy="16.5" r="0.5" fill="currentColor"/><path d="M10.3 3.9 2.5 17.5A1.8 1.8 0 0 0 4 20.2h16a1.8 1.8 0 0 0 1.5-2.7L13.7 3.9a1.8 1.8 0 0 0-3.4 0Z"/></svg>
            </div>
            <div class="stat-card__value">{{ number_format($assetsWithoutCode) }}</div>
            <div class="stat-card__label">Aset Belum Berkode</div>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('bmn-codes.index') }}" class="filters">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama barang...">
            <button type="submit" class="btn btn-outline btn-sm">Cari</button>
        </form>

        @if ($codes->count() === 0)
            <div class="empty-state">Belum ada kode BMN. Mulai dengan Import CSV atau Tambah Kode manual.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Jumlah Aset</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($codes as $code)
                        <tr>
                            <td><a href="{{ route('bmn-codes.show', $code->id) }}"><span class="code-chip">{{ $code->kode }}</span></a></td>
                            <td><a href="{{ route('bmn-codes.show', $code->id) }}" style="color: var(--ink); text-decoration: none; font-weight: 500;">{{ $code->nama }}</a></td>
                            <td>
                                @if ($code->assets_count > 0)
                                    <a href="{{ route('bmn-codes.show', $code->id) }}" class="badge badge-baik">{{ $code->assets_count }} aset</a>
                                @else
                                    <span class="badge badge-hilang">0 aset</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('bmn-codes.show', $code->id) }}" class="icon-btn" title="Lihat Detail" aria-label="Lihat Detail">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="{{ route('bmn-codes.edit', $code->id) }}" class="icon-btn" title="Edit" aria-label="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('bmn-codes.destroy', $code->id) }}" style="display:inline" data-confirm="Yakin hapus kode {{ $code->kode }}?">
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
                {{ $codes->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
