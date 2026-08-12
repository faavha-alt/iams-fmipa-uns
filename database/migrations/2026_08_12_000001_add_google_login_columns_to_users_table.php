<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('password');
            // User yang baru masuk lewat Google butuh admin memberi role & unit dulu sebelum bisa akses apa pun.
            // Terpisah dari is_active (itu untuk admin sengaja menonaktifkan akun yang sudah disetujui).
            $table->boolean('is_approved')->default(true)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'is_approved']);
        });
    }
};
