<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('asset_category_id')->nullable()->constrained('asset_categories')->nullOnDelete();

            $table->string('item_name'); // nama barang yang diajukan
            $table->unsignedInteger('quantity')->default(1);
            $table->text('reason'); // alasan/justifikasi pengajuan

            $table->enum('status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            $table->timestamps();

            $table->index(['unit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_requests');
    }
};
