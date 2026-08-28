<?php

namespace Tests\Unit;

use App\Concerns\ImportsSpreadsheet;
use PHPUnit\Framework\TestCase;

class ImportsSpreadsheetTest extends TestCase
{
    public function test_sanitize_cell_neutralizes_formula_and_csv_injection_prefixes(): void
    {
        $helper = new class {
            use ImportsSpreadsheet;

            public function sanitize(mixed $cell): mixed
            {
                return $this->sanitizeCell($cell);
            }
        };

        // Karakter berbahaya dinetralkan dengan apostrof.
        $this->assertSame("'=SUM(A1)", $helper->sanitize('=SUM(A1)'));
        $this->assertSame("'+1", $helper->sanitize('+1'));
        $this->assertSame("'-5", $helper->sanitize('-5'));
        $this->assertSame("'@cmd", $helper->sanitize('@cmd'));

        // Nilai biasa & non-string dibiarkan apa adanya.
        $this->assertSame('12000000', $helper->sanitize('12000000'));
        $this->assertSame('3.05.02', $helper->sanitize('3.05.02'));
        $this->assertSame(42, $helper->sanitize(42));
        $this->assertSame('', $helper->sanitize(''));
    }

    public function test_read_spreadsheet_rows_throws_friendly_exception_on_unreadable_file(): void
    {
        $helper = new class {
            use ImportsSpreadsheet;

            public function read(string $path): array
            {
                return $this->readSpreadsheetRows($path);
            }
        };

        $tmp = tempnam(sys_get_temp_dir(), 'import_dump');
        file_put_contents($tmp, "\x89PNG\r\n\x1a\n".random_bytes(64));

        $this->expectException(\RuntimeException::class);
        $helper->read($tmp);
    }
}
