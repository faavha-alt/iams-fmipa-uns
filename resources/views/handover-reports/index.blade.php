<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Serah Terima</div>
            <h1>Berita Acara Serah Terima (BAST)</h1>
            <p>Dokumen serah terima barang pengadaan ke unit.</p>
        </div>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    @if ($unitsWithPending->count() > 0)
        <div class="card" style="background: var(--gold-pale);">
            <div class="card__header"><h2 class="card__title">Unit dengan Barang Menunggu BAST</h2></div>
            <table>
                <thead>
                    <tr>
                        <th>Unit</th>
                        <th>Jumlah Barang Menunggu</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($unitsWithPending as $unit)
                        <tr>
                            <td>{{ $unit->name }}</td>
                            <td>{{ $unit->pending_count }} barang</td>
                            <td><a href="{{ route('handover-reports.create', $unit->id) }}" class="btn btn-sm">Buat BAST</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('handover-reports.index') }}" class="filters">
            <select name="unit_id" onchange="this.form.submit()">
                <option value="">Semua Unit</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>{{ $unit->name }}</option>
                @endforeach
            </select>
        </form>

        @if ($reports->count() === 0)
            <div class="empty-state">Belum ada BAST dibuat.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Nomor BAST</th>
                        <th>Unit</th>
                        <th>Jumlah Barang</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                        <tr>
                            <td><span class="code-chip">{{ $report->nomor_bast }}</span></td>
                            <td>{{ $report->unit->name }}</td>
                            <td>{{ $report->assets->count() }} barang</td>
                            <td>{{ $report->tanggal_serah_terima->format('d M Y') }}</td>
                            <td>
                                @if ($report->status === 'final')
                                    <span class="badge badge-baik">Final</span>
                                @else
                                    <span class="badge badge-diajukan">Draft</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('handover-reports.show', $report->id) }}" class="icon-btn" title="Lihat" aria-label="Lihat">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
