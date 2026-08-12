<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $fillable = [
    'name', 'email', 'password',
    'unit_id', 'role', 'nip', 'phone', 'is_active',
    'google_id', 'is_approved',
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

public function responsibleAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Asset::class, 'responsible_user_id');
}

public function assignedAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Asset::class, 'current_user_id');
}
}
