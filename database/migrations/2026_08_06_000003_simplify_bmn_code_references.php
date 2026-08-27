<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bmn_code_references', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['level']); // index eksplisit dari migrasi 000002 — SQLite error kalau kolomnya di-drop duluan
            $table->dropColumn(['parent_id', 'level', 'satuan', 'masa_manfaat_tahun']);
        });
    }

    public function down(): void
    {
        Schema::table('bmn_code_references', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('bmn_code_references')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(1);
            $table->string('satuan')->nullable();
            $table->unsignedTinyInteger('masa_manfaat_tahun')->nullable();
        });
    }
};
