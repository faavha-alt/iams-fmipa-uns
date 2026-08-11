<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->decimal('estimated_cost', 15, 2)->nullable()->after('quantity');
        });

        Schema::table('assets', function (Blueprint $table) {
            // Kalau aset ini adalah hasil realisasi dari sebuah pengajuan, tautkan di sini.
            // Nullable karena aset bisa juga dicatat manual tanpa melalui alur pengajuan.
            $table->foreignId('asset_request_id')->nullable()->after('id')
                ->constrained('asset_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['asset_request_id']);
            $table->dropColumn('asset_request_id');
        });

        Schema::table('asset_requests', function (Blueprint $table) {
            $table->dropColumn('estimated_cost');
        });
    }
};
