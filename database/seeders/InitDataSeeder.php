<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TarifAir;
use App\Models\BlokHunian;
use App\Models\UnitHunian;
use App\Models\BacaanMeter;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class InitDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Role (Spatie)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $petugasRole = Role::firstOrCreate(['name' => 'petugas']);

        // 2. Akun Admin Utama
        $admin = User::firstOrCreate(
            ['email' => 'admin@grandsul.web.id'],
            [
                'name' => 'Admin Pengelola Air',
                'password' => Hash::make('password123'),
            ]
        );
        $admin->syncRoles([$adminRole]);

        // 3. Akun Petugas Lapangan
        $petugas = User::firstOrCreate(
            ['email' => 'petugas1@grandsul.web.id'],
            [
                'name' => 'Hendra (Petugas Lapangan)',
                'password' => Hash::make('password123'),
            ]
        );
        $petugas->syncRoles([$petugasRole]);

        // 4. Data Master Blok Hunian (Blok A-F)
        $bloks = [];
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $b) {
            $bloks[$b] = BlokHunian::firstOrCreate(
                ['nama_blok' => 'Blok ' . $b],
                ['keterangan_wilayah' => 'Perumahan Grand Sulawesi - Sektor ' . $b]
            );
        }

        // 5. Data Master Skema Tarif Progresif
        $tarif1 = TarifAir::firstOrCreate(['blok_konsumsi' => '0 - 10 m³'], ['batas_bawah' => 0, 'batas_atas' => 10, 'tarif_per_m3' => 2000]);
        $tarif2 = TarifAir::firstOrCreate(['blok_konsumsi' => '11 - 20 m³'], ['batas_bawah' => 11, 'batas_atas' => 20, 'tarif_per_m3' => 3000]);
        $tarif3 = TarifAir::firstOrCreate(['blok_konsumsi' => '> 20 m³'], ['batas_bawah' => 21, 'batas_atas' => null, 'tarif_per_m3' => 5000]);

        // 6. Data Unit Hunian Sesuai SRS Lampiran C & Mockup
        $sampleUnits = [
            ['blok' => 'A', 'no' => 'A-03', 'nama' => 'Ibu Siti Nurhaliza', 'wa' => '081234567890', 'huni' => 'Dihuni', 'meter_lalu' => 18, 'meter_kini' => 18, 'status' => 'belum_dicatat'],
            ['blok' => 'A', 'no' => 'A-12', 'nama' => 'Budi Santoso', 'wa' => '081234567801', 'huni' => 'Dihuni', 'meter_lalu' => 1700, 'meter_kini' => 1780, 'status' => 'ter_flag', 'catatan' => 'Pemakaian melonjak (>200% rata-rata)', 'conf' => 71.0],
            ['blok' => 'B', 'no' => 'B-07', 'nama' => 'Siti Aminah', 'wa' => '081234567802', 'huni' => 'Dihuni', 'meter_lalu' => 1713, 'meter_kini' => 1820, 'status' => 'ter_flag', 'catatan' => 'Foto miring/redup, OCR confidence rendah (67%)', 'conf' => 67.0],
            ['blok' => 'C', 'no' => 'C-03', 'nama' => 'Andi Rahman', 'wa' => '081234567803', 'huni' => 'Dihuni', 'meter_lalu' => 1726, 'meter_kini' => 1700, 'status' => 'ter_flag', 'catatan' => 'Angka lebih kecil dari bulan lalu (-26 m³)', 'conf' => 63.0],
            ['blok' => 'A', 'no' => 'A-05', 'nama' => 'Nur Hidayah', 'wa' => '081234567804', 'huni' => 'Dihuni', 'meter_lalu' => 1739, 'meter_kini' => 1753, 'status' => 'valid', 'catatan' => null, 'conf' => 96.0],
            ['blok' => 'D', 'no' => 'D-14', 'nama' => 'Hasanuddin', 'wa' => '081234567805', 'huni' => 'Dihuni', 'meter_lalu' => 1600, 'meter_kini' => 1625, 'status' => 'valid', 'catatan' => 'Tunggakan > 4 bulan', 'conf' => 98.0],
            ['blok' => 'E', 'no' => 'E-02', 'nama' => 'Yusuf Hakim', 'wa' => '081234567806', 'huni' => 'Dihuni', 'meter_lalu' => 1550, 'meter_kini' => 1550, 'status' => 'ter_flag', 'catatan' => 'Pemakaian nol pada unit hunian aktif', 'conf' => 88.0],
            ['blok' => 'F', 'no' => 'F-06', 'nama' => 'Syamsul Bahri', 'wa' => '081234567807', 'huni' => 'Dihuni', 'meter_lalu' => 1420, 'meter_kini' => 1435, 'status' => 'ditolak', 'catatan' => 'GPS berjarak 180 m dari rumah', 'conf' => 85.0],
        ];

        // Tambah unit-unit tambahan agar total unit realistis (misal 140 unit untuk Blok A-F)
        for ($i = 1; $i <= 132; $i++) {
            $blokKey = ['A', 'B', 'C', 'D', 'E', 'F'][$i % 6];
            $sampleUnits[] = [
                'blok' => $blokKey,
                'no' => $blokKey . '-' . str_pad($i + 15, 2, '0', STR_PAD_LEFT),
                'nama' => 'Warga ' . $blokKey . ' ' . ($i + 15),
                'wa' => '08129876' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'huni' => ($i % 10 === 0) ? 'Kosong' : 'Dihuni',
                'meter_lalu' => 1000 + ($i * 12),
                'meter_kini' => 1015 + ($i * 12),
                'status' => ($i <= 120) ? 'valid' : 'belum_dicatat',
                'catatan' => null,
                'conf' => 95.0,
            ];
        }

        $now = Carbon::now();
        $priode = $now->format('Y-m');

        foreach ($sampleUnits as $data) {
            $unit = UnitHunian::firstOrCreate(
                ['nomor_rumah' => $data['no']],
                [
                    'blok_id' => $bloks[$data['blok']]->id,
                    'nama_pemilik' => $data['nama'],
                    'nomor_wa' => $data['wa'],
                    'status_huni' => $data['huni'],
                ]
            );

            if ($data['status'] !== 'belum_dicatat') {
                $pemakaian = max(0, $data['meter_kini'] - $data['meter_lalu']);
                $bacaan = BacaanMeter::firstOrCreate(
                    [
                        'unit_id' => $unit->id,
                        'bulan' => $now->month,
                        'tahun' => $now->year,
                    ],
                    [
                        'user_id' => $petugas->id,
                        'foto_meter' => 'sample_meter.jpg',
                        'angka_meter_bulanlalu' => $data['meter_lalu'],
                        'angka_meter' => $data['meter_kini'],
                        'latitude' => -5.147665,
                        'longitude' => 119.432731,
                        'status_verifikasi' => $data['status'],
                        'confidence_score' => $data['conf'] ?? 95.0,
                        'is_ocr_corrected' => false,
                        'catatan_anomali' => $data['catatan'] ?? null,
                    ]
                );

                if ($data['status'] === 'valid') {
                    $totalTagihan = ($pemakaian <= 10) ? ($pemakaian * 2000) : (20000 + ($pemakaian - 10) * 3000);
                    $tagihan = Tagihan::firstOrCreate(
                        [
                            'unit_id' => $unit->id,
                            'priode' => $priode,
                        ],
                        [
                            'bacaan_meter_id' => $bacaan->id,
                            'tarif_id' => $tarif1->id,
                            'volume_pemakaian' => $pemakaian,
                            'total_tagihan' => $totalTagihan,
                            'status_bayar' => 'belum_lunas',
                            'tgl_terbit' => $now->copy()->startOfMonth(),
                            'tgl_jatuh_tempo' => $now->copy()->addDays(20),
                        ]
                    );
                }
            }
        }
    }
}
