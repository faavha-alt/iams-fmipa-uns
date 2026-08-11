<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Pengajuan</div>
            <h1>{{ $isAdmin ? 'Semua Pengajuan Aset' : 'Pengajuan Unit Saya' }}</h1>
            <p>{{ $isAdmin ? 'Tinjau dan putuskan permintaan dari setiap unit.' : 'Riwayat pengajuan yang pernah Anda kirim.' }}</p>
        </div>
        @if (! $isAdmin)
            <a href="{{ route('requests.create') }}" class="btn">+ Ajukan Aset</a>
        @endif
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('requests.index') }}" class="filters">
            <select name="status" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="diajukan" @selected(request('status') == 'diajukan')>Diajukan</option>
                <option value="disetujui" @selected(request('status') == 'disetujui')>Disetujui</option>
                <option value="ditolak" @selected(request('status') == 'ditolak')>Ditolak</option>
            </select>
        </form>

        @if ($requests->count() === 0)
            <div class="empty-state">Belum ada pengajuan.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        @if ($isAdmin)
                            <th>Unit</th>
                            <th>Pengaju</th>
                        @endif
                        <th>Tahun</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                        @if ($isAdmin)
                            <th></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                        <tr>
                            <td>
                                {{ $request->item_name }}
                                @if ($request->category)
                                    <br><span style="color: var(--muted); font-size: 0.78rem;">{{ $request->category->name }}</span>
                                @endif
                            </td>
                            @if ($isAdmin)
                                <td>{{ $request->unit->name }}</td>
                                <td>{{ $request->requestedBy->name }}</td>
                            @endif
                            <td>{{ $request->fiscal_year ?? '-' }}</td>
                            <td>{{ $request->quantity }}</td>
                            <td><span class="badge badge-{{ $request->status }}">{{ $request->status }}</span></td>
                            <td>{{ $request->created_at->format('d M Y') }}</td>
                            @if ($isAdmin)
                                <td>
                                    @if ($request->status !== 'diajukan')
                                        <span style="color: var(--muted); font-size: 0.8rem;">Selesai</span>
                                    @endif
                                </td>
                            @endif
                        </tr>

                        @if ($isAdmin && $request->status === 'diajukan')
                            @php
                                $reqYear = $request->fiscal_year ?? now()->year;
                                $sisa = $paguContext[$request->unit_id.'-'.$reqYear] ?? 0;
                            @endphp
                            <tr>
                                <td colspan="7" style="border-bottom: 1px solid var(--border); padding: 0;">
                                    <details class="review-panel">
                                        <summary>Tinjau pengajuan ini</summary>
                                        <div class="review-panel__body">
                                            <div style="display:flex; gap:20px; flex-wrap:wrap; margin-bottom: 14px;">
                                                @if ($request->supporting_image)
                                                    <a href="{{ asset('storage/'.$request->supporting_image) }}" target="_blank">
                                                        <img src="{{ asset('storage/'.$request->supporting_image) }}" alt="Bukti pendukung" style="width: 140px; height: 140px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                                                    </a>
                                                @endif
                                                <div style="flex: 1; min-width: 240px;">
                                                    <p><strong>Alasan:</strong> {{ $request->reason }}</p>
                                                    @if ($request->purchase_link)
                                                        <p><strong>Link referensi:</strong> <a href="{{ $request->purchase_link }}" target="_blank" rel="noopener">{{ $request->purchase_link }}</a></p>
                                                    @endif
                                                    @if ($request->estimated_cost)
                                                        <p><strong>Estimasi biaya:</strong> Rp {{ number_format($request->estimated_cost, 0, ',', '.') }}</p>
                                                    @endif
                                                    <p>
                                                        <strong>Sisa pagu {{ $request->unit->name }} ({{ $reqYear }}):</strong>
                                                        <span style="color: {{ $request->estimated_cost && $request->estimated_cost > $sisa ? 'var(--danger)' : 'var(--ink)' }}; font-weight: 600;">
                                                            Rp {{ number_format($sisa, 0, ',', '.') }}
                                                        </span>
                                                        @if ($request->estimated_cost && $request->estimated_cost > $sisa)
                                                            <span class="badge badge-rusak_berat">melebihi sisa pagu</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>

                                            <form method="POST" action="{{ route('requests.decide', $request->id) }}">
                                                @csrf
                                                <div class="form-group">
                                                    <label>Catatan review (opsional)</label>
                                                    <textarea name="review_notes" rows="2"></textarea>
                                                </div>
                                                <div style="display:flex; gap:8px;">
                                                    <button type="submit" name="decision" value="setuju" class="btn btn-sm">Setujui</button>
                                                    <button type="submit" name="decision" value="tolak" class="btn btn-sm btn-danger">Tolak</button>
                                                </div>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            <div class="pagination">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
