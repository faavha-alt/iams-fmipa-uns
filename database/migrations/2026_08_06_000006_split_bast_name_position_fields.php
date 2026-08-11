<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('handover_reports', function (Blueprint $table) {
            $table->dropColumn(['pihak_menyerahkan', 'pihak_menerima']);
            $table->string('nama_menyerahkan')->nullable()->after('tanggal_serah_terima');
            $table->string('jabatan_menyerahkan')->nullable()->after('nama_menyerahkan');
            $table->string('nama_menerima')->nullable()->after('jabatan_menyerahkan');
            $table->string('jabatan_menerima')->nullable()->after('nama_menerima');
        });
    }

    public function down(): void
    {
        Schema::table('handover_reports', function (Blueprint $table) {
            $table->dropColumn(['nama_menyerahkan', 'jabatan_menyerahkan', 'nama_menerima', 'jabatan_menerima']);
            $table->string('pihak_menyerahkan')->nullable();
            $table->string('pihak_menerima')->nullable();
        });
    }
};
