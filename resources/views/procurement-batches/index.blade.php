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
            <table>
                <thead>
                    <tr>
                        <th>Nama Periode</th>
                        <th>Tanggal</th>
                        <th>Jumlah Realisasi</th>
                        <th>Total Nilai</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($batches as $batch)
                        <tr>
                            <td>{{ $batch->nama }}</td>
                            <td>
                                {{ $batch->tanggal_mulai?->format('d M Y') ?? '-' }}
                                @if ($batch->tanggal_selesai)
                                    &ndash; {{ $batch->tanggal_selesai->format('d M Y') }}
                                @endif
                            </td>
                            <td>{{ $batch->realizations_count }}</td>
                            <td>Rp {{ number_format($batch->realizations_sum_cost ?? 0, 0, ',', '.') }}</td>
                            <td>
                                @if ($batch->status === 'selesai')
                                    <span class="badge badge-baik">Selesai</span>
                                @else
                                    <span class="badge badge-diajukan">Berjalan</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('procurement-batches.show', $batch->id) }}">Lihat</a>
                                &nbsp;·&nbsp;
                                <a href="{{ route('procurement-batches.edit', $batch->id) }}">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
