<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simak_bmn_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();

            $table->enum('direction', ['push', 'pull'])->default('push'); // push: kirim data ke SIMAK BMN, pull: tarik data dari sana
            $table->string('endpoint')->nullable(); // diisi setelah metode integrasi (API/ADK/dll) ditentukan
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->unsignedSmallInteger('http_status_code')->nullable();

            $table->enum('status', ['pending', 'berhasil', 'gagal'])->default('pending');
            $table->text('error_message')->nullable();

            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete(); // null jika otomatis/terjadwal

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simak_bmn_sync_logs');
    }
};
