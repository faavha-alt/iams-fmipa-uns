<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handover_reports', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_bast')->unique();
            $table->foreignId('purchase_realization_id')->constrained('purchase_realizations')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();

            $table->date('tanggal_serah_terima');
            $table->string('pihak_menyerahkan'); // nama+jabatan pihak pengadaan
            $table->string('pihak_menerima'); // nama+jabatan pihak unit (mis. Kepala Prodi X)
            $table->text('catatan')->nullable();

            $table->string('dokumen_scan')->nullable(); // hasil scan BAST yang sudah ditandatangani
            $table->enum('status', ['draft', 'final'])->default('draft');

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handover_reports');
    }
};
