<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $table->string('code')->unique(); // contoh: ELK-KOM (Elektronik-Komputer)
            $table->string('name');
            $table->unsignedTinyInteger('useful_life_years')->nullable(); // masa manfaat, untuk penyusutan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_categories');
    }
};
