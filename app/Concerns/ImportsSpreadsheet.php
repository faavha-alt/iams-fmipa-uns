<?php

namespace App\Concerns;

use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

trait ImportsSpreadsheet
{
    /**
     * Baca file .xlsx, .xls, atau .csv jadi array baris (tiap baris = array nilai kolom).
     * Format terdeteksi otomatis dari isi file, bukan dari ekstensi nama file.
     *
     * @throws RuntimeException kalau file tidak bisa dibaca/rusak — supaya pemanggil bisa
     *                          menampilkan pesan ramah, bukan HTTP 500 mentah.
     */
    protected function readSpreadsheetRows(string $path): array
    {
        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();

            // calculateFormulas=false: jangan mengeksekusi formula sel. File yang dimanipulasi bisa
            // berisi formula =cmd/... (formula injection) atau referensi eksternal yang memicu
            // request keluar (SSRF) — menonaktifkan kalkulasi formula memutus keduanya saat baca.
            $rows = $sheet->toArray(null, false, true, false);

            // Netralkan sel teks yang diawali karakter berbahaya (potensi formula/CSV injection:
            // '=', '+', '-', '@', tab). Apostrof depan membuat nilai diperlakukan sebagai teks polos.
            $rows = array_map(fn ($row) => array_map(fn ($cell) => $this->sanitizeCell($cell), $row), $rows);

            // Buang baris yang seluruh kolomnya kosong (baris kosong di Excel)
            return array_values(array_filter($rows, function ($row) {
                return count(array_filter($row, fn ($cell) => $cell !== null && trim((string) $cell) !== '')) > 0;
            }));
        } catch (\Throwable $e) {
            throw new RuntimeException('File tidak bisa dibaca. Pastikan file .xlsx/.xls/.csv/.txt yang valid.', 0, $e);
        }
    }

    /**
     * Cegah formula/CSV injection untuk nilai teks dari spreadsheet. Hanya string yang diawali
     * '=', '+', '-', '@', atau tab yang dinetralkan; nilai non-string (angka, dsb.) dibiarkan.
     */
    private function sanitizeCell(mixed $cell): mixed
    {
        if (! is_string($cell)) {
            return $cell;
        }

        if ($cell === '') {
            return $cell;
        }

        $first = $cell[0];
        if (in_array($first, ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$cell;
        }

        return $cell;
    }
}
