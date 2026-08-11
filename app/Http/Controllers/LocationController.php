<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

    public function update(Request $request, Location $location): RedirectResponse
    {
        $location->update($this->validated($request));

        return redirect()->route('locations.index')->with('message', 'Data lokasi berhasil diperbarui.');
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
