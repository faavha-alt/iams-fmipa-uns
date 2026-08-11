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
                                <a href="{{ route('bmn-codes.edit', $code->id) }}">Edit</a>
                                &nbsp;·&nbsp;
                                <form method="POST" action="{{ route('bmn-codes.destroy', $code->id) }}" style="display:inline" data-confirm="Yakin hapus kode {{ $code->kode }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-danger">Hapus</button>
                                </form>
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
