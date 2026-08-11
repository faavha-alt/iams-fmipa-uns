<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            // Identitas aset
            $table->string('asset_code')->unique(); // kode aset internal, contoh: FMIPA-2026-00001
            $table->string('qr_code')->unique(); // string unik yang di-encode ke QR Code
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();

            // Klasifikasi & kepemilikan
            $table->foreignId('asset_category_id')->constrained('asset_categories')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete(); // unit pemilik/pengelola
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete(); // lokasi saat ini

            // Penanggung jawab & pengguna
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('current_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Pengadaan & nilai
            $table->date('acquisition_date')->nullable();
            $table->enum('acquisition_source', ['pengadaan', 'hibah', 'bantuan', 'lainnya'])->default('pengadaan');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->decimal('acquisition_value', 15, 2)->default(0); // nilai perolehan
            $table->decimal('book_value', 15, 2)->nullable(); // nilai buku setelah penyusutan

            // Kondisi & status
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])->default('baik');
            $table->enum('status', ['aktif', 'dalam_perbaikan', 'dipinjamkan', 'dihapuskan'])->default('aktif');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_id', 'status']);
            $table->index(['condition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
