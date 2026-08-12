<x-layouts.guest>
    <div class="guest-panel__inner">
        <h2>Menunggu Persetujuan</h2>
        <p class="subtitle">
            Akun Google <strong>{{ $user->email }}</strong> berhasil terhubung, tapi belum ada role & unit yang
            diberikan admin. Hubungi admin sistem IAMS untuk mengaktifkan akun Anda.
        </p>

        <a href="{{ route('login') }}" class="btn btn-outline" style="width:100%; justify-content:center;">← Kembali ke halaman masuk</a>
    </div>
</x-layouts.guest>
