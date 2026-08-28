<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Pengadaan @if ($batch->vendor) — {{ $batch->vendor->name }} @endif</div>
            <h1>{{ $batch->nama }}</h1>
            <p>
                {{ $batch->tanggal_mulai?->format('d M Y') ?? '-' }}
                @if ($batch->tanggal_selesai) &ndash; {{ $batch->tanggal_selesai->format('d M Y') }} @endif
                @if ($batch->nomor_dokumen) &nbsp;·&nbsp; No. {{ $batch->nomor_dokumen }} @endif
            </p>
        </div>
        @if (auth()->user()->isAdmin())
        <div style="display:flex; gap:8px;">
            <a href="{{ route('realizations.create', ['batch' => $batch->id]) }}" class="btn">+ Tambah Barang</a>
            <a href="{{ route('procurement-batches.edit', $batch->id) }}" class="btn btn-outline">✏ Edit</a>
        </div>
        @endif
    </div>

    @if ($batch->catatan)
        <div class="card" style="background: var(--sky-pale);">{{ $batch->catatan }}</div>
    @endif

    @if (auth()->user()->isAdmin() && $orphans->count() > 0)
        <div class="card" style="background: var(--gold-pale);">
            <div class="card__header"><h2 class="card__title">Tambahkan Realisasi yang Sudah Ada</h2></div>
            <p style="font-size: 0.85rem; color: var(--muted); margin-bottom: 14px;">
                Realisasi di bawah ini belum masuk pengadaan manapun (termasuk yang statusnya sudah final) — centang yang mau ditarik ke pengadaan ini.
            </p>
            <form method="POST" action="{{ route('procurement-batches.attach', $batch->id) }}">
                @csrf
                <div style="border: 1px solid var(--border); border-radius: 8px; padding: 4px; max-height: 280px; overflow-y: auto; margin-bottom: 14px;">
                    <table style="margin: 0;">
                        <thead>
                            <tr>
                                <th style="width: 36px;">
                                    <input type="checkbox" onclick="document.querySelectorAll('.orphan-check').forEach(c => c.checked = this.checked)">
                                </th>
                                <th>Barang</th>
                                <th>Vendor</th>
                                <th>Unit</th>
                                <th>Biaya</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orphans as $o)
                                <tr>
                                    <td><input type="checkbox" class="orphan-check" name="realization_ids[]" value="{{ $o->id }}"></td>
                                    <td>{{ $o->item_name }}</td>
                                    <td>{{ $o->vendor?->name ?? '-' }}</td>
                                    <td>{{ $o->unit->name }}</td>
                                    <td>Rp {{ number_format($o->cost, 0, ',', '.') }}</td>
                                    <td>{{ $o->purchase_date->format('d M Y') }}</td>
                                    <td>
                                        @if ($o->status === 'sudah_final')
                                            <span class="badge badge-baik">Final</span>
                                        @else
                                            <span class="badge badge-diajukan">Belum Final</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-sm">Tambahkan yang Dicentang ke Pengadaan Ini</button>
            </form>
        </div>
    @endif

    @if ($items->count() === 0)
        <div class="card">
            <div class="empty-state">Belum ada barang di pengadaan ini. Klik "+ Tambah Barang" di atas.</div>
        </div>
    @else
        <div class="card">
            <div class="card__header">
                <h2 class="card__title">Daftar Barang</h2>
                <span style="font-size: 0.85rem; color: var(--muted); font-weight: 600;">
                    {{ $items->count() }} item · Rp {{ number_format($items->sum('cost'), 0, ',', '.') }}
                </span>
            </div>
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Unit Pemesan</th>
                        <th class="num">Jumlah</th>
                        <th class="num">Biaya</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->category?->name ?? '-' }}</td>
                            <td>{{ $item->unit->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rp {{ number_format($item->cost, 0, ',', '.') }}</td>
                            <td>{{ $item->purchase_date->format('d M Y') }}</td>
                            <td>
                                @if ($item->status === 'sudah_final')
                                    <span class="badge badge-baik">Sudah Final</span>
                                @else
                                    <span class="badge badge-diajukan">Belum Final</span>
                                @endif
                            </td>
                            <td>
                                @if (auth()->user()->isAdmin())
                                    @if ($item->status === 'belum_final')
                                        <div class="row-actions">
                                            <a href="{{ route('realizations.finalize-form', $item->id) }}" class="action-pill action-pill--ok" title="Finalisasi jadi Aset">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                                Finalisasi
                                            </a>
                                            <a href="{{ route('realizations.edit', $item->id) }}" class="icon-btn" title="Edit" aria-label="Edit">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                            </a>
                                        </div>
                                    @else
                                        <span style="color: var(--muted); font-size: 0.8rem;">{{ $item->assets_count }} aset dibuat</span>
                                    @endif
                                @elseif ($item->status === 'sudah_final')
                                    <span style="color: var(--muted); font-size: 0.8rem;">{{ $item->assets_count }} aset dibuat</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <div class="card" style="background: var(--navy); color: white;">
            <strong>Total Pengadaan Ini: Rp {{ number_format($items->sum('cost'), 0, ',', '.') }}</strong>
        </div>
    @endif
</x-layouts.app>
