<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Master Data</div>
            <h1>{{ $vendor ? 'Edit Vendor' : 'Tambah Vendor Baru' }}</h1>
        </div>
        <a href="{{ route('vendors.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ $vendor ? route('vendors.update', $vendor->id) : route('vendors.store') }}">
            @csrf
            @if ($vendor) @method('PUT') @endif

            <div class="form-group">
                <label>Nama Vendor</label>
                <input type="text" name="name" value="{{ old('name', $vendor->name ?? '') }}">
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nama Kontak (opsional)</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $vendor->contact_person ?? '') }}">
                </div>
                <div class="form-group">
                    <label>No. Telepon (opsional)</label>
                    <input type="text" name="phone" value="{{ old('phone', $vendor->phone ?? '') }}">
                </div>
            </div>

            <div class="form-group">
                <label>Email (opsional)</label>
                <input type="email" name="email" value="{{ old('email', $vendor->email ?? '') }}">
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Alamat (opsional)</label>
                <textarea name="address" rows="3">{{ old('address', $vendor->address ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn">{{ $vendor ? 'Simpan Perubahan' : 'Tambah Vendor' }}</button>
        </form>
    </div>
</x-layouts.app>
