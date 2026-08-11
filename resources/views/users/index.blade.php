<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Administrasi</div>
            <h1>Manajemen User</h1>
            <p>Kelola akun admin dan pengaju per prodi/unit.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn">+ Tambah User</a>
    </div>

    @if (session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('users.index') }}" class="filters">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email...">
            <select name="role" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                <option value="admin" @selected(request('role') == 'admin')>Admin</option>
                <option value="kepala_unit" @selected(request('role') == 'kepala_unit')>Kepala Unit</option>
                <option value="staff" @selected(request('role') == 'staff')>Staff</option>
                <option value="pimpinan" @selected(request('role') == 'pimpinan')>Pimpinan</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Cari</button>
        </form>

        @if ($users->count() === 0)
            <div class="empty-state">Belum ada user yang cocok.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="role-tag role-tag--{{ $user->role }}" style="color: var(--ink); background: var(--neutral-soft);">{{ $user->role }}</span></td>
                            <td>{{ $user->unit?->name ?? '-' }}</td>
                            <td>
                                @if ($user->is_active)
                                    <span class="badge badge-baik">Aktif</span>
                                @else
                                    <span class="badge badge-hilang">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('users.edit', $user->id) }}" class="icon-btn" title="Edit" aria-label="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.toggle-active', $user->id) }}" style="display:inline">
                                            @csrf
                                            <button type="submit" class="action-pill {{ $user->is_active ? 'action-pill--danger' : 'action-pill--ok' }}">
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
