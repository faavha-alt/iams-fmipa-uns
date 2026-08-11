<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('asset_categories', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
