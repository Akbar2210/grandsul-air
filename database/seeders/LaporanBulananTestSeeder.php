<?php

namespace Database\Seeders;

use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\Tagihan;
use App\Models\TarifAir;
use App\Models\UnitHunian;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaporanBulananTestSeeder extends Seeder
{
    /**
     * Membuat data uji khusus Laporan Bulanan Oktober 2026 (DU-048) untuk TC_057 s/d TC_060:
     * - Total Blok A s.d. F: 700 unit, 13.350 m³, Total Tagihan Rp148.320.000
     * - Khusus Blok A (TC_058): 120 unit, 2.410 m³, Tagihan Rp26.100.000, Terbayar Rp19.800.000, Belum Lunas Rp6.300.000
     * - Periode Desember 2027 (TC_059): dipastikan kosong (0 transaksi)
     */
    public function run(): void
    {
        $petugasId = User::query()->where('email', 'petugas1@grandsul.web.id')->value('id')
            ?? User::query()->value('id');
        $tarifId = TarifAir::query()->orderBy('id')->value('id') ?? 1;

        // Target agregasi eksak per Blok untuk periode Oktober 2026 (Total: 700 unit, 13.350 m³, Rp148.320.000)
        $targets = [
            'Blok A' => ['prefix' => 'A', 'units' => 120, 'vol' => 2410, 'total' => 26100000, 'paid' => 19800000],
            'Blok B' => ['prefix' => 'B', 'units' => 120, 'vol' => 2280, 'total' => 25400000, 'paid' => 21000000],
            'Blok C' => ['prefix' => 'C', 'units' => 120, 'vol' => 2250, 'total' => 25120000, 'paid' => 20500000],
            'Blok D' => ['prefix' => 'D', 'units' => 120, 'vol' => 2210, 'total' => 24600000, 'paid' => 19600000],
            'Blok E' => ['prefix' => 'E', 'units' => 110, 'vol' => 2100, 'total' => 23600000, 'paid' => 18800000],
            'Blok F' => ['prefix' => 'F', 'units' => 110, 'vol' => 2100, 'total' => 23500000, 'paid' => 19000000],
        ];

        $now = now();

        // Pastikan periode 2027-12 kosong untuk TC_059
        Tagihan::query()->where('priode', '2027-12')->delete();

        DB::transaction(function () use ($targets, $petugasId, $tarifId, $now) {
            foreach ($targets as $namaBlok => $cfg) {
                $blok = BlokHunian::query()->firstOrCreate(
                    ['nama_blok' => $namaBlok],
                    ['keterangan_wilayah' => "Perumahan Grand Sulawesi - {$namaBlok}"]
                );

                // 1. Pastikan jumlah unit pada blok mencapai target (tanpa mengubah unit lama)
                $existingUnits = UnitHunian::query()->where('blok_id', $blok->id)->orderBy('id')->get();
                $needUnits = max(0, $cfg['units'] - $existingUnits->count());

                if ($needUnits > 0) {
                    $existingNumbers = $existingUnits->pluck('nomor_rumah')->flip();
                    $newUnitRows = [];
                    $seq = 101;

                    while (count($newUnitRows) < $needUnits) {
                        $kode = "{$cfg['prefix']}-{$seq}";
                        $seq++;
                        if ($existingNumbers->has($kode)) {
                            continue;
                        }
                        $existingNumbers->put($kode, true);
                        $newUnitRows[] = [
                            'blok_id' => $blok->id,
                            'nomor_rumah' => $kode,
                            'nama_pemilik' => "Warga {$kode}",
                            'nomor_wa' => '08128000'.str_pad((string) ($blok->id * 1000 + $seq), 4, '0', STR_PAD_LEFT),
                            'status_huni' => 'Dihuni',
                            'radius_toleransi' => 50,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    UnitHunian::query()->insert($newUnitRows);
                }

                // Ambil tepat sejumlah target unit pada blok ini (prioritaskan unit lama yang sudah punya tagihan 2026-10)
                $allBlokUnits = UnitHunian::query()->where('blok_id', $blok->id)->orderBy('id')->take($cfg['units'])->get();
                $unitIds = $allBlokUnits->pluck('id')->all();

                // Pertahankan tagihan 2026-10 milik unit uji tunggakan (A-05, D-14, B-11) agar TC_040-044 tetap utuh
                $preservedNumbers = ['A-05', 'D-14', 'B-11'];
                $preservedUnitIds = $allBlokUnits->whereIn('nomor_rumah', $preservedNumbers)->pluck('id')->all();

                // Bersihkan tagihan 2026-10 non-preserved pada blok ini agar seeder 100% idempoten
                Tagihan::query()
                    ->where('priode', '2026-10')
                    ->whereIn('unit_id', $unitIds)
                    ->when(! empty($preservedUnitIds), fn ($q) => $q->whereNotIn('unit_id', $preservedUnitIds))
                    ->delete();

                $preservedBills = Tagihan::query()
                    ->where('priode', '2026-10')
                    ->whereIn('unit_id', $preservedUnitIds)
                    ->get();

                $preservedBillUnitIds = $preservedBills->pluck('unit_id')->all();
                $preservedVol = (int) $preservedBills->sum('volume_pemakaian');
                $preservedPaid = (int) round((float) $preservedBills->where('status_bayar', 'lunas')->sum('total_tagihan'));
                $preservedUnpaid = (int) round((float) $preservedBills->where('status_bayar', '!=', 'lunas')->sum('total_tagihan'));

                $targetUnpaid = $cfg['total'] - $cfg['paid'];
                $remVol = $cfg['vol'] - $preservedVol;
                $remPaid = $cfg['paid'] - $preservedPaid;
                $remUnpaid = $targetUnpaid - $preservedUnpaid;

                $unitsToSeed = $allBlokUnits->reject(fn ($u) => in_array($u->id, $preservedBillUnitIds, true))->values();
                $seedCount = $unitsToSeed->count();

                if ($seedCount === 0) {
                    continue;
                }

                // Bagi unit yang akan di-seed: ~76% lunas, ~24% belum_lunas
                $paidCount = max(1, min($seedCount - 1, (int) round($seedCount * 0.76)));
                $unpaidCount = $seedCount - $paidCount;

                // Pastikan tiap unit punya bacaan_meter bulan 10 tahun 2026
                $existingBacaans = BacaanMeter::query()
                    ->whereIn('unit_id', $unitsToSeed->pluck('id')->all())
                    ->where('bulan', 10)
                    ->where('tahun', 2026)
                    ->pluck('id', 'unit_id');

                $volDistribution = $this->distributeInteger($remVol, $seedCount);
                $paidDistribution = $this->distributeInteger($remPaid, $paidCount);
                $unpaidDistribution = $this->distributeInteger($remUnpaid, $unpaidCount);

                $bacaanRowsToInsert = [];
                foreach ($unitsToSeed as $idx => $unit) {
                    if ($existingBacaans->has($unit->id)) {
                        continue;
                    }
                    $vol = $volDistribution[$idx];
                    $baseMeter = 2000 + ($unit->id * 5);
                    $bacaanRowsToInsert[] = [
                        'unit_id' => $unit->id,
                        'user_id' => $petugasId,
                        'foto_meter' => 'meter-photos/sample-meter-a12.jpg',
                        'bulan' => 10,
                        'tahun' => 2026,
                        'angka_meter_bulanlalu' => $baseMeter,
                        'angka_meter' => $baseMeter + $vol,
                        'status_verifikasi' => 'valid',
                        'gps_unverified' => false,
                        'confidence_score' => 98.0,
                        'is_ocr_corrected' => false,
                        'created_at' => '2026-10-15 09:00:00',
                        'updated_at' => '2026-10-15 09:00:00',
                    ];
                }

                if (! empty($bacaanRowsToInsert)) {
                    BacaanMeter::query()->insert($bacaanRowsToInsert);
                    $existingBacaans = BacaanMeter::query()
                        ->whereIn('unit_id', $unitsToSeed->pluck('id')->all())
                        ->where('bulan', 10)
                        ->where('tahun', 2026)
                        ->pluck('id', 'unit_id');
                }

                $tagihanRows = [];
                foreach ($unitsToSeed as $idx => $unit) {
                    $isPaid = $idx < $paidCount;
                    $nominal = $isPaid
                        ? $paidDistribution[$idx]
                        : $unpaidDistribution[$idx - $paidCount];

                    $tagihanRows[] = [
                        'unit_id' => $unit->id,
                        'bacaan_meter_id' => $existingBacaans->get($unit->id),
                        'tarif_id' => $tarifId,
                        'priode' => '2026-10',
                        'volume_pemakaian' => $volDistribution[$idx],
                        'denda' => 0,
                        'biaya_admin' => 0,
                        'jumlah_tunggakan' => 0,
                        'total_tagihan' => $nominal,
                        'status_bayar' => $isPaid ? 'lunas' : 'belum_lunas',
                        'tgl_terbit' => '2026-10-01',
                        'tgl_jatuh_tempo' => '2026-10-20',
                        'created_at' => '2026-10-15 10:00:00',
                        'updated_at' => '2026-10-15 10:00:00',
                    ];
                }

                Tagihan::query()->insert($tagihanRows);
            }
        });
    }

    /**
     * Membagi total bilangan bulat ke $count slot sehingga jumlah totalnya 100% persis.
     *
     * @return array<int, int>
     */
    private function distributeInteger(int $total, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $base = intdiv($total, $count);
        $remainder = $total - ($base * $count);

        $result = array_fill(0, $count, $base);
        for ($i = 0; $i < abs($remainder); $i++) {
            $result[$i] += ($remainder > 0 ? 1 : -1);
        }

        return $result;
    }
}
