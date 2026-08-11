<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = AssetCategory::query()
            ->with('parent')
            ->withCount('assets')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->input('search').'%'))
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->query());

        return view('categories.index', ['categories' => $categories]);
    }

    public function create(): View
    {
        return view('categories.form', [
            'category' => null,
            'parentOptions' => AssetCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['code'] = $this->generateCode($data['parent_id'] ?? null);

        AssetCategory::create($data);

        return redirect()->route('categories.index')->with('message', "Kategori baru berhasil ditambahkan dengan kode {$data['code']}.");
    }

    public function edit(AssetCategory $category): View
    {
        return view('categories.form', [
            'category' => $category,
            'parentOptions' => AssetCategory::where('id', '!=', $category->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, AssetCategory $category): RedirectResponse
    {
        $data = $this->validated($request, $category);
        $category->update($data);

        return redirect()->route('categories.index')->with('message', 'Kategori berhasil diperbarui.');
    }

    public function destroy(AssetCategory $category): RedirectResponse
    {
        if ($category->assets()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', "Tidak bisa hapus {$category->name}: masih dipakai oleh aset yang ada. Edit aset-nya dulu kalau mau ganti kategori.");
        }

        if ($category->children()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', "Tidak bisa hapus {$category->name}: masih punya sub-kategori.");
        }

        $category->delete();

        return redirect()->route('categories.index')->with('message', "{$category->name} berhasil dihapus.");
    }

    private function validated(Request $request, ?AssetCategory $category = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => ['nullable', 'exists:asset_categories,id', Rule::notIn([$category?->id])],
            'unit_satuan' => 'nullable|string|max:50',
            'specification' => 'nullable|string',
            'useful_life_years' => 'nullable|integer|min:1|max:50',
        ]);
    }

    /**
     * Kode kategori otomatis, mengikuti pola: [KODE_INDUK]-[urutan] atau KTG-[urutan] kalau tanpa induk.
     */
    private function generateCode(?int $parentId): string
    {
        $prefix = 'KTG';
        if ($parentId) {
            $parent = AssetCategory::find($parentId);
            $prefix = $parent?->code ?? 'KTG';
        }

        $sequence = AssetCategory::where('parent_id', $parentId)->count() + 1;
        $code = sprintf('%s-%02d', $prefix, $sequence);

        while (AssetCategory::where('code', $code)->exists()) {
            $sequence++;
            $code = sprintf('%s-%02d', $prefix, $sequence);
        }

        return $code;
    }
}
