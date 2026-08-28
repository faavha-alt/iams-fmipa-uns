<?php

namespace App\Concerns;

/**
 * Retry otomatis saat query kena konflik constraint UNIQUE (kode aset, nomor BAST, dst).
 *
 * Kode otomatis (Asset::generateAssetCode, HandoverReport::generateNomor) dihitung lewat
 * count()+1 yang tidak atomik terhadap kolom ber-UNIQUE. Dua proses bersamaan bisa
 * menghasilkan nilai sama; salah satunya gagal dengan QueryException duplicate.
 * Pembungkus ini menangkap pelanggaran UNIQUE lalu menjalankan ulang callback (yang
 * men-generate kode baru dari state terbaru) sampai berhasil atau batas percobaan.
 *
 * Catatan: karena pemanggil biasanya membungkus pekerjaannya dalam DB::transaction,
 * state sudah otomatis di-rollback sebelum retry — jadi tidak ada data parsial.
 */
trait RetriesUniqueConstraint
{
    protected function retryOnUniqueViolation(callable $callback, int $maxAttempts = 5): mixed
    {
        $attempts = 0;

        while (true) {
            try {
                return $callback();
            } catch (\Illuminate\Database\QueryException $e) {
                if (! $this->isUniqueViolation($e) || ++$attempts >= $maxAttempts) {
                    throw $e;
                }
            }
        }
    }

    protected function isUniqueViolation(\Illuminate\Database\QueryException $e): bool
    {
        // SQLSTATE 23000 = integrity constraint; 1062 (MySQL), 2601/2627 (SQL Server) = duplicate key.
        $state = $e->errorInfo[0] ?? '';
        $code = (int) ($e->errorInfo[1] ?? 0);

        return $state === '23000' || in_array($code, [1062, 2601, 2627], true);
    }
}
