<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BmnCodeReference extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bmn_code_references';

    protected $fillable = [
        'kode', 'nama', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Label gabungan buat ditampilkan di dropdown/datalist: "3.05.02.01.001 — Kursi Besi/Metal"
     */
    public function getFullLabelAttribute(): string
    {
        return "{$this->kode} — {$this->nama}";
    }
}
