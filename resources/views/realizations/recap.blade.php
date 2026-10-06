<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Pengadaan</div>
            <h1>Rekap Barang {{ $year }}</h1>
            <p>Seluruh nama barang yang dibelanjakan tahun {{ $year }} — semua status digabung — beserta total unit &amp; nilainya.</p>
        </div>
        <a href="{{ route('realizations.recap.export', request()->query()) }}" class="btn">Export Excel</a>
    </div>

    <div style="display:flex; gap:6px; margin-bottom: 20px;">
        <a href="{{ route('procurement-batches.index') }}" class="btn btn-outline btn-sm">Daftar Pengadaan</a>
        <a href="{{ route('realizations.index') }}" class="btn btn-outline btn-sm">Semua Barang (lintas vendor)</a>
        <a href="{{ route('realizations.recap') }}" class="btn btn-sm" style="background: var(--navy); border-color: var(--navy);">Rekap Barang</a>
    </div>

    <div class="stat-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <div class="stat-card__value">{{ number_format($items->count(), 0, ',', '.') }}</div>
            <div class="stat-card__label">Jenis Barang</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value">{{ number_format($grandQuantity, 0, ',', '.') }}</div>
            <div class="stat-card__label">Total Unit</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value">Rp {{ number_format($grandCost, 0, ',', '.') }}</div>
            <div class="stat-card__label">Total Nilai</div>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('realizations.recap') }}" class="filters">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang...">
            <select name="year">
                @foreach (range(now()->year + 1, now()->year - 3) as $y)
                    <option value="{{ $y }}" @selected($y == $year)>Tahun {{ $y }}</option>
                @endforeach
            </select>
            <select name="unit_id">
                <option value="">Semua Unit</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>{{ $unit->name }}</option>
                @endforeach
            </select>
            <select name="status">
                <option value="">Semua Status</option>
                <option value="belum_final" @selected(request('status') === 'belum_final')>Belum Final</option>
                <option value="sudah_final" @selected(request('status') === 'sudah_final')>Sudah Final</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Cari</button>
            @if (request()->filled('search') || request()->filled('unit_id') || request()->filled('status') || request()->filled('year'))
                <a href="{{ route('realizations.recap') }}" class="btn btn-sm">Reset</a>
            @endif
            <a href="{{ route('realizations.recap.export', request()->query()) }}" class="btn btn-sm" style="margin-left:auto;">⬇ Export Excel</a>
        </form>
    </div>

    @if ($items->isEmpty())
        <div class="card">
            <div class="empty-state">Belum ada barang pengadaan tahun {{ $year }} yang cocok dengan filter ini.</div>
        </div>
    @else
        <div class="card" style="padding: 0;">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width:52px;">No</th>
                            <th>Nama Barang</th>
                            <th class="num">Jumlah Unit</th>
                            <th class="num">Jumlah Transaksi</th>
                            <th class="num">Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td class="num">{{ number_format((int) $item->total_quantity, 0, ',', '.') }}</td>
                                <td class="num">{{ number_format((int) $item->total_records, 0, ',', '.') }}</td>
                                <td class="num">Rp {{ number_format((float) $item->total_cost, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" style="text-align:right;">Total</th>
                            <th class="num">{{ number_format($grandQuantity, 0, ',', '.') }}</th>
                            <th class="num">{{ number_format($items->sum('total_records'), 0, ',', '.') }}</th>
                            <th class="num">Rp {{ number_format($grandCost, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</x-layouts.app>
