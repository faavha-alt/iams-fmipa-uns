<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id', 'code', 'name', 'type', 'head_user_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Unit::class, 'parent_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'unit_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Generate kode unit otomatis berdasarkan tipe, mengikuti urutan kode sebelumnya.
     * Contoh: PRODI-001, PRODI-002, LAB-001, dst.
     */
    public static function generateCode(string $type): string
    {
        $prefixes = [
            'fakultas' => 'FAK',
            'departemen' => 'DEPT',
            'program_studi' => 'PRODI',
            'laboratorium' => 'LAB',
            'unit_kerja' => 'UNIT',
        ];

        $prefix = $prefixes[$type] ?? 'UNIT';
        $sequence = static::where('type', $type)->count() + 1;
        $code = sprintf('%s-%03d', $prefix, $sequence);

        // Jaga-jaga kalau ada bentrok (misal setelah unit lama dihapus), naikkan urutan sampai aman.
        while (static::where('code', $code)->exists()) {
            $sequence++;
            $code = sprintf('%s-%03d', $prefix, $sequence);
        }

        return $code;
    }
}
