<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->unsignedSmallInteger('fiscal_year')->nullable()->after('unit_id');
            $table->string('purchase_link')->nullable()->after('reason');
            $table->string('supporting_image')->nullable()->after('purchase_link');
        });
    }

    public function down(): void
    {
        Schema::table('asset_requests', function (Blueprint $table) {
            $table->dropColumn(['fiscal_year', 'purchase_link', 'supporting_image']);
        });
    }
};
