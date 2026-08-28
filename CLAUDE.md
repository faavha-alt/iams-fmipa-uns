# IAMS FMIPA UNS — Integrated Asset Management System

Sistem manajemen aset & pengadaan untuk Fakultas MIPA, Universitas Sebelas Maret.

## Stack

- **Laravel 13**, PHP 8.4+
- **MySQL** (bukan SQLite — sudah dimigrasikan)
- **Blade + vanilla JS** — TIDAK pakai Livewire (sempat dicoba, dilepas karena masalah asset-loading di shared hosting), TIDAK pakai Tailwind/Vite (scaffolding bawaan `laravel new` sudah dibuang — tidak ada `package.json`/`vite.config.js`/build step sama sekali, lihat `public/css/frontend.css` di bawah)
- **CSS custom** di `public/css/frontend.css` — tema cerulean blue (`#0E7DA7`) + gold (`#E9A828`), font Montserrat (judul) + Poppins (body), di-load langsung lewat `asset()`, bukan Vite
- **PhpSpreadsheet** — dipakai untuk import/export Excel (aset, kode BMN)
- Hosting: shared hosting (panel "jogo-os"), akses via SSH

## Deploy

Ada `deploy.sh` di root repo — commit & push perubahan seperti biasa, lalu jalankan (lewat Git Bash di Windows):

```bash
./deploy.sh      # minta konfirmasi sebelum migrate di server
./deploy.sh -y   # skip konfirmasi
```

Script ini otomatis: cek working tree bersih & di branch `main` → `git push origin main` → SSH ke server (alias `iams-fmipa`) → `git pull` → `composer install` → `migrate --force` → clear cache. Kalau butuh deploy manual (misal komposer gagal di tengah jalan):

