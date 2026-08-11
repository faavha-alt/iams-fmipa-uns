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
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($codes as $code)
                        <tr>
                            <td><span class="code-chip">{{ $code->kode }}</span></td>
                            <td>{{ $code->nama }}</td>
                            <td>
                                <div class="row-actions">
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
