<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id', 'code', 'name', 'unit_satuan', 'specification', 'useful_life_years',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AssetCategory::class, 'parent_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
