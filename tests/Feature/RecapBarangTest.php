<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecapBarangTest extends TestCase
{
    use RefreshDatabase;

    public function test_recap_aggregates_all_item_names_for_selected_year(): void
    {
        $now = now();

        $userId = DB::table('users')->insertGetId([
            'name' => 'Admin Uji',
            'email' => 'admin-uji@example.test',
            'password' => bcrypt('secret'),
            'role' => 'admin',
            'is_active' => true,
            'is_approved' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $unitId = DB::table('units')->insertGetId([
            'code' => 'FMIPA',
            'name' => 'FMIPA',
            'type' => 'fakultas',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $batchId = DB::table('procurement_batches')->insertGetId([
            'nama' => 'Pengadaan Uji',
            'status' => 'berjalan',
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->realization($userId, $unitId, $batchId, 'Laptop', 2, 100, now()->startOfYear()->toDateString());
        $this->realization($userId, $unitId, $batchId, 'Laptop', 3, 150, now()->startOfYear()->addMonth()->toDateString());
        $this->realization($userId, $unitId, $batchId, 'Printer', 1, 999, now()->subYear()->startOfYear()->toDateString());

        $response = $this->actingAs(User::find($userId))
            ->get('/realizations/recap?year='.now()->year);

        $response->assertOk();
        $response->assertSee('Laptop');
        $response->assertDontSee('Printer'); // beda tahun, tidak ikut
        $response->assertSee('Rp 250');      // 100 + 150, digabung per nama barang
        $response->assertSee('5');           // 2 + 3 unit
    }

    public function test_recap_can_be_filtered_by_status(): void
    {
        [$userId, $unitId, $batchId] = $this->fixtures();

        $this->realization($userId, $unitId, $batchId, 'Laptop', 2, 100, now()->toDateString(), 'belum_final');
        $this->realization($userId, $unitId, $batchId, 'Printer', 1, 999, now()->toDateString(), 'sudah_final');

        $response = $this->actingAs(User::find($userId))
            ->get('/realizations/recap?year='.now()->year.'&status=belum_final');

        $response->assertOk();
        $response->assertSee('Laptop');
        $response->assertDontSee('Printer');
    }

    public function test_recap_export_downloads_excel(): void
    {
        [$userId, $unitId, $batchId] = $this->fixtures();

        $this->realization($userId, $unitId, $batchId, 'Laptop', 2, 100, now()->toDateString());

        $this->actingAs(User::find($userId))
            ->get('/realizations/recap/export?year='.now()->year)
            ->assertOk()
            ->assertDownload('rekap_barang_'.now()->year.'.xlsx');
    }

    /** Bikin user admin + unit + pengadaan, kembalikan [userId, unitId, batchId]. */
    private function fixtures(): array
    {
        $now = now();

        $userId = DB::table('users')->insertGetId([
            'name' => 'Admin Uji',
            'email' => 'admin-uji@example.test',
            'password' => bcrypt('secret'),
            'role' => 'admin',
            'is_active' => true,
            'is_approved' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $unitId = DB::table('units')->insertGetId([
            'code' => 'FMIPA',
            'name' => 'FMIPA',
            'type' => 'fakultas',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $batchId = DB::table('procurement_batches')->insertGetId([
            'nama' => 'Pengadaan Uji',
            'status' => 'berjalan',
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [$userId, $unitId, $batchId];
    }

    private function realization(int $userId, int $unitId, int $batchId, string $name, int $qty, float $cost, string $date, string $status = 'belum_final'): void
    {
        DB::table('purchase_realizations')->insert([
            'unit_id' => $unitId,
            'procurement_batch_id' => $batchId,
            'item_name' => $name,
            'quantity' => $qty,
            'cost' => $cost,
            'purchase_date' => $date,
            'status' => $status,
            'recorded_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
