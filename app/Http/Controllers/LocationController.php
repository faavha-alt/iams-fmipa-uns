<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $locations = Location::query()
            ->with('unit')
            ->withCount('assets')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->input('search').'%'))
            ->when($request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->input('unit_id')))
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->query());

        return view('locations.index', [
            'locations' => $locations,
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('locations.form', [
            'location' => null,
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Location::create($this->validated($request));

        return redirect()->route('locations.index')->with('message', 'Lokasi baru berhasil ditambahkan.');
    }

    public function edit(Location $location): View
    {
        return view('locations.form', [
            'location' => $location,
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    /**
     * Detail lokasi — rekap kategori & kondisi, tabel aset yang bisa difilter/diurutkan,
     * QR code, dan bisa langsung dihapus dari sini kalau ketahuan ada input dobel.
     */
    public function show(Request $request, Location $location): View
    {
        $location->load('unit');

        $assetBase = $location->assets();
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

        $categoryRecap = (clone $assetBase)
            ->select('asset_category_id')->selectRaw('count(*) as total')
            ->groupBy('asset_category_id')
            ->with('category')
            ->get()
            ->sortByDesc('total')
            ->map(fn ($row) => [
                'name' => $row->category?->name ?? 'Tanpa Kategori',
                'total' => $row->total,
                'percent' => $totalAssets > 0 ? round(($row->total / $totalAssets) * 100) : 0,
            ])
            ->values();

        // Kolom yang boleh dipakai sorting, dipetakan ke nama kolom SQL supaya tidak bisa disuntik lewat query string.
        $sortColumns = [
            'name' => 'assets.name',
            'brand' => 'assets.brand',
            'condition' => 'assets.condition',
            'status' => 'assets.status',
            'category' => 'asset_categories.name',
        ];
        $sort = array_key_exists($request->input('sort'), $sortColumns) ? $request->input('sort') : 'name';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $assetsQuery = $location->assets()->with('category');
        if ($sort === 'category') {
            $assetsQuery->leftJoin('asset_categories', 'assets.asset_category_id', '=', 'asset_categories.id')->select('assets.*');
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
            ->when($request->filled('category_id'), fn ($q) => $q->where('assets.asset_category_id', $request->input('category_id')))
            ->when($request->filled('condition'), fn ($q) => $q->where('assets.condition', $request->input('condition')))
            ->when($request->filled('status'), fn ($q) => $q->where('assets.status', $request->input('status')))
            ->orderBy($sortColumns[$sort], $direction)
            ->get();

        $dbrUrl = route('locations.public-info', $location->id);

        return view('locations.show', [
            'location' => $location,
            'assets' => $assets,
            'totalAssets' => $totalAssets,
            'totalValue' => $totalValue,
            'conditions' => $conditions,
            'categoryRecap' => $categoryRecap,
            'categories' => AssetCategory::orderBy('name')->get(),
            'qrSvg' => QrCode::size(120)->generate($dbrUrl),
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    /**
     * Halaman publik (tanpa login) yang dibuka lewat scan QR code di lembar DBR.
     */
    public function publicInfo(Location $location): View
    {
        $location->load('unit');
        $assets = $location->assets()
            ->with('category')
            ->where('status', '!=', 'dihapuskan')
            ->orderBy('name')
            ->get();

        return view('locations.public-info', [
            'location' => $location,
            'assets' => $assets,
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $location->update($this->validated($request));

        return redirect()->route('locations.index')->with('message', 'Data lokasi berhasil diperbarui.');
    }

    /**
     * Daftar Barang Ruangan (DBR) — versi cetak untuk ditempel fisik di ruangan.
     * QR code di lembar ini mengarah ke halaman publik (lihat publicInfo()), jadi discan
     * kapan pun akan selalu nampilkan data aset terkini di ruangan itu, tanpa perlu login.
     */
    public function dbr(Location $location): View
    {
        $location->load('unit');
        $assets = $location->assets()
            ->with('category')
            ->where('status', '!=', 'dihapuskan')
            ->orderBy('name')
            ->get();

        $dbrUrl = route('locations.public-info', $location->id);

        return view('locations.dbr', [
            'location' => $location,
            'assets' => $assets,
            'qrSvg' => QrCode::size(130)->generate($dbrUrl),
            'kopLogo' => Setting::get('bast_kop_logo'),
            'kopBaris1' => Setting::get('bast_kop_baris1', 'Fakultas Matematika dan Ilmu Pengetahuan Alam'),
            'kopBaris2' => Setting::get('bast_kop_baris2', 'Universitas Sebelas Maret'),
        ]);
    }

    public function destroy(Location $location): RedirectResponse
    {
        if ($location->assets()->exists()) {
            return redirect()->route('locations.index')
                ->with('error', "Tidak bisa hapus {$location->name}: masih ada aset yang ditempatkan di sini. Pindahkan aset-nya dulu.");
        }

        $location->delete();

        return redirect()->route('locations.index')->with('message', "{$location->name} berhasil dihapus.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:255',
            'building' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:50',
            'room_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);
    }
}
