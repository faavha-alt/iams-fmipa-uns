<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bmn_code_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('bmn_code_references')->nullOnDelete();
            $table->string('kode')->unique(); // kode resmi BMN, contoh: 3.05.02.01.001
            $table->string('nama'); // nama jenis barang resmi sesuai SIMAK BMN
            $table->unsignedTinyInteger('level')->default(1); // 1=Golongan .. 5=Sub-sub Kelompok
            $table->string('satuan')->nullable(); // satuan standar BMN kalau ada
            $table->unsignedTinyInteger('masa_manfaat_tahun')->nullable(); // masa manfaat standar per Permenkeu
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bmn_code_references');
    }
};
