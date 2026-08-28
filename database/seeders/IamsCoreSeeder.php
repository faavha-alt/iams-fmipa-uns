<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class IamsCoreSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Struktur unit: Fakultas > Departemen/Prodi/Lab
        $fmipa = Unit::create([
            'code' => 'FMIPA',
            'name' => 'Fakultas Matematika dan Ilmu Pengetahuan Alam',
            'type' => 'fakultas',
        ]);

        $prodiMatematika = Unit::create([
            'parent_id' => $fmipa->id,
            'code' => 'MAT',
            'name' => 'Program Studi Matematika',
            'type' => 'program_studi',
        ]);

        $prodiFisika = Unit::create([
            'parent_id' => $fmipa->id,
            'code' => 'FIS',
            'name' => 'Program Studi Fisika',
            'type' => 'program_studi',
        ]);

        $prodiKimia = Unit::create([
            'parent_id' => $fmipa->id,
            'code' => 'KIM',
            'name' => 'Program Studi Kimia',
            'type' => 'program_studi',
        ]);

        $prodiInformatika = Unit::create([
            'parent_id' => $fmipa->id,
            'code' => 'INF',
            'name' => 'Program Studi Informatika',
            'type' => 'program_studi',
        ]);

        $labFisikaDasar = Unit::create([
            'parent_id' => $prodiFisika->id,
            'code' => 'LAB-FIS-01',
            'name' => 'Laboratorium Fisika Dasar',
            'type' => 'laboratorium',
        ]);

        // 2. Lokasi contoh
        Location::create([
            'unit_id' => $fmipa->id,
            'name' => 'Ruang Dekanat',
            'building' => 'Gedung A',
            'floor' => '1',
            'room_code' => 'A-101',
        ]);

        Location::create([
            'unit_id' => $prodiInformatika->id,
            'name' => 'Ruang Dosen Informatika',
            'building' => 'Gedung B',
            'floor' => '2',
            'room_code' => 'B-201',
        ]);

        Location::create([
            'unit_id' => $labFisikaDasar->id,
            'name' => 'Lab Fisika Dasar',
            'building' => 'Gedung C',
            'floor' => '1',
            'room_code' => 'C-105',
        ]);

        // 3. Kategori aset (hierarkis)
        $elektronik = AssetCategory::create([
            'code' => 'ELK',
            'name' => 'Elektronik',
            'useful_life_years' => 5,
        ]);

        AssetCategory::create([
            'parent_id' => $elektronik->id,
            'code' => 'ELK-KOM',
            'name' => 'Komputer & Laptop',
            'useful_life_years' => 4,
        ]);

        AssetCategory::create([
            'parent_id' => $elektronik->id,
            'code' => 'ELK-PRN',
            'name' => 'Printer & Scanner',
            'useful_life_years' => 4,
        ]);

        AssetCategory::create([
            'parent_id' => $elektronik->id,
            'code' => 'ELK-PRJ',
            'name' => 'Proyektor',
            'useful_life_years' => 5,
        ]);

        $furnitur = AssetCategory::create([
            'code' => 'FUR',
            'name' => 'Furnitur',
            'useful_life_years' => 10,
        ]);

        AssetCategory::create([
            'parent_id' => $furnitur->id,
            'code' => 'FUR-MEJ',
            'name' => 'Meja',
            'useful_life_years' => 10,
        ]);

        AssetCategory::create([
            'parent_id' => $furnitur->id,
            'code' => 'FUR-KUR',
            'name' => 'Kursi',
            'useful_life_years' => 10,
        ]);

        AssetCategory::create([
            'code' => 'LAB',
            'name' => 'Peralatan Laboratorium',
            'useful_life_years' => 8,
        ]);

        // 4. Akun admin (GANTI PASSWORD SEBELUM PRODUKSI)
        $admin = \App\Models\User::create([
            'name' => 'Admin IAMS',
            'email' => 'admin@fmipa.uns.ac.id',
            'password' => 'password',
        ]);
        // role/unit_id tidak ada di $fillable (anti mass-assignment) — set eksplisit.
        $admin->role = 'admin';
        $admin->unit_id = $fmipa->id;
        $admin->is_active = true;
        $admin->is_approved = true;
        $admin->save();
    }
}
