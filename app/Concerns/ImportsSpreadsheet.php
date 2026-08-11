<?php

namespace App\Concerns;

use PhpOffice\PhpSpreadsheet\IOFactory;

trait ImportsSpreadsheet
{
    /**
     * Baca file .xlsx, .xls, atau .csv jadi array baris (tiap baris = array nilai kolom).
     * Format terdeteksi otomatis dari isi file, bukan dari ekstensi nama file.
     */
    protected function readSpreadsheetRows(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();

        // toArray: baris pertama header, sisanya data. Kolom kosong jadi null.
        $rows = $sheet->toArray(null, true, true, false);

        // Buang baris yang seluruh kolomnya kosong (baris kosong di Excel)
        return array_values(array_filter($rows, function ($row) {
            return count(array_filter($row, fn ($cell) => $cell !== null && trim((string) $cell) !== '')) > 0;
        }));
    }
}
