<?php

namespace Database\Seeders;

use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\UnitHunian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PetugasRiwayatTestSeeder extends Seeder
{
    /**
     * Membuat data uji khusus untuk TC_045 & TC_047 di halaman Riwayat Petugas (/petugas/riwayat):
     * - Unit A-12 (Budi Santoso, 4.224 m3, SN: WM-0091823) milik petugas1@grandsul.web.id
     * - Catatan milik petugas lain (petugas2@grandsul.web.id) untuk menguji proteksi IDOR (403 Forbidden)
     */
    public function run(): void
    {
        $petugasRole = Role::firstOrCreate(['name' => 'petugas']);

        $petugas1 = User::query()->where('email', 'petugas1@grandsul.web.id')->first()
            ?? User::query()->role('petugas')->first();

        $petugas2 = User::query()->firstOrCreate(
            ['email' => 'petugas2@grandsul.web.id'],
            [
                'name' => 'Hasan (Petugas Lapangan 2)',
                'password' => Hash::make('password123'),
            ]
        );
        $petugas2->syncRoles([$petugasRole]);

        $blokAId = BlokHunian::query()->where('nama_blok', 'Blok A')->value('id') ?? 1;
        $blokBId = BlokHunian::query()->where('nama_blok', 'Blok B')->value('id') ?? 2;

        $now = Carbon::now();

        // 1. Data Uji TC_047: Unit A-12 (Budi Santoso, Stand Kini 4.224 m3, SN: WM-0091823)
        // Dibuat paling terbaru agar langsung muncul di kartu paling atas pada /petugas/riwayat.
        $unitA12 = UnitHunian::query()->updateOrCreate(
            ['nomor_rumah' => 'A-12'],
            [
                'blok_id' => $blokAId,
                'nama_pemilik' => 'Budi Santoso',
                'nomor_wa' => '081244000012',
                'status_huni' => 'Dihuni',
                'latitude' => -4.007468,
                'longitude' => 119.661131,
                'radius_toleransi' => 50,
            ]
        );

        if ($petugas1) {
            BacaanMeter::query()->updateOrCreate(
                [
                    'unit_id' => $unitA12->id,
                    'bulan' => 11,
                    'tahun' => 2026,
                ],
                [
                    'user_id' => $petugas1->id,
                    'foto_meter' => 'meter-photos/sample-meter-a12.jpg',
                    'angka_meter_bulanlalu' => 4206,
                    'angka_meter' => 4224,
                    'latitude' => -4.007468,
                    'longitude' => 119.661131,
                    'status_verifikasi' => 'valid',
                    'gps_unverified' => false,
                    'confidence_score' => 98.5,
                    'is_ocr_corrected' => false,
                    'catatan_anomali' => 'SN: WM-0091823',
                    'alasan_penolakan' => null,
                    'created_at' => $now->copy()->addMinutes(5),
                    'updated_at' => $now->copy()->addMinutes(5),
                ]
            );
        }

        // 2. Data Uji TC_045 (IDOR 403): Catatan milik petugas lain (petugas2@grandsul.web.id)
        $unitB07 = UnitHunian::query()->updateOrCreate(
            ['nomor_rumah' => 'B-07'],
            [
                'blok_id' => $blokBId,
                'nama_pemilik' => 'Siti Aminah',
                'nomor_wa' => '081244000007',
                'status_huni' => 'Dihuni',
                'latitude' => -4.007512,
                'longitude' => 119.661245,
                'radius_toleransi' => 50,
            ]
        );

        BacaanMeter::query()->updateOrCreate(
            [
                'unit_id' => $unitB07->id,
                'bulan' => 11,
                'tahun' => 2026,
            ],
            [
                'user_id' => $petugas2->id,
                'foto_meter' => 'meter-photos/sample-meter-a03.jpg',
                'angka_meter_bulanlalu' => 1200,
                'angka_meter' => 1218,
                'latitude' => -4.007512,
                'longitude' => 119.661245,
                'status_verifikasi' => 'valid',
                'gps_unverified' => false,
                'confidence_score' => 97.2,
                'is_ocr_corrected' => false,
                'catatan_anomali' => 'SN: WM-0099907 (Milik Petugas 2)',
                'alasan_penolakan' => null,
            ]
        );
    }
}
