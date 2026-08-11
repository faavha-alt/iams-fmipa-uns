<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('code')->unique(); // contoh: FMIPA, MAT, LAB-FIS
            $table->string('name');
            $table->enum('type', ['fakultas', 'departemen', 'program_studi', 'laboratorium', 'unit_kerja'])
                ->default('unit_kerja');
            $table->foreignId('head_user_id')->nullable(); // kepala unit, FK ditambahkan setelah tabel users siap
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