```bash
ssh iams-fmipa   # alias SSH, lihat ~/.ssh/config
cd ~/htdocs/aset.mipa.uns.ac.id
git pull
COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev
php artisan migrate --force
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

### Konek dari PC/laptop lain (kantor, rumah, dst.)

Alias `iams-fmipa` cuma ada di `~/.ssh/config` per-mesin — tiap mesin baru harus disetel ulang:

1. Generate keypair baru di mesin itu: `ssh-keygen -t ed25519 -C "nama@device"` (jangan pakai satu private key yang sama di banyak mesin — kalau satu laptop hilang, cukup cabut satu key di server, bukan revoke ganti-ganti semua device).
2. Tambahkan blok ini ke `~/.ssh/config` mesin itu:
   ```
   Host iams-fmipa
     HostName 203.6.149.150
     Port 1103
     User aset
     IdentityFile ~/.ssh/id_ed25519
   ```
3. Daftarkan public key baru ke server (butuh password sekali): `ssh-copy-id -p 1103 aset@203.6.149.150`, atau kalau tidak ada `ssh-copy-id`, login manual (PuTTY dsb.) lalu `echo "<isi .pub>" >> ~/.ssh/authorized_keys`.
4. Tes: `ssh iams-fmipa`. Setelah itu `./deploy.sh` langsung bisa jalan dari mesin itu.

**PENTING**: server pakai shared hosting dengan memory terbatas. Command composer/artisan yang berat kadang perlu `php -d memory_limit=-1`.

**PENTING**: `.env` di server TIDAK ikut git (sengaja, demi keamanan) — perubahan `.env` di server harus dilakukan manual per-server, tidak lewat `git pull`.

## Arsitektur & Modul

Tidak ada file routing terpisah per modul — semua di `routes/web.php`, dikelompokkan dalam middleware group `auth` dan `admin`.

### Role & Akses
Kolom `users.role` (enum): `admin`, `kepala_unit`, `staff`, `pimpinan`.
- **admin (operator)**: akses penuh — Satu-satunya role yang boleh MENGELOLA (membuat/mengedit/menghapus/menfinalisasi/mengunggah, dst.). Rute tulis semua ada di grup middleware `admin`.
- **kepala_unit / staff**: HANYA MELIHAT (read-only). Data di-scope ke **unit sendiri** — tidak bisa membuka unit lain. Bisa mengajukan permintaan aset (`requests.*`).
- **pimpinan**: HANYA MELIHAT, tapi **semua unit** (porsi laporan/reviewer), tidak bisa mengelola.
- Kebijakan ini dijalankan lewat trait `App\Concerns\RestrictsByRole` (`canSeeAllUnits()`, `canAccessUnit()`, `restrictByRole()`) + pemisahan rute di `routes/web.php`: rute LIHAT (index/show/dbr/print) ada di grup `auth`, rute TULIS ada di grup `admin`.
- Middleware custom: `EnsureUserIsAdmin` (alias `admin`, cek `role === 'admin'`) untuk rute tulis; `EnsureUserIsActive` (cek `is_active`/`is_approved` per-request, bukan cuma saat login).

### Situs Publik vs Aplikasi (login)
- `/` (nama rute `home`) adalah **halaman depan publik** — beranda informatif (statistik ringkas tanpa nilai rupiah, pengumuman terbaru, link dokumentasi), TIDAK butuh login, di-handle `PublicController`. Login **bukan** lagi di `/`, tapi di `/login` sendiri (link "Masuk" di navbar situs publik).
- Tiga layout Blade berbeda, jangan ketuker: `x-layouts.app` (shell+sidebar, buat halaman setelah login), `x-layouts.guest` (split-screen branding+form, cuma buat `auth.login` & `auth.pending`), `x-layouts.public` (navbar Beranda/Pengumuman/Dokumentasi + footer, buat `PublicController` & `AnnouncementController@public*`).
- Rute publik situs (di luar grup `auth`, didefinisikan di atas grup itu di `routes/web.php`): `/` (beranda), `/dokumentasi` (masih placeholder "segera hadir"), `/pengumuman` + `/pengumuman/{announcement}` (daftar & detail, cuma yang `is_published=true`).

### Login & Google OAuth
- Dua cara login: email+password biasa (`LoginController`), atau **Masuk dengan Google** (`GoogleAuthController`, pakai `laravel/socialite`).
- Google login pertama kali (email belum pernah ada di `users`) → bikin baris user baru dengan `role='staff'` placeholder, `is_approved=false`, TIDAK login otomatis — ditampilkan halaman `auth.pending`. Kalau email sudah ada (didaftarkan admin manual) → `google_id` ditautkan ke user itu.
- `is_approved` (kolom baru, default `true`) beda makna dari `is_active`: `is_approved=false` = "belum di-approve admin" (khusus user baru dari Google), `is_active=false` = "sengaja dinonaktifkan admin". Keduanya di-cek di login (password maupun Google) — kalau salah satu `false`, tidak bisa masuk.
- Approve user pending = admin buka `users.edit`, isi role+unit, simpan — `UserController::update` otomatis set `is_approved=true` saat itu (tidak ada tombol approve terpisah).
- Butuh env var `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` (OAuth client dari Google Cloud Console, authorized redirect URI = `https://aset.mipa.uns.ac.id/auth/google/callback`) — set manual di `.env` server, tidak ikut git.

