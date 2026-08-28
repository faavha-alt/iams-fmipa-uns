<?php

namespace App\Concerns;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;

/**
 * Batasan akses data berdasarkan peran (keputusan otorisasi bisnis):
 *   - admin & pimpinan  : boleh melihat SEMUA data.
 *   - kepala_unit/staff : hanya melihat data unit miliknya sendiri.
 *
 * Hanya berlaku untuk akses LIHAT (read-only). Operasi tulis tetap dijaga ketat
 * lewat middleware 'admin' di rute — role non-admin tidak pernah sampai ke sini.
 */
trait RestrictsByRole
{
    /** Apakah user boleh melihat seluruh unit (admin / pimpinan). */
    protected function canSeeAllUnits(): bool
    {
        $user = auth()->user();

        return $user && in_array($user->role, ['admin', 'pimpinan'], true);
    }

    /** Apakah user boleh melihat unit tertentu. */
    protected function canAccessUnit(Unit $unit): bool
    {
        return $this->canSeeAllUnits() || auth()->user()?->unit_id === $unit->id;
    }

    /**
     * Batasi query agar non-admin hanya melihat baris unit miliknya.
     * Admin/pimpinan tidak dibatasi (melihat semua).
     */
    protected function restrictByRole(Builder $query, string $unitColumn = 'unit_id'): Builder
    {
        if ($this->canSeeAllUnits()) {
            return $query;
        }

        return $query->where($unitColumn, auth()->user()->unit_id);
    }

    /**
     * ID unit yang boleh dilihat user. null = semua unit (admin/pimpinan).
     */
    protected function visibleUnitIds(): ?array
    {
        if ($this->canSeeAllUnits()) {
            return null;
        }

        return [auth()->user()->unit_id];
    }
}
