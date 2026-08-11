<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $vendors = Vendor::query()
            ->withCount('assets')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->query());

        return view('vendors.index', ['vendors' => $vendors]);
    }

    public function create(): View
    {
        return view('vendors.form', ['vendor' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Vendor::create($this->validated($request));

        return redirect()->route('vendors.index')->with('message', 'Vendor baru berhasil ditambahkan.');
    }

    public function edit(Vendor $vendor): View
    {
        return view('vendors.form', ['vendor' => $vendor]);
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $vendor->update($this->validated($request));

        return redirect()->route('vendors.index')->with('message', 'Data vendor berhasil diperbarui.');
    }

    public function toggleActive(Vendor $vendor): RedirectResponse
    {
        $vendor->update(['is_active' => ! $vendor->is_active]);

        return redirect()->route('vendors.index')
            ->with('message', $vendor->is_active ? 'Vendor diaktifkan kembali.' : 'Vendor dinonaktifkan.');
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        // aman dihapus: relasi vendor_id di assets & purchase_realizations pakai nullOnDelete
        $vendor->delete();

        return redirect()->route('vendors.index')->with('message', "{$vendor->name} berhasil dihapus.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);
    }
}
