<?php

namespace App\Console\Commands;

use App\Models\BacaanMeter;
use App\Models\Tagihan;
use App\Models\UnitHunian;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:seed-surat-tunggakan')]
#[Description('Seed sample tunggakan data (>= 4 bulan) untuk fitur Surat Penyampaian Tunggakan')]
class SeedSuratTunggakanCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai seeding data surat tunggakan (≥ 4 bulan)...');

        // Daftar 5 unit target dari berbagai blok (A, B, C, D, E)
        $targets = [
            // 1. Blok C: C-10 (6 bulan tunggakan: Juni s/d November 2026)
            [
                'nomor_rumah' => 'C-10',
                'months' => [
                    ['priode' => '2026-06', 'vol' => 16, 'total' => 38000, 'tgl' => '2026-06-20'],
                    ['priode' => '2026-07', 'vol' => 15, 'total' => 35000, 'tgl' => '2026-07-20'],
                    ['priode' => '2026-08', 'vol' => 18, 'total' => 44000, 'tgl' => '2026-08-20'],
                    ['priode' => '2026-09', 'vol' => 17, 'total' => 41000, 'tgl' => '2026-09-20'],
                    ['priode' => '2026-10', 'vol' => 16, 'total' => 38000, 'tgl' => '2026-10-20'],
                    ['priode' => '2026-11', 'vol' => 19, 'total' => 47000, 'tgl' => '2026-11-20'],
                ],
            ],
            // 2. Blok B: B-20 (5 bulan tunggakan: Juli s/d November 2026)
            [
                'nomor_rumah' => 'B-20',
                'months' => [
                    ['priode' => '2026-07', 'vol' => 18, 'total' => 44000, 'tgl' => '2026-07-20'],
                    ['priode' => '2026-08', 'vol' => 15, 'total' => 35000, 'tgl' => '2026-08-20'],
                    ['priode' => '2026-09', 'vol' => 17, 'total' => 41000, 'tgl' => '2026-09-20'],
                    ['priode' => '2026-10', 'vol' => 14, 'total' => 32000, 'tgl' => '2026-10-20'],
                    ['priode' => '2026-11', 'vol' => 16, 'total' => 38000, 'tgl' => '2026-11-20'],
                ],
            ],
            // 3. Blok A: A-15 (5 bulan tunggakan: Juli s/d November 2026)
            [
                'nomor_rumah' => 'A-15',
                'months' => [
                    ['priode' => '2026-07', 'vol' => 14, 'total' => 32000, 'tgl' => '2026-07-20'],
                    ['priode' => '2026-08', 'vol' => 16, 'total' => 38000, 'tgl' => '2026-08-20'],
                    ['priode' => '2026-09', 'vol' => 15, 'total' => 35000, 'tgl' => '2026-09-20'],
                    ['priode' => '2026-10', 'vol' => 17, 'total' => 41000, 'tgl' => '2026-10-20'],
                    ['priode' => '2026-11', 'vol' => 15, 'total' => 35000, 'tgl' => '2026-11-20'],
                ],
            ],
            // 4. Blok D: D-56 (4 bulan tunggakan: Agustus s/d November 2026)
            [
                'nomor_rumah' => 'D-56',
                'months' => [
                    ['priode' => '2026-08', 'vol' => 14, 'total' => 32000, 'tgl' => '2026-08-20'],
                    ['priode' => '2026-09', 'vol' => 13, 'total' => 29000, 'tgl' => '2026-09-20'],
                    ['priode' => '2026-10', 'vol' => 15, 'total' => 35000, 'tgl' => '2026-10-20'],
                    ['priode' => '2026-11', 'vol' => 12, 'total' => 26000, 'tgl' => '2026-11-20'],
                ],
            ],
            // 5. Blok E: E-05 (4 bulan tunggakan: Agustus s/d November 2026)
            [
                'nomor_rumah' => 'E-05',
                'months' => [
                    ['priode' => '2026-08', 'vol' => 15, 'total' => 35000, 'tgl' => '2026-08-20'],
                    ['priode' => '2026-09', 'vol' => 16, 'total' => 38000, 'tgl' => '2026-09-20'],
                    ['priode' => '2026-10', 'vol' => 17, 'total' => 41000, 'tgl' => '2026-10-20'],
                    ['priode' => '2026-11', 'vol' => 18, 'total' => 44000, 'tgl' => '2026-11-20'],
                ],
            ],
        ];

        $createdCount = 0;

        foreach ($targets as $target) {
            $unit = UnitHunian::where('nomor_rumah', $target['nomor_rumah'])->first();
            if (! $unit) {
                $this->warn("Unit {$target['nomor_rumah']} tidak ditemukan, lewati.");
                continue;
            }

            $currentBaseMeter = 5000 + ($unit->id * 20);

            foreach ($target['months'] as $m) {
                $carbonDate = Carbon::parse($m['priode'] . '-01');
                $bulan = (int) $carbonDate->format('m');
                $tahun = (int) $carbonDate->format('Y');

                // Bacaan meter
                $bacaan = BacaanMeter::updateOrCreate(
                    [
                        'unit_id' => $unit->id,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                    ],
                    [
                        'user_id' => 2,
                        'foto_meter' => 'sample_meter.jpg',
                        'angka_meter_bulanlalu' => $currentBaseMeter,
                        'angka_meter' => $currentBaseMeter + $m['vol'],
                        'latitude' => -5.147265,
                        'longitude' => 119.433131,
                        'status_verifikasi' => 'valid',
                        'confidence_score' => 96.0,
                        'is_ocr_corrected' => false,
                        'catatan_anomali' => null,
                    ]
                );

                // Tagihan belum lunas
                $tagihan = Tagihan::updateOrCreate(
                    [
                        'unit_id' => $unit->id,
                        'priode' => $m['priode'],
                    ],
                    [
                        'bacaan_meter_id' => $bacaan->id,
                        'tarif_id' => 2,
                        'volume_pemakaian' => $m['vol'],
                        'denda' => 0,
                        'biaya_admin' => 0,
                        'jumlah_tunggakan' => 0,
                        'total_tagihan' => $m['total'],
                        'status_bayar' => 'belum_lunas',
                        'tgl_terbit' => $m['tgl'],
                        'tgl_jatuh_tempo' => Carbon::parse($m['tgl'])->endOfMonth()->toDateString(),
                    ]
                );

                $currentBaseMeter += $m['vol'];
                $createdCount++;
            }

            $this->line("  ✓ Unit {$unit->nomor_rumah} ({$unit->nama_pemilik}) berhasil diset menunggak " . count($target['months']) . " bulan.");
        }

        $this->info("Selesai! {$createdCount} data tagihan tunggakan berhasil diproses.");

        return Command::SUCCESS;
    }
}
