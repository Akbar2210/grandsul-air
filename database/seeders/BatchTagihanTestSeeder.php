<?php

namespace Database\Seeders;

use App\Models\BacaanMeter;
use App\Models\Tagihan;
use App\Models\UnitHunian;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BatchTagihanTestSeeder extends Seeder
{
    /**
     * Menyiapkan data antrean Batch Processing periode Oktober 2026 (700 unit valid) untuk TC_026, TC_027, TC_028:
     * - 700 unit memiliki bacaan meter berstatus 'valid' untuk bulan 10 tahun 2026
     * - Unit A-05 memiliki volume tepat 14 m³ (2.112 -> 2.126) sehingga saat diproses batch menghasilkan Rp 43.000 (Belum Lunas)
     * - Tagihan periode 2026-10 dikosongkan agar seluruh 700/700 unit masuk antrean siap diproses saat klik 'Proses Tagihan (Batch)'
     */
    public function run(): void
    {
        $petugasId = User::query()->where('email', 'petugas1@grandsul.web.id')->value('id')
            ?? User::query()->value('id');

        // Pastikan tersedia 700 unit (panggil seeder unit bila kurang dari 700)
        if (UnitHunian::query()->count() < 700) {
            $this->call(LaporanBulananTestSeeder::class);
        }

        $units = UnitHunian::query()->orderBy('id')->take(700)->get();
        $unitIds = $units->pluck('id')->all();

        DB::transaction(function () use ($units, $unitIds, $petugasId) {
            // 1. Kosongkan tagihan periode Oktober 2026 agar 700 unit valid siap diproses batch dari 0% -> 100%
            Tagihan::query()->where('priode', '2026-10')->delete();

            // 2. Pastikan seluruh 700 unit memiliki bacaan meter Oktober 2026 berstatus 'valid'
            $existingBacaans = BacaanMeter::query()
                ->whereIn('unit_id', $unitIds)
                ->where('bulan', 10)
                ->where('tahun', 2026)
                ->get()
                ->keyBy('unit_id');

            $rowsToInsert = [];

            foreach ($units as $unit) {
                $isA05 = ($unit->nomor_rumah === 'A-05');
                $baseMeter = $isA05 ? 2112 : (2000 + ($unit->id * 5));
                $vol = $isA05 ? 14 : (12 + ($unit->id % 12)); // 12-23 m³; A-05 tepat 14 m³ -> Rp 43.000

                $existing = $existingBacaans->get($unit->id);
                if ($existing) {
                    $existing->update([
                        'angka_meter_bulanlalu' => $baseMeter,
                        'angka_meter' => $baseMeter + $vol,
                        'status_verifikasi' => 'valid',
                        'alasan_penolakan' => null,
                    ]);
                } else {
                    $rowsToInsert[] = [
                        'unit_id' => $unit->id,
                        'user_id' => $petugasId,
                        'foto_meter' => 'meter-photos/sample-meter-a12.jpg',
                        'bulan' => 10,
                        'tahun' => 2026,
                        'angka_meter_bulanlalu' => $baseMeter,
                        'angka_meter' => $baseMeter + $vol,
                        'status_verifikasi' => 'valid',
                        'gps_unverified' => false,
                        'confidence_score' => 98.5,
                        'is_ocr_corrected' => false,
                        'created_at' => '2026-10-15 09:00:00',
                        'updated_at' => '2026-10-15 09:00:00',
                    ];
                }
            }

            if (! empty($rowsToInsert)) {
                foreach (array_chunk($rowsToInsert, 250) as $chunk) {
                    BacaanMeter::query()->insert($chunk);
                }
            }
        });
    }
}
