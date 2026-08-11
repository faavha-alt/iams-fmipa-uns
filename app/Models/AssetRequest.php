<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id', 'requested_by', 'asset_category_id', 'fiscal_year',
        'item_name', 'quantity', 'estimated_cost', 'purchase_link', 'supporting_image', 'reason',
        'status', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function fulfilledAsset(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Asset::class);
    }
}
