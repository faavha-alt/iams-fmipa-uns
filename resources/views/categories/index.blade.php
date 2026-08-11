<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Master Data</div>
            <h1>Kategori Aset</h1>
            <p>Klasifikasi aset beserta satuan dan spesifikasi standar.</p>
        </div>
        <a href="{{ route('categories.create') }}" class="btn">+ Tambah Kategori</a>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif
    @if (session('error'))
        <div class="alert-success" style="background: var(--danger-soft); color: var(--danger); border-left-color: var(--danger);">{{ session('error') }}</div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('categories.index') }}" class="filters">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kategori...">
            <button type="submit" class="btn btn-outline btn-sm">Cari</button>
        </form>

        @if ($categories->count() === 0)
            <div class="empty-state">Belum ada kategori yang cocok.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Induk</th>
                        <th>Satuan</th>
                        <th>Masa Manfaat</th>
                        <th>Aset</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td><span class="code-chip">{{ $category->code }}</span></td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->parent?->name ?? '-' }}</td>
                            <td>{{ $category->unit_satuan ?? '-' }}</td>
                            <td>{{ $category->useful_life_years ? $category->useful_life_years.' tahun' : '-' }}</td>
                            <td>{{ $category->assets_count }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('categories.edit', $category->id) }}" class="icon-btn" title="Edit" aria-label="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('categories.destroy', $category->id) }}" style="display:inline" data-confirm="Yakin hapus kategori {{ $category->name }}?">
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
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
