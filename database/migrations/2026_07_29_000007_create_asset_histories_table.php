<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();

            $table->enum('event_type', [
                'perpindahan_lokasi',
                'perpindahan_pengguna',
                'perubahan_kondisi',
                'perbaikan',
                'penghapusan',
                'lainnya',
            ]);

            $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('condition_before')->nullable();
            $table->string('condition_after')->nullable();

            $table->text('description')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete(); // siapa yang mencatat
            $table->date('event_date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_histories');
    }
};
