<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HandoverReport extends Model
{
    use HasFactory;

    protected $table = 'handover_reports';

    protected $fillable = [
        'nomor_bast', 'unit_id',
        'tanggal_serah_terima', 'nama_menyerahkan', 'jabatan_menyerahkan',
        'nama_menerima', 'jabatan_menerima', 'catatan',
        'dokumen_scan', 'status', 'created_by',
    ];

    protected $casts = [
        'tanggal_serah_terima' => 'date',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'handover_report_items');
    }

    /**
     * Nomor BAST otomatis, contoh: BAST/FMIPA/2026/00001
     */
    public static function generateNomor(): string
    {
        $year = now()->year;
        $sequence = static::whereYear('created_at', $year)->count() + 1;
        $nomor = sprintf('BAST/FMIPA/%d/%05d', $year, $sequence);

        while (static::where('nomor_bast', $nomor)->exists()) {
            $sequence++;
            $nomor = sprintf('BAST/FMIPA/%d/%05d', $year, $sequence);
        }

        return $nomor;
    }
}
