<?php

namespace App\Http\Controllers;

use App\Concerns\ImportsSpreadsheet;
use App\Models\Asset;
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
        // Kolom yang boleh dipakai sorting, dipetakan ke nama kolom SQL supaya tidak bisa disuntik lewat query string.
        $sortColumns = [
            'kode' => 'kode',
            'nama' => 'nama',
            'assets_count' => 'assets_count',
        ];
        $sort = array_key_exists($request->input('sort'), $sortColumns) ? $request->input('sort') : 'assets_count';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $codes = BmnCodeReference::query()
            ->withCount('assets')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('kode', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortColumns[$sort], $direction)
            ->paginate(20)
            ->appends($request->query());

        // Statistik ringkas — dihitung dari seluruh master kode BMN & aset, tidak ikut kepotong
        // pencarian di atas, supaya jadi gambaran utuh (mirip statistik di halaman Daftar Aset).
        $totalCodes = BmnCodeReference::count();
        $codesInUse = BmnCodeReference::has('assets')->count();
        $totalAssets = Asset::count();
        $assetsWithCode = Asset::whereNotNull('simak_kode_barang')->where('simak_kode_barang', '!=', '')->count();
        $assetsWithoutCode = $totalAssets - $assetsWithCode;

        return view('bmn-codes.index', [
            'codes' => $codes,
            'totalCodes' => $totalCodes,
            'codesInUse' => $codesInUse,
            'assetsWithCode' => $assetsWithCode,
            'assetsWithoutCode' => $assetsWithoutCode,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    /**
     * Detail satu kode BMN — daftar aset yang pakai kode ini, bisa langsung diedit dari sini.
     */
    public function show(Request $request, BmnCodeReference $bmnCode): View
    {
        $assetBase = $bmnCode->assets();
        $totalAssets = (clone $assetBase)->count();
        $totalValue = (clone $assetBase)->sum('acquisition_value');

        $conditionCounts = (clone $assetBase)
            ->select('condition')->selectRaw('count(*) as total')
            ->groupBy('condition')->pluck('total', 'condition');

        $conditions = collect(['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])
            ->map(fn ($key) => [
                'key' => $key,
                'total' => $conditionCounts[$key] ?? 0,
                'percent' => $totalAssets > 0 ? round((($conditionCounts[$key] ?? 0) / $totalAssets) * 100) : 0,
            ]);

        // Kolom yang boleh dipakai sorting, dipetakan ke nama kolom SQL supaya tidak bisa disuntik lewat query string.
        $sortColumns = [
            'name' => 'assets.name',
            'brand' => 'assets.brand',
            'condition' => 'assets.condition',
            'status' => 'assets.status',
            'unit' => 'units.name',
        ];
        $sort = array_key_exists($request->input('sort'), $sortColumns) ? $request->input('sort') : 'name';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $assetsQuery = $bmnCode->assets()->with(['category', 'unit', 'location']);
        if ($sort === 'unit') {
            $assetsQuery->leftJoin('units', 'assets.unit_id', '=', 'units.id')->select('assets.*');
        }

        $assets = $assetsQuery
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('assets.name', 'like', "%{$search}%")
                        ->orWhere('assets.brand', 'like', "%{$search}%")
                        ->orWhere('assets.serial_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('condition'), fn ($q) => $q->where('assets.condition', $request->input('condition')))
            ->when($request->filled('status'), fn ($q) => $q->where('assets.status', $request->input('status')))
            ->orderBy($sortColumns[$sort], $direction)
            ->get();

        return view('bmn-codes.show', [
            'bmnCode' => $bmnCode,
            'assets' => $assets,
            'totalAssets' => $totalAssets,
            'totalValue' => $totalValue,
            'conditions' => $conditions,
            'sort' => $sort,
            'direction' => $direction,
        ]);
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
