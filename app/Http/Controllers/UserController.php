<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('unit')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->input('role')))
            ->when($request->input('status') === 'pending', fn ($q) => $q->where('is_approved', false))
            ->orderByRaw('is_approved asc')
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->query());

        return view('users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('users.form', [
            'user' => null,
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,kepala_unit,staff,pimpinan',
            'unit_id' => 'nullable|exists:units,id',
            'nip' => 'nullable|string|max:50|unique:users,nip',
            'phone' => 'nullable|string|max:30',
        ]);

        $user = User::create(collect($data)->except(['role', 'unit_id'])->toArray());
        // role/unit_id tidak ada di $fillable (anti mass-assignment) — set eksplisit di sini.
        $user->role = $data['role'];
        $user->unit_id = $data['unit_id'] ?? null;
        $user->is_active = true;
        $user->is_approved = true;
        $user->save();

        return redirect()->route('users.index')->with('message', 'User baru berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.form', [
            'user' => $user,
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,kepala_unit,staff,pimpinan',
            'unit_id' => 'nullable|exists:units,id',
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nip')->ignore($user->id)],
            'phone' => 'nullable|string|max:30',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        // role/unit_id/is_approved tidak ada di $fillable — set eksplisit. Menyimpan role & unit
        // lewat form ini adalah tindakan approve untuk user yang masuk lewat Google.
        $user->update(collect($data)->except(['role', 'unit_id'])->toArray());
        $user->role = $data['role'];
        $user->unit_id = $data['unit_id'] ?? null;
        $user->is_approved = true;
        $user->save();

        return redirect()->route('users.index')->with('message', 'Data user berhasil diperbarui.');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403, 'Tidak bisa menonaktifkan akun sendiri.');

        // is_active tidak ada di $fillable — set eksplisit.
        $user->is_active = ! $user->is_active;
        $user->save();

        $message = $user->is_active ? 'User diaktifkan kembali.' : 'User dinonaktifkan.';

        return redirect()->route('users.index')->with('message', $message);
    }
}
