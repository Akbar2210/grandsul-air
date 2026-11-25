<?php

namespace Database\Seeders;

use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\Tagihan;
use App\Models\TarifAir;
use App\Models\UnitHunian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SuratTunggakanTestSeeder extends Seeder
{
    /**
     * Membuat data uji khusus untuk pengujian Surat Peringatan Tunggakan (TC_040 s/d TC_044):
     * - Unit D-14 (Hasanuddin): 4 bulan tunggakan (TC_040)
     * - Unit A-05 (Nur Hidayah): 2 bulan tunggakan / < 4 bulan (TC_041 & TC_043)
     * - Unit B-11 (Rina Wulandari): 6 bulan tunggakan (TC_042 & TC_044)
     */
    public function run(): void
    {
        $petugasId = User::query()->where('email', 'petugas1@grandsul.web.id')->value('id')
            ?? User::query()->value('id');
        $tarifId = TarifAir::query()->orderBy('id')->value('id') ?? 1;

        $blokAId = BlokHunian::query()->where('nama_blok', 'Blok A')->value('id') ?? 1;
        $blokBId = BlokHunian::query()->where('nama_blok', 'Blok B')->value('id') ?? 2;
        $blokDId = BlokHunian::query()->where('nama_blok', 'Blok D')->value('id') ?? 4;

        // 1. TC_040 — Unit D-14 (Hasanuddin): 4 bulan tunggakan (Agu s/d Nov 2026)
        $unitD14 = UnitHunian::query()->updateOrCreate(
            ['nomor_rumah' => 'D-14'],
            [
                'blok_id' => $blokDId,
                'nama_pemilik' => 'Hasanuddin',
                'nomor_wa' => '081244000014',
                'status_huni' => 'Dihuni',
                'radius_toleransi' => 50,
            ]
        );

        $this->seedUnpaidMonths($unitD14, $petugasId, $tarifId, 4100, [
            ['priode' => '2026-08', 'vol' => 15, 'total' => 35000.00],
            ['priode' => '2026-09', 'vol' => 16, 'total' => 38000.00],
            ['priode' => '2026-10', 'vol' => 18, 'total' => 44000.00],
            ['priode' => '2026-11', 'vol' => 25, 'total' => 75000.00],
        ]);

        // 2. TC_041 & TC_043 — Unit A-05 (Nur Hidayah): tepat 2 bulan tunggakan (Sep & Okt 2026, < 4 bulan)
        $unitA05 = UnitHunian::query()->updateOrCreate(
            ['nomor_rumah' => 'A-05'],
            [
                'blok_id' => $blokAId,
                'nama_pemilik' => 'Nur Hidayah',
                'nomor_wa' => '081244000005',
                'status_huni' => 'Dihuni',
                'radius_toleransi' => 50,
            ]
        );

        // Pastikan selain Sep & Okt 2026 tidak ada tunggakan belum lunas lain agar total tunggakan tepat 2 bulan
        Tagihan::query()
            ->where('unit_id', $unitA05->id)
            ->whereNotIn('priode', ['2026-09', '2026-10'])
            ->update(['status_bayar' => 'lunas']);

        $this->seedUnpaidMonths($unitA05, $petugasId, $tarifId, 2100, [
            ['priode' => '2026-09', 'vol' => 12, 'total' => 26000.00],
            ['priode' => '2026-10', 'vol' => 14, 'total' => 32000.00],
        ]);

        // 3. TC_042 & TC_044 — Unit B-11 (Rina Wulandari): 6 bulan tunggakan (Jun s/d Nov 2026)
        $unitB11 = UnitHunian::query()->updateOrCreate(
            ['nomor_rumah' => 'B-11'],
            [
                'blok_id' => $blokBId,
                'nama_pemilik' => 'Rina Wulandari',
                'nomor_wa' => '081244000011',
                'status_huni' => 'Dihuni',
                'radius_toleransi' => 50,
            ]
        );

        $this->seedUnpaidMonths($unitB11, $petugasId, $tarifId, 3200, [
            ['priode' => '2026-06', 'vol' => 15, 'total' => 35000.00],
            ['priode' => '2026-07', 'vol' => 16, 'total' => 38000.00],
            ['priode' => '2026-08', 'vol' => 17, 'total' => 41000.00],
            ['priode' => '2026-09', 'vol' => 15, 'total' => 35000.00],
            ['priode' => '2026-10', 'vol' => 18, 'total' => 44000.00],
            ['priode' => '2026-11', 'vol' => 16, 'total' => 38000.00],
        ]);
    }

    /**
     * @param  array<int, array{priode: string, vol: int, total: float}>  $months
     */
    private function seedUnpaidMonths(UnitHunian $unit, int $petugasId, int $tarifId, int $baseMeter, array $months): void
    {
        $currentMeter = $baseMeter;

        foreach ($months as $item) {
            $date = Carbon::parse($item['priode'].'-01');
            $nextMeter = $currentMeter + $item['vol'];

            $bacaan = BacaanMeter::query()->updateOrCreate(
                [
                    'unit_id' => $unit->id,
                    'bulan' => (int) $date->format('m'),
                    'tahun' => (int) $date->format('Y'),
                ],
                [
                    'user_id' => $petugasId,
                    'foto_meter' => 'meter-photos/sample-meter-tunggakan.jpg',
                    'angka_meter_bulanlalu' => $currentMeter,
                    'angka_meter' => $nextMeter,
                    'status_verifikasi' => 'valid',
                    'gps_unverified' => false,
                    'confidence_score' => 98.5,
                    'is_ocr_corrected' => false,
                ]
            );

            Tagihan::query()->updateOrCreate(
                [
                    'unit_id' => $unit->id,
                    'priode' => $item['priode'],
                ],
                [
                    'bacaan_meter_id' => $bacaan->id,
                    'tarif_id' => $tarifId,
                    'volume_pemakaian' => $item['vol'],
                    'denda' => 0,
                    'biaya_admin' => 0,
                    'jumlah_tunggakan' => 0,
                    'total_tagihan' => $item['total'],
                    'status_bayar' => 'belum_lunas',
                    'tgl_terbit' => $date->copy()->day(1)->toDateString(),
                    'tgl_jatuh_tempo' => $date->copy()->day(20)->toDateString(),
                ]
            );

            $currentMeter = $nextMeter;
        }
    }
}
