<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->string('unit_satuan')->nullable()->after('name'); // Unit, Buah, Paket, Set, Meter, dll
            $table->text('specification')->nullable()->after('unit_satuan'); // spesifikasi standar/detail kategori ini
        });
    }

    public function down(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn(['unit_satuan', 'specification']);
        });
    }
};
