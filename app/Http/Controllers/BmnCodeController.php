<?php

namespace App\Http\Controllers;

use App\Concerns\ImportsSpreadsheet;
use App\Models\BmnCodeReference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BmnCodeController extends Controller
{
    use ImportsSpreadsheet;

    public function index(Request $request): View
    {
        $codes = BmnCodeReference::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('kode', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%");
                });
            })
            ->orderBy('kode')
            ->paginate(20)
            ->appends($request->query());

        return view('bmn-codes.index', ['codes' => $codes]);
    }

    /**
     * Typeahead buat form aset — dulunya seluruh tabel di-load ke <datalist> di setiap
     * buka form, sekarang cuma kirim beberapa hasil yang cocok lewat AJAX.
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $codes = BmnCodeReference::query()
            ->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            })
            ->orderBy('kode')
            ->limit(20)
            ->get(['kode', 'nama']);

        return response()->json($codes);
    }

    public function create(): View
    {
        return view('bmn-codes.form', ['code' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        BmnCodeReference::create($this->validated($request));

        return redirect()->route('bmn-codes.index')->with('message', 'Kode BMN baru berhasil ditambahkan.');
    }

    public function edit(BmnCodeReference $bmnCode): View
    {
        return view('bmn-codes.form', ['code' => $bmnCode]);
    }

    public function update(Request $request, BmnCodeReference $bmnCode): RedirectResponse
    {
        $bmnCode->update($this->validated($request, $bmnCode));

        return redirect()->route('bmn-codes.index')->with('message', 'Kode BMN berhasil diperbarui.');
    }

    public function destroy(BmnCodeReference $bmnCode): RedirectResponse
    {
        $bmnCode->delete(); // soft delete

        return redirect()->route('bmn-codes.index')->with('message', "{$bmnCode->kode} berhasil dihapus.");
    }

    public function importForm(): View
    {
        return view('bmn-codes.import');
    }

    public function downloadTemplate()
    {
        $columns = ['kode', 'nama'];
        $examples = [
            ['3.05.02.01.001', 'Kursi Besi/Metal'],
            ['3.05.02.03.004', 'Laptop'],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($columns, null, 'A1');
        $sheet->fromArray($examples, null, 'A2');
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);

        $tempPath = tempnam(sys_get_temp_dir(), 'template_bmn').'.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()->download($tempPath, 'template_kode_bmn.xlsx')->deleteFileAfterSend(true);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv,txt']);

        $rows = $this->readSpreadsheetRows($request->file('file')->getRealPath());
        $header = array_map(fn ($h) => trim((string) $h), array_shift($rows));

        $success = 0;
        $failed = [];

        foreach ($rows as $i => $row) {
            if (count($row) < count($header)) {
                $failed[] = 'Baris '.($i + 2).': jumlah kolom tidak sesuai template.';
                continue;
            }

            $data = array_combine($header, $row);
            $data = array_map(fn ($v) => trim((string) ($v ?? '')), $data);

            if (empty($data['kode']) || empty($data['nama'])) {
                $failed[] = 'Baris '.($i + 2).': kode atau nama kosong.';
                continue;
            }

            try {
                BmnCodeReference::updateOrCreate(
                    ['kode' => $data['kode']],
                    ['nama' => $data['nama']]
                );
                $success++;
            } catch (\Exception $e) {
                $failed[] = 'Baris '.($i + 2).': '.$e->getMessage();
            }
        }

        $message = "{$success} kode BMN berhasil diimport/diperbarui.";
        if (count($failed) > 0) {
            $message .= ' '.count($failed).' baris gagal.';
        }

        return redirect()->route('bmn-codes.index')
            ->with('message', $message)
            ->with('importErrors', $failed);
    }

    private function validated(Request $request, ?BmnCodeReference $code = null): array
    {
        return $request->validate([
            'kode' => ['required', 'string', 'max:100', Rule::unique('bmn_code_references', 'kode')->ignore($code?->id)],
            'nama' => 'required|string|max:255',
        ]);
    }
}
