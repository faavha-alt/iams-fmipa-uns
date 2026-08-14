<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->index('acquisition_date');
        });

        Schema::table('purchase_realizations', function (Blueprint $table) {
            $table->index('purchase_date');
        });

        Schema::table('asset_requests', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['acquisition_date']);
        });

        Schema::table('purchase_realizations', function (Blueprint $table) {
            $table->dropIndex(['purchase_date']);
        });

        Schema::table('asset_requests', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
