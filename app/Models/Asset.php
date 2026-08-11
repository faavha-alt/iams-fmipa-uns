<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_request_id', 'purchase_realization_id',
        'asset_code', 'qr_code', 'name', 'brand', 'model', 'serial_number',
        'asset_category_id', 'unit_id', 'location_id',
        'responsible_user_id', 'current_user_id',
        'acquisition_date', 'acquisition_source', 'vendor_id',
        'acquisition_value', 'book_value',
        'condition', 'status', 'notes',
        // Identitas & status integrasi SIMAK BMN
        'simak_kode_barang', 'simak_nup', 'simak_kode_lokasi', 'simak_tahun_perolehan',
        'simak_sync_status', 'simak_last_synced_at', 'simak_sync_notes',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_value' => 'decimal:2',
        'book_value' => 'decimal:2',
        'simak_last_synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Asset $asset) {
            if (empty($asset->asset_code)) {
                $asset->asset_code = static::generateAssetCode($asset->unit_id);
            }
            if (empty($asset->qr_code)) {
                $asset->qr_code = (string) Str::uuid();
            }
        });
    }

    /**
     * Buat kode aset unik, contoh: FMIPA-2026-000001
     */
    public static function generateAssetCode(?int $unitId): string
    {
        $unit = $unitId ? Unit::find($unitId) : null;
        $prefix = $unit?->code ?? 'FMIPA';
        $year = now()->year;
        $sequence = static::withTrashed()
            ->where('asset_code', 'like', "{$prefix}-{$year}-%")
            ->count() + 1;

        return sprintf('%s-%d-%06d', $prefix, $year, $sequence);
    }

    // Relasi

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function currentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function assetRequest(): BelongsTo
    {
        return $this->belongsTo(AssetRequest::class);
    }

    public function purchaseRealization(): BelongsTo
    {
        return $this->belongsTo(PurchaseRealization::class);
    }

    public function handoverReports(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(HandoverReport::class, 'handover_report_items');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AssetHistory::class)->orderByDesc('event_date');
    }

    public function simakSyncLogs(): HasMany
    {
        return $this->hasMany(SimakBmnSyncLog::class)->orderByDesc('created_at');
    }

    /**
     * Gabungan Kode Barang-Tahun Perolehan-NUP, format umum yang dipakai SIMAK BMN
     * untuk mengidentifikasi satu unit barang secara unik. Contoh: 3.05.02.01.001-2026-0007
     */
    public function getSimakFullCodeAttribute(): ?string
    {
        if (! $this->simak_kode_barang || ! $this->simak_tahun_perolehan || ! $this->simak_nup) {
            return null;
        }

        return sprintf(
            '%s-%d-%04d',
            $this->simak_kode_barang,
            $this->simak_tahun_perolehan,
            $this->simak_nup
        );
    }

    public function isSyncedToSimak(): bool
    {
        return $this->simak_sync_status === 'tersinkron';
    }
}
