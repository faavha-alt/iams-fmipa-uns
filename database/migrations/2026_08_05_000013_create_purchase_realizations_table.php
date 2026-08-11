<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_realizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('asset_category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();

            $table->string('item_name');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('cost', 15, 2); // total biaya pembelian (semua unit)
            $table->date('purchase_date');
            $table->text('notes')->nullable();

            // belum_final: sudah dibeli & dihitung realisasi anggaran, tapi belum jadi record Asset resmi.
            // sudah_final: sudah dikonversi jadi Asset (lihat relasi assets() di model).
            $table->enum('status', ['belum_final', 'sudah_final'])->default('belum_final');

            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['unit_id', 'status']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('purchase_realization_id')->nullable()->after('asset_request_id')
                ->constrained('purchase_realizations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['purchase_realization_id']);
            $table->dropColumn('purchase_realization_id');
        });

        Schema::dropIfExists('purchase_realizations');
    }
};
