<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi lewat mass-assignment.
     *
     * Kolom sensitif (role, is_active, is_approved, unit_id) SENGAJA TIDAK ada di sini agar
     * tidak bisa ditimpa lewat request mass-assignment (pintu privilege escalation). Nilainya
     * hanya di-set eksplisit di logika berotorisasi (UserController / GoogleAuthController).
     *
     * @return array<string, string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'nip', 'phone', 'google_id',
    ];

protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'is_active' => 'boolean',
    'is_approved' => 'boolean',
];

public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(Unit::class);
}

/**
 * Hanya role 'admin' yang boleh mengelola data (membuat/mengedit/menghapus).
 * Dipakai di view untuk menyembunyikan tombol aksi bagi non-admin.
 */
public function isAdmin(): bool
{
    return $this->role === 'admin';
}

public function responsibleAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Asset::class, 'responsible_user_id');
}

public function assignedAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Asset::class, 'current_user_id');
}
}
