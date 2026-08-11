<x-layouts.app>
    <div class="page-header">
        <div>
            <div class="page-header__eyebrow">Master Data</div>
            <h1>{{ $location ? 'Edit Lokasi' : 'Tambah Lokasi Baru' }}</h1>
        </div>
        <a href="{{ route('locations.index') }}" class="btn btn-outline">← Kembali</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ $location ? route('locations.update', $location->id) : route('locations.store') }}">
            @csrf
            @if ($location) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group">
                    <label>Nama Lokasi</label>
                    <input type="text" name="name" value="{{ old('name', $location->name ?? '') }}" placeholder="Contoh: Ruang Dosen Lantai 2">
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <select name="unit_id">
                        <option value="">-- Pilih Unit --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $location->unit_id ?? '') == $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    @error('unit_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Gedung (opsional)</label>
                    <input type="text" name="building" value="{{ old('building', $location->building ?? '') }}">
                </div>
                <div class="form-group">
                    <label>Lantai (opsional)</label>
                    <input type="text" name="floor" value="{{ old('floor', $location->floor ?? '') }}">
                </div>
            </div>

            <div class="form-group" style="max-width: 240px;">
                <label>Kode Ruang (opsional)</label>
                <input type="text" name="room_code" value="{{ old('room_code', $location->room_code ?? '') }}" placeholder="Contoh: A-201">
            </div>

            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="notes" rows="2">{{ old('notes', $location->notes ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn">{{ $location ? 'Simpan Perubahan' : 'Tambah Lokasi' }}</button>
        </form>
    </div>
</x-layouts.app>
