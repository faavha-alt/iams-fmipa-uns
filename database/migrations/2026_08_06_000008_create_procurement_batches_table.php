<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_batches', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // contoh: Pengadaan Periode 1 2026
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('nomor_dokumen')->nullable(); // buat nomor surat pengadaan nanti, kosong dulu
            $table->enum('status', ['berjalan', 'selesai'])->default('berjalan');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::table('purchase_realizations', function (Blueprint $table) {
            $table->foreignId('procurement_batch_id')->nullable()->after('unit_id')
                ->constrained('procurement_batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_realizations', function (Blueprint $table) {
            $table->dropForeign(['procurement_batch_id']);
            $table->dropColumn('procurement_batch_id');
        });

        Schema::dropIfExists('procurement_batches');
    }
};
