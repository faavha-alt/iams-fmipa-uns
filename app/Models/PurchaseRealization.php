<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRealization extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id', 'procurement_batch_id', 'asset_category_id', 'vendor_id',
        'item_name', 'quantity', 'cost', 'purchase_date', 'notes',
        'status', 'recorded_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function procurementBatch(): BelongsTo
    {
        return $this->belongsTo(ProcurementBatch::class, 'procurement_batch_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function isFinalized(): bool
    {
        return $this->status === 'sudah_final';
    }
}
