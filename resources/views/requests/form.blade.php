<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Pengajuan</div>
            <h1>Ajukan Aset Baru</h1>
            <p>Permintaan akan dikirim ke admin untuk ditinjau.</p>
        </div>
        <a href="{{ route('requests.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('requests.create') }}" class="filters" style="margin-bottom: 0;">
            <select name="year" onchange="this.form.submit()">
                @foreach ($availableYears as $y)
                    <option value="{{ $y }}" @selected($y == $selectedYear)>Tahun Anggaran {{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="card">
        @if ($hasPagu)
            <div class="alert-success" style="background: var(--sky-pale); color: var(--cerulean-dark);">
                Sisa pagu anggaran unit Anda tahun <strong>{{ $selectedYear }}</strong>: <strong>Rp {{ number_format($sisaPagu, 0, ',', '.') }}</strong>
            </div>
        @else
            <div class="alert-success" style="background: var(--gold-pale); color: #92660F;">
                Pagu unit Anda untuk tahun {{ $selectedYear }} belum ditentukan admin — pengajuan tetap bisa dikirim, tapi belum ada pembanding sisa anggaran.
            </div>
        @endif

        <form method="POST" action="{{ route('requests.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="fiscal_year" value="{{ $selectedYear }}">

            <div class="form-row">
                <div class="form-group">
                    <label>Nama Barang</label>
                    <input type="text" name="item_name" value="{{ old('item_name') }}" placeholder="Contoh: Laptop untuk Lab Fisika Dasar">
                    @error('item_name') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Kategori (opsional)</label>
                    <select name="asset_category_id">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('asset_category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Jumlah</label>
                    <input type="number" min="1" name="quantity" id="quantity" value="{{ old('quantity', 1) }}">
                    @error('quantity') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Estimasi Harga Satuan (Rp, opsional)</label>
                    <input type="number" step="0.01" min="0" name="estimated_unit_price" id="estimated_unit_price" value="{{ old('estimated_unit_price') }}" placeholder="Perkiraan harga per 1 unit">
                </div>
            </div>

            <div class="form-group" style="max-width: 320px;">
                <label>Estimasi Total</label>
                <input type="text" id="total_preview" value="Rp 0" disabled style="background: var(--surface-sunken); color: var(--navy); font-weight: 700; font-family: var(--font-mono);">
            </div>

            <script>
                (function () {
                    const qty = document.getElementById('quantity');
                    const price = document.getElementById('estimated_unit_price');
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

            <div class="form-group">
                <label>Link Referensi Pembelian</label>
                <input type="url" name="purchase_link" value="{{ old('purchase_link') }}" placeholder="https://tokopedia.com/... atau link toko/vendor lainnya">
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Wajib diisi — jadi acuan harga wajar buat admin saat review.</p>
                @error('purchase_link') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Gambar Pendukung</label>
                <input type="file" name="supporting_image" accept="image/png, image/jpeg, image/webp">
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Wajib diisi — screenshot produk/toko, atau foto barang serupa. Format JPG/PNG/WEBP, maks 2MB.</p>
                @error('supporting_image') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Alasan / Justifikasi</label>
                <textarea name="reason" rows="4" placeholder="Jelaskan kebutuhan dan urgensinya...">{{ old('reason') }}</textarea>
                @error('reason') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn">Kirim Pengajuan</button>
        </form>
    </div>
</x-layouts.app>