### Modul yang sudah ada
| Modul | Controller | Keterangan |
|---|---|---|
| Dashboard | `DashboardController` | Statistik ringkas, scoped per role |
| Aset | `AssetController` | CRUD + import Excel massal. Cetak sticker aset terpilih (F4, ~20/lembar) berisi QR ke halaman publik `assets.public-info` |
| Kategori Aset | `CategoryController` | Hierarkis (`parent_id`), ada `unit_satuan` |
| Unit/Prodi | `UnitController` | Hierarkis, kode auto-generate |
| Lokasi | `LocationController` | Terikat ke unit. Detail lokasi ada rekap kategori/kondisi + tabel aset filterable/sortable + cetak DBR (Daftar Barang Ruangan, F4) dengan QR ke halaman publik `locations.public-info` |
| Vendor | `VendorController` | Soft delete |
| Pengajuan Aset | `AssetRequestController` | Alur approve/reject dari pengaju ke admin, wajib upload gambar + link referensi |
| **Pengadaan** (`procurement_batches`) | `ProcurementBatchController` | **Header transaksi** — vendor, tanggal, nomor dokumen. Vendor **wajib** di validasi (`required`). Kolom `vendor_id` di DB tetap nullable supaya data lama (dari sebelum aturan ini) tidak rusak, tapi Pengadaan baru wajib pilih vendor. |
| **Barang Pengadaan** (`purchase_realizations`) | `RealizationController` | **Item di dalam Pengadaan** — WAJIB terikat ke satu `procurement_batch_id` (tidak boleh berdiri sendiri lagi). Finalisasi → jadi `Asset` resmi (kode + QR otomatis). |
| Anggaran | `BudgetController` | 2 lapis: Fakultas (pagu total) → Prodi (alokasi). Realisasi dihitung dari `assets.acquisition_value` + `purchase_realizations` yang `belum_final` |
| BAST | `HandoverReportController` | Berbasis **unit** (bukan per-realisasi) — bisa gabung banyak aset dari realisasi berbeda. Cetak F4, kop surat bisa upload gambar (di `/settings`) |
| Kode BMN | `BmnCodeController` | Master kode+nama, dipakai di `<datalist>` form aset |
| User | `UserController` | Soft-disable via `is_active`, bukan hard delete |
| Pengaturan | `SettingController` | Key-value generic di tabel `settings`, dipakai untuk kop surat BAST |
| Pengumuman | `AnnouncementController` | CRUD admin (`is_published` toggle, soft delete) + tampil di halaman depan publik & `/pengumuman`. Tidak ada editor rich-text — isi apa adanya (`white-space: pre-line` di tampilan), ganti baris di textarea langsung kepakai. |

### Alur Bisnis Pengadaan (PENTING)
Buat Pengadaan (pilih vendor, wajib)
Tambah Barang ke Pengadaan itu (procurement_batch_id wajib diisi)
Finalisasi barang → jadi Asset (kode & QR auto), vendor diambil dari Pengadaan induk
Buat BAST per unit (bisa gabung aset dari beberapa Pengadaan sekaligus)

Jangan bikin ulang field vendor di level barang — itu SENGAJA dihapus dari form, vendor cuma ada di level Pengadaan supaya tidak dobel.

## Konvensi Kode

- **Soft delete** dipakai di: `Unit`, `Location`, `AssetCategory`, `Vendor`, `BmnCodeReference` — supaya hapus dari UI tidak permanen. `Asset` juga soft delete.
- **Kode otomatis**: `Asset::generateAssetCode()`, `Unit::generateCode()`, `HandoverReport::generateNomor()` — pola serupa, jangan bikin ulang, ikuti polanya kalau butuh kode baru di modul lain.
- **Harga**: form selalu minta **harga satuan**, total dihitung otomatis (vanilla JS live preview + dihitung ulang di server saat validasi, jangan percaya angka dari client).
- **File upload**: pakai `Storage::disk('public')`, butuh `php artisan storage:link` di server. Folder: `pengajuan/` (bukti pengajuan), `bast/` (scan BAST), `settings/` (kop surat).
- **Import Excel**: pakai trait `App\Concerns\ImportsSpreadsheet` (baca xlsx/xls/csv otomatis lewat PhpSpreadsheet).
- **Rute publik tanpa login**: hanya dua — `/ruangan/{location}` dan `/aset/{asset:qr_code}` (dibuka dari hasil scan QR sticker/DBR). Keduanya di luar middleware `auth`, didefinisikan sebelum grup `Route::middleware('auth')` di `routes/web.php`. Route model binding aset sengaja pakai kolom `qr_code` (bukan `id`) supaya URL tidak gampang ditebak — ikuti pola ini kalau nambah halaman publik baru, jangan pakai `id` auto-increment mentah.
- **Null-safe operator**: HATI-HATI kalau bikin form yang dipakai bareng create+edit (`$model` bisa `null` saat create) — pakai `$model?->relasi?->format(...)`, BUKAN `$model->relasi?->format(...)` (baru cukup error kalau `$model`-nya sendiri null).

