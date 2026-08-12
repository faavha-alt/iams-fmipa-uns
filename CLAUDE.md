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
- **admin**: akses penuh ke semua modul
- **kepala_unit / staff**: pengaju dari unit masing-masing — cuma lihat data unit sendiri, bisa ajukan permintaan aset
- **pimpinan**: ada di enum tapi belum ada logika/otorisasi khusus di kode — perlakuannya masih sama seperti non-admin biasa (perlu diperjelas kalau mau dipakai beneran)
- Middleware custom: `App\Http\Middleware\EnsureUserIsAdmin` (alias `admin`, cek `role === 'admin'`)

### Modul yang sudah ada
| Modul | Controller | Keterangan |
|---|---|---|
| Dashboard | `DashboardController` | Statistik ringkas, scoped per role |
| Aset | `AssetController` | CRUD + import Excel massal |
| Kategori Aset | `CategoryController` | Hierarkis (`parent_id`), ada `unit_satuan` |
| Unit/Prodi | `UnitController` | Hierarkis, kode auto-generate |
| Lokasi | `LocationController` | Terikat ke unit |
| Vendor | `VendorController` | Soft delete |
| Pengajuan Aset | `AssetRequestController` | Alur approve/reject dari pengaju ke admin, wajib upload gambar + link referensi |
| **Pengadaan** (`procurement_batches`) | `ProcurementBatchController` | **Header transaksi** — vendor, tanggal, nomor dokumen. Vendor **wajib** di validasi (`required`). Kolom `vendor_id` di DB tetap nullable supaya data lama (dari sebelum aturan ini) tidak rusak, tapi Pengadaan baru wajib pilih vendor. |
| **Barang Pengadaan** (`purchase_realizations`) | `RealizationController` | **Item di dalam Pengadaan** — WAJIB terikat ke satu `procurement_batch_id` (tidak boleh berdiri sendiri lagi). Finalisasi → jadi `Asset` resmi (kode + QR otomatis). |
| Anggaran | `BudgetController` | 2 lapis: Fakultas (pagu total) → Prodi (alokasi). Realisasi dihitung dari `assets.acquisition_value` + `purchase_realizations` yang `belum_final` |
| BAST | `HandoverReportController` | Berbasis **unit** (bukan per-realisasi) — bisa gabung banyak aset dari realisasi berbeda. Cetak F4, kop surat bisa upload gambar (di `/settings`) |
| Kode BMN | `BmnCodeController` | Master kode+nama, dipakai di `<datalist>` form aset |
| User | `UserController` | Soft-disable via `is_active`, bukan hard delete |
| Pengaturan | `SettingController` | Key-value generic di tabel `settings`, dipakai untuk kop surat BAST |

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
- **Null-safe operator**: HATI-HATI kalau bikin form yang dipakai bareng create+edit (`$model` bisa `null` saat create) — pakai `$model?->relasi?->format(...)`, BUKAN `$model->relasi?->format(...)` (baru cukup error kalau `$model`-nya sendiri null).

## Gotcha yang Sudah Pernah Kejadian (jangan diulang)

- **MySQL reserved word**: kolom bernama `condition` harus di-quote (`->select('condition')` bukan `->selectRaw('condition, ...')` mentah).
- **Laravel pagination default** pakai markup Tailwind (duplikasi mobile/desktop) — sudah diganti custom view `resources/views/vendor/pagination/custom.blade.php`, didaftarkan lewat `Paginator::defaultView()` di `AppServiceProvider`.
- **Locale tanggal**: `config/app.php` / `.env` harus `APP_LOCALE=id` supaya `Carbon::translatedFormat()` keluar bahasa Indonesia. **Sudah pernah kejadian**: `.env` sempat punya dua baris `APP_LOCALE` (satu `en`, satu `id`) — nilai yang kepakai adalah yang PERTAMA muncul di file (perilaku phpdotenv), jadi baris kedua diam-diam tidak berlaku. Kalau curiga locale tidak jalan, cek dulu ada duplikat key atau tidak: `grep -n APP_LOCALE .env`.
- **`APP_ENV`/`APP_DEBUG` di server sempat kebawa nilai default lokal** (`local`/`true`) padahal domain sudah live publik — bahaya karena `APP_DEBUG=true` membocorkan stack trace. Sudah diperbaiki ke `production`/`false`, tapi cek ulang tiap kali `.env` server disentuh manual.
- **RedirectResponse tidak punya method `when()`** — itu method Query Builder/Collection, jangan dipakai di redirect chain.
- **Hapus data via database langsung (phpMyAdmin) melewati semua safeguard aplikasi** (termasuk soft delete) — SELALU lewat halaman aplikasi, jangan pernah hapus manual di DB kecuali darurat.

## Yang Belum Dikerjakan / Ide Lanjutan

- Modul Penerimaan Barang (checklist fisik sebelum finalisasi)
- Cetak dokumen pengadaan resmi (surat pesanan/kontrak) — field `nomor_dokumen` di `procurement_batches` sudah disiapkan buat ini
- Role lebih granular (misal "admin_gudang" yang cuma bisa kelola aset, tidak bisa hapus permanen)
- Foto aset (upload gambar per aset, belum ada)
- Garansi aset (belum ada kolom)
