<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('id')->constrained('units')->nullOnDelete();
            $table->enum('role', ['admin', 'kepala_unit', 'staff', 'pimpinan'])
                ->default('staff')->after('unit_id');
            $table->string('nip')->nullable()->unique()->after('role'); // Nomor Induk Pegawai
            $table->string('phone')->nullable()->after('nip');
            $table->boolean('is_active')->default(true)->after('phone');
        });

        // Tambahkan foreign key head_user_id di tabel units sekarang users sudah lengkap
        Schema::table('units', function (Blueprint $table) {
            $table->foreign('head_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['head_user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'role', 'nip', 'phone', 'is_active']);
        });
    }
};