## Gotcha yang Sudah Pernah Kejadian (jangan diulang)

- **MySQL reserved word**: kolom bernama `condition` harus di-quote (`->select('condition')` bukan `->selectRaw('condition, ...')` mentah).
- **Laravel pagination default** pakai markup Tailwind (duplikasi mobile/desktop) — sudah diganti custom view `resources/views/vendor/pagination/custom.blade.php`, didaftarkan lewat `Paginator::defaultView()` di `AppServiceProvider`.
- **Locale tanggal**: `config/app.php` / `.env` harus `APP_LOCALE=id` supaya `Carbon::translatedFormat()` keluar bahasa Indonesia. **Sudah pernah kejadian**: `.env` sempat punya dua baris `APP_LOCALE` (satu `en`, satu `id`) — nilai yang kepakai adalah yang PERTAMA muncul di file (perilaku phpdotenv), jadi baris kedua diam-diam tidak berlaku. Kalau curiga locale tidak jalan, cek dulu ada duplikat key atau tidak: `grep -n APP_LOCALE .env`.
- **`APP_ENV`/`APP_DEBUG` di server sempat kebawa nilai default lokal** (`local`/`true`) padahal domain sudah live publik — bahaya karena `APP_DEBUG=true` membocorkan stack trace. Sudah diperbaiki ke `production`/`false`, tapi cek ulang tiap kali `.env` server disentuh manual.
- **RedirectResponse tidak punya method `when()`** — itu method Query Builder/Collection, jangan dipakai di redirect chain.
- **`Cache::remember` JANGAN menyimpan Eloquent Collection** — di shared hosting, cache dibaca lewat `unserialize()` sebelum class `Illuminate\Support\Collection` ter-load → error "call a method on an incomplete object" → HTTP 500 (pernah terjadi di halaman Daftar Pengadaan setelah statistik agregat di-cache). Kalau hasilnya kumpulan data agregat, konversi ke **plain array** (`->values()->all()` / `->toArray()`) SEBELUM di-cache.
- **Hapus data via database langsung (phpMyAdmin) melewati semua safeguard aplikasi** (termasuk soft delete) — SELALU lewat halaman aplikasi, jangan pernah hapus manual di DB kecuali darurat.

## Yang Belum Dikerjakan / Ide Lanjutan

- Modul Penerimaan Barang (checklist fisik sebelum finalisasi)
- Cetak dokumen pengadaan resmi (surat pesanan/kontrak) — field `nomor_dokumen` di `procurement_batches` sudah disiapkan buat ini
- Role lebih granular (misal "admin_gudang" yang cuma bisa kelola aset, tidak bisa hapus permanen)
- Foto aset (upload gambar per aset, belum ada)
- Garansi aset (belum ada kolom)
- Isi konten halaman `/dokumentasi` (masih placeholder "segera hadir", belum ada panduan per-role beneran)

## Changelog / Riwayat Perubahan

Catatan kerja dari sesi review & perbaikan aplikasi (lihat juga `CODE_REVIEW.md` untuk temuan lengkapnya).

### 2026-08-28 — Review kode menyeluruh + perbaikan kelompok A–E, menu, & bug
Dimulai dari review seluruh aplikasi (controller, model, routes, migration, test) lewat workflow paralel → laporan `CODE_REVIEW.md`. Lalu diterapkan perbaikan per kelompok:

