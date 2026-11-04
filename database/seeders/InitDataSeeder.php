<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TarifAir;
use App\Models\BlokHunian;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class InitDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Role (Spatie)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $petugasRole = Role::firstOrCreate(['name' => 'petugas']);

        // 2. Akun Admin Utama
        $admin = User::create([
            'name' => 'Admin Pengelola Air',
            'email' => 'admin@grandsul.web.id',
            'password' => Hash::make('password123'),
        ]);
        $admin->assignRole($adminRole);

        // 3. Akun Petugas Lapangan
        $petugas = User::create([
            'name' => 'Petugas Meteran 1',
            'email' => 'petugas1@grandsul.web.id',
            'password' => Hash::make('password123'),
        ]);
        $petugas->assignRole($petugasRole);

        // 4. Data Master Blok Hunian (Blok A-F)
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $blok) {
            BlokHunian::create([
                'nama_blok' => 'Blok ' . $blok,
                'keterangan_wilayah' => 'Perumahan Grand Sulawesi',
            ]);
        }

        // 5. Data Master Skema Tarif Progresif
        TarifAir::create(['blok_konsumsi' => '0 - 10 m³', 'batas_bawah' => 0, 'batas_atas' => 10, 'tarif_per_m3' => 3000]);
        TarifAir::create(['blok_konsumsi' => '11 - 20 m³', 'batas_bawah' => 11, 'batas_atas' => 20, 'tarif_per_m3' => 4500]);
        TarifAir::create(['blok_konsumsi' => '> 20 m³', 'batas_bawah' => 21, 'batas_atas' => null, 'tarif_per_m3' => 6000]);
    }
}