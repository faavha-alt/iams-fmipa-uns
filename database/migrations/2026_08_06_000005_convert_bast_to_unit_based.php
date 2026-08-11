<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('handover_reports', function (Blueprint $table) {
            $table->dropForeign(['purchase_realization_id']);
            $table->dropColumn('purchase_realization_id');
        });

        Schema::create('handover_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('handover_report_id')->constrained('handover_reports')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['handover_report_id', 'asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handover_report_items');

        Schema::table('handover_reports', function (Blueprint $table) {
            $table->foreignId('purchase_realization_id')->nullable()->constrained('purchase_realizations')->cascadeOnDelete();
        });
    }
};
