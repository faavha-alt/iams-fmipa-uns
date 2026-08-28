# Progress — iams-fmipa-uns

Dibuat: 2026-08-26

## Tasks

<!-- Format checklist standar: "- [ ] belum" / "- [x] selesai". Dibaca otomatis oleh Project Dashboard (http://100.94.175.72:4400/) untuk menghitung progres. -->

### Sudah selesai (berdasarkan kode & git log)
- [x] Setup dasar Laravel 13 + PHP 8.4, MySQL, tanpa Livewire/Tailwind/Vite (CSS custom di `public/css/frontend.css`)
- [x] Modul Aset (`AssetController`) — CRUD, import Excel massal dengan preview cross-check + fuzzy vendor matching, cetak sticker F4 dengan QR
- [x] Modul Kategori Aset, Unit/Prodi, Lokasi (dengan detail rekap kategori/kondisi + cetak DBR), Vendor
- [x] Modul Pengajuan Aset (`AssetRequestController`) — alur approve/reject dari pengaju ke admin
- [x] Modul Pengadaan (`ProcurementBatchController`) + Barang Pengadaan (`RealizationController`) — alur Pengadaan → Barang → Finalisasi jadi Asset resmi
- [x] Modul Anggaran 2 lapis (Fakultas → Prodi) dengan realisasi otomatis
- [x] Modul BAST (`HandoverReportController`) berbasis unit, cetak F4, kop surat upload gambar
- [x] Modul Kode BMN (master + statistik + halaman detail per kode)
- [x] Modul User dengan soft-disable (`is_active`) dan approval Google login (`is_approved`)
- [x] Modul Pengaturan (key-value generic) dan Pengumuman (CRUD + tampil publik)
- [x] Login email+password dan Google OAuth (Socialite) dengan alur approval admin untuk user baru
- [x] Halaman depan publik (beranda info, `/pengumuman`, halaman publik lokasi & aset via QR scan tanpa login)
- [x] Optimasi performa: fix N+1 query, cache badge sidebar, batch query di `BudgetController`, index kolom tanggal (commit `90414a2`)
- [x] Script deploy otomatis (`deploy.sh`) — push, SSH, composer install, migrate, cache clear

### Belum selesai / perlu dikerjakan
- [ ] Isi konten halaman `/dokumentasi` — masih placeholder statis "Dokumentasi Segera Hadir" (`resources/views/public/documentation.blade.php`), belum ada panduan per-role
- [ ] Integrasi sinkronisasi SIMAK BMN — skema DB & model sudah ada (`simak_*` kolom di tabel `assets`, tabel `simak_bmn_sync_logs`, model `SimakBmnSyncLog`), tapi TIDAK ada controller/service/job yang benar-benar melakukan sinkronisasi; komentar migrasi eksplisit bilang "metode koneksi menyusul: API, ekspor/impor ADK, dll" — baru data model, logikanya belum dibuat
- [ ] Kejelasan role `pimpinan` — ada di enum `users.role` dan divalidasi di `UserController`, tapi tidak ada middleware/otorisasi khusus untuknya di kode; perlakuannya masih sama seperti staff biasa (perlu konfirmasi user apakah ini disengaja atau memang belum dikerjakan)
- [ ] Modul Penerimaan Barang (checklist fisik sebelum finalisasi Pengadaan) — belum ada model/controller/migrasi sama sekali
- [ ] Cetak dokumen pengadaan resmi (surat pesanan/kontrak) — field `nomor_dokumen` di `procurement_batches` sudah ada (nullable, diisi manual), tapi belum ada generator/cetak dokumen resmi
- [ ] Foto aset (upload gambar per aset) — belum ada kolom/fitur sama sekali di `Asset`/migrasi
- [ ] Garansi aset — belum ada kolom terkait garansi di skema `assets`
- [ ] Role lebih granular (mis. "admin_gudang" yang hanya kelola aset tanpa hapus permanen) — belum ada, saat ini hanya `admin` (akses penuh) vs non-admin
- [ ] Test otomatis — `tests/Feature/ExampleTest.php` dan `tests/Unit/ExampleTest.php` masih contoh bawaan skeleton Laravel, belum ada test nyata untuk modul apa pun (Aset, Pengadaan, BAST, dll)
- [ ] (perlu konfirmasi user) Status produksi terkini di server `aset.mipa.uns.ac.id` — apakah sudah live dipakai user asli, atau masih tahap internal/testing

## Log sesi

### 2026-08-26
- Ditambahkan ke Project Workspace, dokumentasi tracking diisi berdasarkan eksplorasi kode existing (bukan dari rencana asli developer).
