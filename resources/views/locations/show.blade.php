<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Lokasi @if ($location->unit) — {{ $location->unit->name }} @endif</div>
            <h1>{{ $location->name }}</h1>
            <p>
                {{ $location->building ?? '-' }}
                @if ($location->floor) &nbsp;·&nbsp; Lantai {{ $location->floor }} @endif
                @if ($location->room_code) &nbsp;·&nbsp; Kode Ruang {{ $location->room_code }} @endif
            </p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('locations.dbr', $location->id) }}" target="_blank" class="btn btn-outline">🖨 Cetak DBR</a>
            <a href="{{ route('locations.edit', $location->id) }}" class="btn btn-outline">✏ Edit Lokasi</a>
            <a href="{{ route('locations.index') }}" class="btn btn-outline">← Kembali</a>
        </div>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif
    @if (session('error'))
        <div class="alert-success" style="background: var(--danger-soft); color: var(--danger); border-left-color: var(--danger);">{{ session('error') }}</div>
    @endif

    @if ($location->notes)
        <div class="card" style="background: var(--sky-pale);">{{ $location->notes }}</div>
    @endif

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Aset di Ruangan Ini</h2>
            <span style="font-size: 0.85rem; color: var(--muted); font-weight: 600;">{{ $assets->count() }} aset</span>
        </div>

        @if ($assets->count() === 0)
            <div class="empty-state">Belum ada aset yang ditempatkan di lokasi ini.</div>
        @else
            <p style="font-size: 0.8rem; color: var(--muted); margin: -6px 0 14px;">
                Ketemu input dobel? Hapus langsung dari sini supaya datanya tidak duplikat.
            </p>
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Merk</th>
                        <th>Tipe/Seri</th>
                        <th>Kategori</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $asset)
                        <tr>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->brand ?? '-' }}</td>
                            <td>{{ collect([$asset->model, $asset->serial_number])->filter()->implode(' / ') ?: '-' }}</td>
                            <td>{{ $asset->category?->name ?? '-' }}</td>
                            <td><span class="badge badge-{{ $asset->condition }}">{{ str_replace('_', ' ', $asset->condition) }}</span></td>
                            <td>{{ str_replace('_', ' ', $asset->status) }}</td>
                            <td>
                                <a href="{{ route('assets.edit', $asset->id) }}">Edit</a>
                                &nbsp;·&nbsp;
                                <form method="POST" action="{{ route('assets.destroy', $asset->id) }}"
                                      style="display:inline" data-confirm="Yakin hapus aset {{ $asset->name }}? Pakai ini kalau ini input dobel, bukan aset yang benar-benar ada.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</x-layouts.app>
