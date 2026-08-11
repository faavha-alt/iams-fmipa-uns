<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Pengadaan</div>
            <h1>{{ $realization ? 'Edit Barang' : 'Tambah Barang' }}</h1>
            <p>Detail lengkap (lokasi, kondisi, dll) diisi nanti saat finalisasi jadi aset.</p>
        </div>
        <a href="{{ route('realizations.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ $realization ? route('realizations.update', $realization->id) : route('realizations.store') }}">
            @csrf
            @if ($realization) @method('PUT') @endif

            <div class="form-group">
                <label>Pengadaan (menentukan vendor)</label>
                <select name="procurement_batch_id">
                    <option value="">-- Pilih Pengadaan --</option>
                    @foreach ($batches as $b)
                        <option value="{{ $b->id }}" @selected(old('procurement_batch_id', $realization->procurement_batch_id ?? $preselectedBatchId ?? '') == $b->id)>
                            {{ $b->nama }} @if ($b->vendor) — {{ $b->vendor->name }} @endif
                        </option>
                    @endforeach
                </select>
                @error('procurement_batch_id') <div class="form-error">{{ $message }}</div> @enderror
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Belum ada Pengadaan yang cocok? <a href="{{ route('procurement-batches.create') }}" target="_blank">Buat Pengadaan baru →</a></p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nama Barang</label>
                    <input type="text" name="item_name" value="{{ old('item_name', $realization->item_name ?? '') }}" placeholder="Contoh: Kursi kuliah lipat">
                    @error('item_name') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Unit Pemesan</label>
                    <select name="unit_id">
                        <option value="">-- Pilih Unit --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $realization->unit_id ?? '') == $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    @error('unit_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label>Kategori (opsional)</label>
                <select name="asset_category_id">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('asset_category_id', $realization->asset_category_id ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Jumlah Unit</label>
                    <input type="number" min="1" name="quantity" id="quantity" value="{{ old('quantity', $realization->quantity ?? 1) }}">
                    @error('quantity') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Harga Satuan (Rp)</label>
                    @php
                        $defaultUnitPrice = $realization && $realization->quantity > 0
                            ? $realization->cost / $realization->quantity
                            : null;
                    @endphp
                    <input type="number" step="0.01" min="0" name="unit_price" id="unit_price" value="{{ old('unit_price', $defaultUnitPrice) }}" placeholder="Harga per 1 unit">
                    @error('unit_price') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group" style="max-width: 320px;">
                <label>Total Biaya</label>
                <input type="text" id="total_preview" value="Rp 0" disabled style="background: var(--surface-sunken); color: var(--navy); font-weight: 700; font-family: var(--font-mono);">
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Otomatis: jumlah × harga satuan.</p>
            </div>

            <script>
                (function () {
                    const qty = document.getElementById('quantity');
                    const price = document.getElementById('unit_price');
                    const preview = document.getElementById('total_preview');

                    function updateTotal() {
                        const total = (parseFloat(qty.value) || 0) * (parseFloat(price.value) || 0);
                        preview.value = 'Rp ' + total.toLocaleString('id-ID');
                    }

                    qty.addEventListener('input', updateTotal);
                    price.addEventListener('input', updateTotal);
                    updateTotal();
                })();
            </script>

            <div class="form-group" style="max-width: 240px;">
                <label>Tanggal Pembelian</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date', $realization?->purchase_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                @error('purchase_date') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="notes" rows="2">{{ old('notes', $realization->notes ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn">{{ $realization ? 'Simpan Perubahan' : 'Simpan Barang' }}</button>
        </form>
    </div>
</x-layouts.app>
