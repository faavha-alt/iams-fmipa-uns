# Code Review — IAMS FMIPA UNS

Tanggal: (direview via workflow paralel 5 modul)

**Cakupan:** semua controller (18), model (17), `routes/web.php`, `AppServiceProvider`, `Concerns/ImportsSpreadsheet`, semua migration, dan test.
**Tidak** direview ulang secara mendalam: isi view (kecuali menu Pengadaan), CSS/JS frontend, dan seeder (tidak ada).

## Kesimpulan umum

Kualitas pemrograman **baik dan terawat** — query terparameterisasi (aman SQL injection), mass-assignment terlindungi (`$fillable`), otorisasi rute dikurung middleware `admin` (`EnsureUserIsAdmin`), validasi form terpusat. **Tidak ada blocker kritis.** Ada beberapa titik rapuh, terutama **integritas data (transaksi & race condition)**, **keamanan**, dan **kinerja**.

---

## 🔴 Blocker
Tidak ada.

## 🟡 Penting

### A. Integritas data — transaksi & race condition
1. `RealizationController.php:147-165` — `finalize()` buat N aset dalam loop + update status **tanpa `DB::transaction`**. Gagal parsial → aset duplikat saat retry → anggaran dobel. → Bungkus `DB::transaction()`, idealnya insert massal.
2. `Asset.php:51-61`, `HandoverReport.php:45-57` — `generateAssetCode()`/`generateNomor()` pakai `count()+1` tidak atomik terhadap kolom `UNIQUE` → race saat paralel → exception 500. → Transaksi + `lockForUpdate` / retry saat `QueryException` duplicate.
3. `Unit.php:62-83` — `generateCode()` **mengabaikan unit soft-deleted** (beda dengan `Asset` yang `withTrashed()`) → bisa bentrok kode `UNIQUE`. → Pakai `withTrashed()`.

### B. Keamanan
4. `LoginController.php:18-46` — login **tanpa rate-limiting** → rawan brute-force akun admin. → `throttle:5,1`.
5. `GoogleAuthController.php:36-46` — self-registration Google **terbuka semua domain email** & tanpa cek `email_verified`. → Batasi domain institusi (`@uns.ac.id`), tolak email tak terverifikasi.
6. `AppServiceProvider.php:27` — `is_active`/`is_approved` hanya dicek saat login, **bukan per-request** → user dinonaktifkan tetap bisa pakai sesi lama. → Cek di middleware `auth`.
7. `User.php:25-29` — `$fillable` memuat kolom sensitif `role, is_active, is_approved, unit_id` → pintu privilege-escalation. → Keluarkan dari `$fillable`, set eksplisit.

### C. Kinerja
8. `AssetController.php:258-296` — **N+1** di loop import (kategori/unit/lokasi per baris). → Pre-load ke map.
9. `UnitController.php:81-94`, `LocationController.php:108-121`, `BmnCodeController.php:98-110` — `show()` muat **semua aset tanpa pagination**. → `paginate(20)`.
10. `BudgetController.php:44-83`, `ProcurementBatchController.php:37-76`, `BmnCodeController.php:45-49` — statistik agregat (7–8 query) dihitung ulang tiap muat halaman. → Cache per hari.
11. `RealizationController.php:22-44` — `index()` pakai `get()` tak terbatas + query SUM redundan. → `paginate()` + jumlah dari koleksi.

### D. Robustness import & data
12. `ImportsSpreadsheet.php:22` — `toArray(..., true, ...)` **mengeksekusi formula sel** → risiko formula/CSV injection & SSRF. → `calculateFormulas=false`, sanitasi `=+-@`.
13. `ImportsSpreadsheet.php:15` — baca file **tanpa try/catch** (auto-detect dari isi) → file rusak → HTTP 500. → Wrap `try/catch`, validasi `mimetypes`.
14. `AssetController.php:348` — `nilai_perolehan` non-angka **diam-diam jadi 0** tanpa peringatan. → Jadikan status error.

### E. Otorisasi bisnis (perlu konfirmasi)
15. `routes/web.php:52` + `DashboardController.php:18-19` — semua operasi budget/realisasi/pengadaan **hanya admin**; `kepala_unit`/`pimpinan` tak bisa mengelola. `AssetRequestController::store` **tidak menegakkan pagu/budget**. → Konfirmasi apakah pembatasan admin disengaja.

## ⚪ Nit (opsional)
- `User.php:13,25` — definisi `$fillable` duplikat (attribute `#[Fillable]` 3 kolom ditimpa property 11 kolom).
- `HandoverReportController.php:119-133` — `uploadScan` menimpa file lama tanpa hapus file di disk.
- `RealizationController.php:132-139` — `location_id` tidak dibatasi ke unit realisasi.
- `RealizationController.php:141` — `acquisition_value = cost/quantity` → selisih sen.
- `AssetRequestController.php:77-80` — `estimated_unit_price=0` dianggap "kosong" → `estimated_cost` null.
- `ProcurementBatchController.php:80,95` — `\App\Models\Vendor` inline vs `use-import`.
- `SettingController.php:30-35` — hapus logo tidak menghapus file lama.
- `UserController.php:92-101` — `toggleActive` bisa menonaktifkan admin terakhir.
- `routes/web.php:33` — `/ruangan/{location}` pakai `id` (enumerasi) vs `/aset/{asset:qr_code}`.
- `tests/*` — masih boilerplate bawaan, belum ada test nyata.

## Rekomendasi prioritas
1. **Integritas data:** transaksi di `finalize()`, tangani race generator kode (A1–A3).
2. **Keamanan:** throttle login + batasi domain Google (B4–B5).
3. **Kinerja:** paginate semua `show()` + hilangkan N+1 import (C8–C11).
4. **Test otomatis** untuk modul inti (aset, pengadaan, BAST, otorisasi rute).
