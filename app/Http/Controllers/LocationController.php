<?php

namespace App\Http\Controllers;

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
     * Detail lokasi — daftar aset di ruangan ini, bisa langsung dihapus dari sini
     * kalau ketahuan ada input dobel, tanpa perlu bolak-balik ke halaman Aset.
     */
    public function show(Location $location): View
    {
        $location->load('unit');
        $assets = $location->assets()->with('category')->orderBy('name')->get();

        return view('locations.show', [
            'location' => $location,
            'assets' => $assets,
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
