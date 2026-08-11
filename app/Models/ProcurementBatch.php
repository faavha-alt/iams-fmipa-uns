<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcurementBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama', 'vendor_id', 'tanggal_mulai', 'tanggal_selesai', 'nomor_dokumen', 'status', 'catatan', 'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function realizations(): HasMany
    {
        return $this->hasMany(PurchaseRealization::class, 'procurement_batch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
