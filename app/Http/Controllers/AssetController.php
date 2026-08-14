<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetRequest;
use App\Models\Location;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Concerns\ImportsSpreadsheet;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AssetController extends Controller
{
    use ImportsSpreadsheet;
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';

        $assets = Asset::query()
            ->with(['category', 'unit', 'location'])
            ->when(! $isAdmin, fn ($q) => $q->where('unit_id', $user->unit_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_code', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
                });
            })
            ->when($isAdmin && $request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->input('unit_id')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('asset_category_id', $request->input('category_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        // Statistik ringkas — dihitung dari seluruh aset yang berhak dilihat user
        // (scoped per unit kalau bukan admin), TIDAK ikut kepotong filter pencarian di atas,
        // supaya jadi gambaran utuh inventaris, bukan cuma hasil filter saat itu.
        $statsBase = Asset::query()->when(! $isAdmin, fn ($q) => $q->where('unit_id', $user->unit_id));
        $totalAssets = (clone $statsBase)->count();
        $totalValue = (clone $statsBase)->sum('acquisition_value');

        $conditionCounts = (clone $statsBase)
            ->select('condition')
            ->selectRaw('count(*) as total')
            ->groupBy('condition')
            ->pluck('total', 'condition');

        $statusCounts = (clone $statsBase)
            ->select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $conditions = collect(['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])
            ->map(fn ($key) => [
                'key' => $key,
                'total' => $conditionCounts[$key] ?? 0,
                'percent' => $totalAssets > 0 ? round((($conditionCounts[$key] ?? 0) / $totalAssets) * 100) : 0,
            ]);

        $statuses = collect(['aktif', 'dalam_perbaikan', 'dipinjamkan', 'dihapuskan'])
            ->map(fn ($key) => [
                'key' => $key,
                'total' => $statusCounts[$key] ?? 0,
                'percent' => $totalAssets > 0 ? round((($statusCounts[$key] ?? 0) / $totalAssets) * 100) : 0,
            ]);

        $goodConditionPercent = $totalAssets > 0 ? round((($conditionCounts['baik'] ?? 0) / $totalAssets) * 100) : 0;
        $needsAttentionCount = ($conditionCounts['rusak_ringan'] ?? 0) + ($conditionCounts['rusak_berat'] ?? 0) + ($conditionCounts['hilang'] ?? 0);

        return view('assets.index', [
            'assets' => $assets,
            'units' => Unit::orderBy('name')->get(),
            'categories' => AssetCategory::orderBy('name')->get(),
            'isAdmin' => $isAdmin,
            'totalAssets' => $totalAssets,
            'totalValue' => $totalValue,
            'conditions' => $conditions,
            'statuses' => $statuses,
            'goodConditionPercent' => $goodConditionPercent,
            'needsAttentionCount' => $needsAttentionCount,
        ]);
    }

    public function create(): View
    {
        $this->authorizeAdmin();

        return view('assets.form', array_merge($this->formOptions(), [
            'asset' => null,
            'openRequests' => AssetRequest::where('status', 'disetujui')
                ->whereDoesntHave('fulfilledAsset')
                ->with('unit')
                ->get(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $this->validated($request);
        Asset::create($data); // asset_code & qr_code auto-generate di model

        return redirect()->route('assets.index')->with('message', 'Aset baru berhasil ditambahkan.');
    }

    public function edit(Asset $asset): View
    {
        $this->authorizeAdmin();

        return view('assets.form', array_merge($this->formOptions(), [
            'asset' => $asset,
            'openRequests' => AssetRequest::where('status', 'disetujui')
                ->where(function ($q) use ($asset) {
                    $q->whereDoesntHave('fulfilledAsset')
                        ->orWhereHas('fulfilledAsset', fn ($sub) => $sub->where('assets.id', $asset->id));
                })
                ->with('unit')
                ->get(),
        ]));
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $this->validated($request);
        $asset->update($data);

        return redirect()->route('assets.index')->with('message', 'Aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $this->authorizeAdmin();

        $asset->delete();

        return redirect()->back()->with('message', 'Aset berhasil dihapus.');
    }

    /**
     * Hapus banyak aset sekaligus (dicentang dari tabel) — buat bersihin input dobel
     * tanpa harus konfirmasi hapus satu-satu.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'asset_ids' => 'required|array|min:1',
            'asset_ids.*' => 'exists:assets,id',
        ]);

        $count = Asset::whereIn('id', $data['asset_ids'])->delete();

        return redirect()->back()->with('message', "{$count} aset berhasil dihapus.");
    }

    /**
     * Cetak sticker untuk aset yang dicentang — F4, ~20 sticker per halaman.
     * QR di tiap sticker mengarah ke halaman publik aset itu (lihat publicInfo()).
     */
    public function stickers(Request $request): View
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'asset_ids' => 'required|array|min:1',
            'asset_ids.*' => 'exists:assets,id',
        ]);

        $assets = Asset::whereIn('id', $data['asset_ids'])->orderBy('name')->get();

        $items = $assets->map(fn (Asset $asset) => [
            'asset' => $asset,
            'qrSvg' => QrCode::size(72)->generate(route('assets.public-info', $asset->qr_code)),
        ]);

        return view('assets.stickers', ['items' => $items]);
    }

    /**
     * Halaman publik (tanpa login) yang dibuka lewat scan QR code di sticker aset.
     */
    public function publicInfo(Asset $asset): View
    {
        $asset->load(['category', 'unit', 'location', 'vendor']);

        return view('assets.public-info', ['asset' => $asset]);
    }

    public function importForm(): View
    {
        $this->authorizeAdmin();

        return view('assets.import', [
            'categories' => AssetCategory::orderBy('code')->get(),
            'units' => Unit::orderBy('code')->get(),
            'locationsByUnit' => Location::with('unit')->orderBy('name')->get()->groupBy(fn ($loc) => $loc->unit->name),
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorizeAdmin();

        $columns = ['nama_aset', 'kategori_kode', 'unit_kode', 'lokasi_nama', 'merk', 'model', 'no_seri', 'tanggal_perolehan', 'sumber_perolehan', 'nilai_perolehan', 'kondisi', 'status', 'kode_barang_simak', 'vendor_nama', 'nomor_urut_simak'];
        $example = ['Laptop Dell Latitude', 'ELK-KOM', 'INF', 'Ruang Dosen Informatika', 'Dell', 'Latitude 5420', 'SN123456', '2026-01-15', 'pengadaan', '12000000', 'baik', 'aktif', '3.05.02.03.004', 'CV. RISC COMPUTER', '1'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($columns, null, 'A1');
        $sheet->fromArray($example, null, 'A2');
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:O1')->getFont()->setBold(true);

        $tempPath = tempnam(sys_get_temp_dir(), 'template_aset').'.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tempPath);

        return response()->download($tempPath, 'template_import_aset.xlsx')->deleteFileAfterSend(true);
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv,txt']);

        $rows = $this->readSpreadsheetRows($request->file('file')->getRealPath());
        $header = array_map(fn ($h) => trim((string) $h), array_shift($rows));

        $success = 0;
        $failed = [];

        foreach ($rows as $i => $row) {
            if (count(array_filter($row)) === 0) {
                continue; // baris kosong
            }

            if (count($row) < count($header)) {
                $failed[] = 'Baris '.($i + 2).': jumlah kolom tidak sesuai template.';
                continue;
            }

            $data = array_combine($header, $row);
            $data = array_map(fn ($v) => trim((string) ($v ?? '')), $data);

            $category = AssetCategory::where('code', $data['kategori_kode'] ?? '')->first();
            $unit = Unit::where('code', $data['unit_kode'] ?? '')->first();

            if (! $category) {
                $failed[] = 'Baris '.($i + 2).": kode kategori '{$data['kategori_kode']}' tidak ditemukan.";
                continue;
            }
            if (! $unit) {
                $failed[] = 'Baris '.($i + 2).": kode unit '{$data['unit_kode']}' tidak ditemukan.";
                continue;
            }

            $location = ! empty($data['lokasi_nama'])
                ? Location::where('name', $data['lokasi_nama'])->where('unit_id', $unit->id)->first()
                : null;

            $vendor = ! empty($data['vendor_nama'])
                ? Vendor::firstOrCreate(['name' => $data['vendor_nama']])
                : null;

            try {
                Asset::create([
                    'name' => $data['nama_aset'],
                    'brand' => $data['merk'] ?: null,
                    'model' => $data['model'] ?: null,
                    'serial_number' => $data['no_seri'] ?: null,
                    'asset_category_id' => $category->id,
                    'unit_id' => $unit->id,
                    'location_id' => $location?->id,
                    'vendor_id' => $vendor?->id,
                    'acquisition_date' => $data['tanggal_perolehan'] ?: null,
                    'acquisition_source' => in_array($data['sumber_perolehan'], ['pengadaan', 'hibah', 'bantuan', 'lainnya']) ? $data['sumber_perolehan'] : 'pengadaan',
                    'acquisition_value' => is_numeric($data['nilai_perolehan']) ? $data['nilai_perolehan'] : 0,
                    'condition' => in_array($data['kondisi'], ['baik', 'rusak_ringan', 'rusak_berat', 'hilang']) ? $data['kondisi'] : 'baik',
                    'status' => in_array($data['status'], ['aktif', 'dalam_perbaikan', 'dipinjamkan', 'dihapuskan']) ? $data['status'] : 'aktif',
                    'simak_kode_barang' => $data['kode_barang_simak'] ?: null,
                    'simak_nup' => is_numeric($data['nomor_urut_simak'] ?? null) ? $data['nomor_urut_simak'] : null,
                ]);
                $success++;
            } catch (\Exception $e) {
                $failed[] = 'Baris '.($i + 2).': '.$e->getMessage();
            }
        }

        $message = "{$success} aset berhasil diimport.";
        if (count($failed) > 0) {
            $message .= ' '.count($failed).' baris gagal (lihat detail di bawah).';
        }

        return redirect()->route('assets.index')
            ->with('message', $message)
            ->with('importErrors', $failed);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Hanya admin yang bisa mengelola data aset.');
    }

    private function formOptions(): array
    {
        return [
            'categories' => AssetCategory::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'vendors' => Vendor::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'asset_request_id' => 'nullable|exists:asset_requests,id',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'unit_id' => 'required|exists:units,id',
            'location_id' => 'nullable|exists:locations,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'current_user_id' => 'nullable|exists:users,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'acquisition_date' => 'nullable|date',
            'acquisition_source' => 'required|in:pengadaan,hibah,bantuan,lainnya',
            'acquisition_value' => 'required|numeric|min:0',
            'condition' => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
            'status' => 'required|in:aktif,dalam_perbaikan,dipinjamkan,dihapuskan',
            'notes' => 'nullable|string',
            // SIMAK BMN (opsional, isi kalau sudah punya data resminya)
            'simak_kode_barang' => 'nullable|string|max:255',
            'simak_nup' => 'nullable|integer|min:1',
            'simak_kode_lokasi' => 'nullable|string|max:255',
            'simak_tahun_perolehan' => 'nullable|integer|min:2000|max:2100',
        ]);
    }
}