- **Kelompok A — Integritas data (race condition & transaksi)**
  - `RealizationController::finalize` dibungkus `DB::transaction()` → tidak ada aset parsial/duplikat saat gagal.
  - Trait baru `App\Concerns\RetriesUniqueConstraint` — retry otomatis saat kena konflik UNIQUE (kode aset, nomor BAST) untuk menangani race dua proses bersamaan. Dipakai di `finalize` & `HandoverReportController::store`.
  - `Unit::generateCode()` kini pakai `withTrashed()` agar tidak bentrok kode dengan unit soft-deleted.
- **Kelompok B — Keamanan**
  - Throttle login (`throttle:5,1`) di rute `login.authenticate`.
  - Google self-registration dibatasi ke domain `@uns.ac.id` / `@student.uns.ac.id` + wajib `email_verified`.
  - Middleware baru `EnsureUserIsActive` — cek `is_active`/`is_approved` per-request (didaftarkan di grup `web`), bukan cuma saat login.
  - `User::$fillable` dipersempit (hapus `role`, `is_active`, `is_approved`, `unit_id` — kolom sensitif kini di-set eksplisit di `UserController`/`GoogleAuthController`/seeder). Hapus duplikat attribute `#[Fillable]`.
- **Kelompok C — Kinerja**
  - Hilangkan N+1 di `AssetController::preview` (pre-load kategori/unit/lokasi ke koleksi).
  - Paginate detail aset di `Unit`, `Location`, `BmnCode` (`show` → `paginate` + `->links()` di view).
  - Cache statistik agregat (5 menit) di `ProcurementBatchController::dashboardStats` & `BmnCodeController::index`, + flush saat mutasi.
  - Hapus 2 query SUM redundan di `RealizationController::index` (hitung dari koleksi yang sudah dimuat).
- **Kelompok D — Robustness import**
  - `ImportsSpreadsheet`: `toArray(..., calculateFormulas=false)` (cegah formula injection & SSRF), sanitasi sel berawalan `=+-@`, bungkus baca file dalam try/catch → `RuntimeException` ramah. Pemanggil (`AssetController::preview`, `BmnCodeController::import`) menangkap & redirect dengan pesan.
  - `nilai_perolehan` non-angka dijadikan error baris (tidak lagi diam-diam jadi 0).
- **Kelompok E — Otorisasi bisnis**
  - Kebijakan: **hanya admin yang mengelola; role lain hanya melihat** (kepala_unit/staff scoped ke unit sendiri, pimpinan melihat semua).
  - Trait `App\Concerns\RestrictsByRole`; rute dipecah lihat vs tulis di `routes/web.php`; scoping di controller; UI read-only (12 view) — tombol kelola disembunyikan utk non-admin. Helper `User::isAdmin()`.
  - Hapus route `announcements.show` yang menunjuk ke method tak ada.
- **Menu & navigasi**
  - Sidebar dikelompokkan menjadi seksi: **Manajemen Aset** (Aset, Lokasi, BAST, Kategori*, Kode BMN*), **Pengadaan** (Pengajuan, Pengadaan, Barang Pengadaan, Anggaran, Vendor*), **Referensi** (Program Studi, Pengumuman), **Administrasi*** (Pengguna, Pengaturan). Item `*` khusus admin. Menu Pengajuan dipindah ke seksi Pengadaan. Modul read-only kini tampil untuk non-admin. Label grup disembunyikan di mobile.
- **Bug fix** — Error 500 di `/procurement-batches`: statistik `per_category` di-cache sebagai Eloquent Collection → "incomplete object" di shared hosting. Diperbaiki dengan menyimpan sebagai plain array + sesuaikan view (`! empty(...)`).

### Test otomatis (ditambahkan)
- `tests/Unit/RetriesUniqueConstraintTest.php` — trait retry (retry, rethrow non-unique, menyerah setelah max).
- `tests/Unit/ImportsSpreadsheetTest.php` — sanitizer sel + exception ramah utk file rusak.
