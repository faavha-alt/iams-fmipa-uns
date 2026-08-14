<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Inventaris</div>
            <h1>Kroscek Sebelum Import</h1>
            <p>Belum ada yang tersimpan ke database — cek dulu hasil baca file di bawah, lalu konfirmasi.</p>
        </div>
        <a href="{{ route('assets.import') }}" class="btn btn-outline">← Upload Ulang</a>
    </div>

    <div class="stat-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <div class="stat-card__value">{{ count($rows) }}</div>
            <div class="stat-card__label">Total Baris Dibaca</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <div class="stat-card__value">{{ $okCount }}</div>
            <div class="stat-card__label">Siap Diimport</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon stat-card__icon--warn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v5" stroke-linecap="round"/><circle cx="12" cy="16.5" r="0.5" fill="currentColor"/><path d="M10.3 3.9 2.5 17.5A1.8 1.8 0 0 0 4 20.2h16a1.8 1.8 0 0 0 1.5-2.7L13.7 3.9a1.8 1.8 0 0 0-3.4 0Z"/></svg>
            </div>
            <div class="stat-card__value">{{ $errorCount }}</div>
            <div class="stat-card__label">Error (dilewati)</div>
        </div>
    </div>

    <form method="POST" action="{{ route('assets.import.confirm') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        @php ($vendorsNeedingReview = collect($vendors)->filter(fn ($v) => $v['status'] !== 'matched'))
        @if ($vendorsNeedingReview->count() > 0)
            <div class="card">
                <div class="card__header">
                    <h2 class="card__title">Vendor yang Perlu Ditinjau</h2>
                </div>
                <p style="font-size: 0.82rem; color: var(--muted); margin-bottom: 16px;">
                    Nama vendor di file ini tidak cocok persis dengan Master Vendor. Pilih vendor yang sudah ada kalau memang sama (supaya tidak dobel), atau biarkan dibuat baru.
                </p>

                @foreach ($vendors as $index => $vendor)
                    @continue($vendor['status'] === 'matched')
                    <div style="padding: 14px 0; border-top: 1px solid var(--border);">
                        <p style="font-weight: 600; font-size: 0.88rem; margin: 0 0 8px;">"{{ $vendor['name'] }}"</p>

                        @if ($vendor['status'] === 'ambiguous')
                            @foreach ($vendor['candidates'] as $ci => $candidate)
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; margin-bottom: 6px; cursor: pointer;">
                                    <input type="radio" name="vendor_decisions[{{ $index }}]" value="existing:{{ $candidate['id'] }}" @checked($ci === 0)>
                                    Pakai vendor yang sudah ada: <strong>{{ $candidate['name'] }}</strong>
                                    <span class="badge badge-rusak_ringan">{{ $candidate['percent'] }}% mirip</span>
                                </label>
                            @endforeach
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; cursor: pointer;">
                                <input type="radio" name="vendor_decisions[{{ $index }}]" value="new">
                                Buat vendor baru dengan nama ini
                            </label>
                        @else
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--muted);">
                                <input type="hidden" name="vendor_decisions[{{ $index }}]" value="new">
                                Tidak ada vendor mirip di Master Vendor — akan dibuatkan vendor baru otomatis.
                            </label>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="card">
            <div class="card__header">
                <h2 class="card__title">Detail per Baris</h2>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Aset</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th>Lokasi</th>
                            <th>Vendor</th>
                            <th>Nilai</th>
                            <th>Kondisi</th>
                            <th>Hasil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>{{ $row['row_number'] }}</td>
                                <td>{{ $row['display']['nama_aset'] ?? '-' }}</td>
                                <td>{{ $row['display']['kategori'] ?? '-' }}</td>
                                <td>{{ $row['display']['unit'] ?? '-' }}</td>
                                <td>{{ $row['display']['lokasi'] ?? '-' }}</td>
                                <td>{{ $row['display']['vendor_nama'] ?? '-' }}</td>
                                <td>{{ $row['display']['nilai_perolehan'] ?? '-' }}</td>
                                <td>{{ $row['display']['kondisi'] ?? '-' }}</td>
                                <td>
                                    @if ($row['status'] === 'ok')
                                        <span class="badge badge-baik">Siap</span>
                                    @else
                                        <span class="badge badge-rusak_berat" title="{{ implode('; ', $row['errors']) }}">Error</span>
                                        <div style="font-size: 0.72rem; color: var(--danger); margin-top: 4px;">{{ implode('; ', $row['errors']) }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
            <p style="margin: 0; font-size: 0.85rem; color: var(--muted);">
                @if ($okCount > 0)
                    {{ $okCount }} baris akan disimpan ke database. {{ $errorCount }} baris error akan dilewati.
                @else
                    Tidak ada baris yang siap diimport — perbaiki file lalu upload ulang.
                @endif
            </p>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('assets.import') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn" @disabled($okCount === 0)>Konfirmasi &amp; Simpan ke Database</button>
            </div>
        </div>
    </form>
</x-layouts.app>
