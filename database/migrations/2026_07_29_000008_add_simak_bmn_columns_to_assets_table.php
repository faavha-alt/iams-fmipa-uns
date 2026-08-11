<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Identitas resmi BMN (Barang Milik Negara), dipakai untuk koneksi ke SIMAK BMN
            $table->string('simak_kode_barang')->nullable()->after('qr_code'); // kode klasifikasi BMN, contoh: 3.05.02.01.001
            $table->unsignedInteger('simak_nup')->nullable()->after('simak_kode_barang'); // Nomor Urut Pendaftaran
            $table->string('simak_kode_lokasi')->nullable()->after('simak_nup'); // kode lokasi/satker versi DJKN, beda dari location_id internal
            $table->unsignedSmallInteger('simak_tahun_perolehan')->nullable()->after('simak_kode_lokasi'); // tahun perolehan menurut pencatatan BMN

            // Status sinkronisasi ke sistem SIMAK BMN (metode koneksi menyusul: API, ekspor/impor ADK, dll)
            $table->enum('simak_sync_status', ['belum_disinkron', 'terkirim', 'tersinkron', 'gagal'])
                ->default('belum_disinkron')->after('simak_tahun_perolehan');
            $table->timestamp('simak_last_synced_at')->nullable()->after('simak_sync_status');
            $table->text('simak_sync_notes')->nullable()->after('simak_last_synced_at'); // catatan/error terakhir

            $table->index(['simak_kode_barang', 'simak_tahun_perolehan', 'simak_nup']);
            $table->index('simak_sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'simak_kode_barang',
                'simak_nup',
                'simak_kode_lokasi',
                'simak_tahun_perolehan',
                'simak_sync_status',
                'simak_last_synced_at',
                'simak_sync_notes',
            ]);
        });
    }
};
