<?php

namespace App\Console\Commands;

use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TarifAir;
use App\Models\UnitHunian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SeedSevenHundredData extends Command
{
    protected $signature = 'app:seed-700';
    protected $description = 'Seed exact 700 units, 700 tagihan, 407 lunas, 248 belum lunas, 45 ter-flag as specified in the Academic Proposal';

    public function handle()
    {
        $this->info('Starting 700 units & tagihans synchronization...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Pembayaran::truncate();
        Tagihan::truncate();
        BacaanMeter::truncate();
        UnitHunian::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Roles & Users
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $petugasRole = Role::firstOrCreate(['name' => 'petugas']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@grandsul.web.id'],
            [
                'name' => 'Admin Pengelola Air',
                'password' => Hash::make('password123'),
            ]
        );
        $admin->syncRoles([$adminRole]);

        $petugas = User::firstOrCreate(
            ['email' => 'petugas1@grandsul.web.id'],
            [
                'name' => 'Hendra (Petugas Lapangan)',
                'password' => Hash::make('password123'),
            ]
        );
        $petugas->syncRoles([$petugasRole]);

        // 2. Blok Hunian (A-F)
        $bloks = [];
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $b) {
            $bloks[$b] = BlokHunian::firstOrCreate(
                ['nama_blok' => 'Blok '.$b],
                ['keterangan_wilayah' => 'Perumahan Grand Sulawesi - Sektor '.$b]
            );
        }

        // 3. Tarifs
        $tarif1 = TarifAir::firstOrCreate(['blok_konsumsi' => '0 - 10 m³'], ['batas_bawah' => 0, 'batas_atas' => 10, 'tarif_per_m3' => 2000]);
        $tarif2 = TarifAir::firstOrCreate(['blok_konsumsi' => '11 - 20 m³'], ['batas_bawah' => 11, 'batas_atas' => 20, 'tarif_per_m3' => 3000]);
        $tarif3 = TarifAir::firstOrCreate(['blok_konsumsi' => '> 20 m³'], ['batas_bawah' => 21, 'batas_atas' => null, 'tarif_per_m3' => 5000]);

        $now = Carbon::now();
        $periodeStr = $now->format('Y-m');

        // 4. Generate 700 units across Blok A-F
        // Blok A: 120, Blok B: 116, Blok C: 116, Blok D: 116, Blok E: 116, Blok F: 116 = 700
        $blokDistribution = [
            'A' => 120,
            'B' => 116,
            'C' => 116,
            'D' => 116,
            'E' => 116,
            'F' => 116,
        ];

        $specialNames = [
            'A-03' => 'Ibu Siti Nurhaliza',
            'A-05' => 'Nur Hidayah',
            'A-12' => 'Budi Santoso',
            'B-07' => 'Siti Aminah',
            'C-03' => 'Andi Rahman',
            'D-14' => 'Hasanuddin',
            'E-02' => 'Yusuf Hakim',
            'F-06' => 'Syamsul Bahri',
        ];

        $unitList = [];
        $unitIndex = 1;
        foreach ($blokDistribution as $blokKey => $count) {
            for ($num = 1; $num <= $count; $num++) {
                $nomorRumah = $blokKey.'-'.str_pad($num, 2, '0', STR_PAD_LEFT);
                $nama = $specialNames[$nomorRumah] ?? ('Warga '.$blokKey.' '.str_pad($num, 2, '0', STR_PAD_LEFT));
                $unitList[] = [
                    'blok_id' => $bloks[$blokKey]->id,
                    'nomor_rumah' => $nomorRumah,
                    'nama_pemilik' => $nama,
                    'nomor_wa' => '0812'.rand(10000000, 99999999),
                    'status_huni' => 'Dihuni',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        UnitHunian::insert($unitList);
        $allUnits = UnitHunian::orderBy('id')->get();
        $this->info("Created {$allUnits->count()} unit hunians.");

        // 5. Volume and Tagihan target calibration
        // Total Volume target = 9.350 m³
        // Total Nilai Tagihan target = Rp 34.692.000
        // Total Lunas = 407 (58%)
        // Total Belum Lunas = 248 (35%)
        // Total Perlu Review (ter_flag) = 45 (7%)
        // 407 + 248 + 45 = 700

        $totalUnitsCount = 700;
        $targetTotalVol = 9350;
        $targetTotalNominal = 34692000;

        // Base volume per unit: generate realistic pattern around 13-14 m³
        $volumes = [];
        $currentVolSum = 0;
        for ($i = 0; $i < $totalUnitsCount; $i++) {
            // vary between 8 and 22 m³
            $cycle = $i % 15;
            $v = 10 + ($cycle % 8); // 10 to 17 m³
            $volumes[$i] = $v;
            $currentVolSum += $v;
        }

        // Adjust last units to hit exactly 9350 m³
        $diffVol = $targetTotalVol - $currentVolSum;
        $i = 0;
        while ($diffVol !== 0) {
            $step = $diffVol > 0 ? 1 : -1;
            $volumes[$i % $totalUnitsCount] += $step;
            $diffVol -= $step;
            $i++;
        }

        // Calculate progressive tariffs
        $tagihanAmounts = [];
        $nominalSum = 0;
        for ($i = 0; $i < $totalUnitsCount; $i++) {
            $v = $volumes[$i];
            if ($v <= 10) {
                $cost = $v * 2000;
            } elseif ($v <= 20) {
                $cost = 20000 + (($v - 10) * 3000);
            } else {
                $cost = 20000 + 30000 + (($v - 20) * 5000);
            }
            $cost += 15000; // standard meter maintenance fee
            $tagihanAmounts[$i] = (float) $cost;
            $nominalSum += $cost;
        }

        // Scale tagihanAmounts to exactly hit Rp 34.692.000
        $diffNominal = $targetTotalNominal - $nominalSum;
        $i = 0;
        while ($diffNominal !== 0) {
            $step = $diffNominal > 0 ? 500 : -500;
            if (abs($diffNominal) < 500) {
                $step = $diffNominal;
            }
            $tagihanAmounts[$i % $totalUnitsCount] += $step;
            $diffNominal -= $step;
            $i++;
        }

        // 6. Create Bacaan Meters and Tagihans
        // Exactly:
        // Indices 0 to 406 (407 units) => LUNAS, valid bacaan
        // Indices 407 to 654 (248 units) => BELUM LUNAS, valid bacaan
        // Indices 655 to 699 (45 units) => BELUM LUNAS, TER-FLAG bacaan (Perlu Review)

        $terflagReasons = [
            'Pemakaian melonjak (>200% rata-rata historis)',
            'Foto miring/redup, OCR confidence rendah (67%)',
            'Angka lebih kecil dari bulan lalu (-26 m³)',
            'Pemakaian nol pada unit hunian aktif',
            'Pola konsumsi tidak wajar dibanding histori 3 bulan',
            'Foto meteran buram/terhalang debu, butuh konfirmasi',
            'GPS pembacaan berjarak di atas batas toleransi',
        ];

        // Put A-12, B-07, C-03, E-02 in the ter-flag range
        $specialTerflagHomes = ['A-12', 'B-07', 'C-03', 'E-02'];

        foreach ($allUnits as $idx => $unit) {
            $vol = $volumes[$idx];
            $nominal = $tagihanAmounts[$idx];
            $standAwal = 1000 + ($idx * 15);
            $standKini = $standAwal + $vol;

            // Determine if ter-flag: exactly 45 units (4 special homes + 41 from index 659 to 699)
            $isTerflag = in_array($unit->nomor_rumah, $specialTerflagHomes) || ($idx >= 659);

            $statusVerifikasi = $isTerflag ? 'ter_flag' : 'valid';
            $catatan = null;
            $conf = 96.0;

            if ($unit->nomor_rumah === 'A-12') {
                $catatan = 'Pemakaian melonjak (>200% rata-rata)';
                $conf = 71.0;
                $statusVerifikasi = 'ter_flag';
            } elseif ($unit->nomor_rumah === 'B-07') {
                $catatan = 'Foto miring/redup, OCR confidence rendah (67%)';
                $conf = 67.0;
                $statusVerifikasi = 'ter_flag';
            } elseif ($unit->nomor_rumah === 'C-03') {
                $catatan = 'Angka lebih kecil dari bulan lalu (-26 m³)';
                $conf = 63.0;
                $statusVerifikasi = 'ter_flag';
            } elseif ($unit->nomor_rumah === 'E-02') {
                $catatan = 'Pemakaian nol pada unit hunian aktif';
                $conf = 88.0;
                $statusVerifikasi = 'ter_flag';
            } elseif ($isTerflag) {
                $catatan = $terflagReasons[$idx % count($terflagReasons)];
                $conf = 65.0 + ($idx % 12);
            }

            // Create Bacaan Meter
            $bacaan = BacaanMeter::create([
                'unit_id' => $unit->id,
                'user_id' => $petugas->id,
                'foto_meter' => 'sample_meter.jpg',
                'bulan' => $now->month,
                'tahun' => $now->year,
                'angka_meter_bulanlalu' => $standAwal,
                'angka_meter' => $standKini,
                'latitude' => -5.147665 + (($idx % 10) * 0.0001),
                'longitude' => 119.432731 + (($idx % 10) * 0.0001),
                'status_verifikasi' => $statusVerifikasi,
                'confidence_score' => $conf,
                'is_ocr_corrected' => false,
                'catatan_anomali' => $catatan,
                'created_at' => $now->copy()->subMinutes(700 - $idx),
                'updated_at' => $now->copy()->subMinutes(700 - $idx),
            ]);

            // Determine bill status: 407 lunas (indices 0 to 406), ensuring ter-flag are NOT lunas
            $isLunas = ($idx < 407 && $statusVerifikasi === 'valid');
            $statusBayar = $isLunas ? 'lunas' : 'belum_lunas';

            $tagihan = Tagihan::create([
                'unit_id' => $unit->id,
                'bacaan_meter_id' => $bacaan->id,
                'tarif_id' => $tarif1->id,
                'priode' => $periodeStr,
                'volume_pemakaian' => $vol,
                'total_tagihan' => $nominal,
                'status_bayar' => $statusBayar,
                'tgl_terbit' => $now->copy()->startOfMonth(),
                'tgl_jatuh_tempo' => $now->copy()->addDays(20),
                'created_at' => $now->copy()->subMinutes(700 - $idx),
                'updated_at' => $now->copy()->subMinutes(700 - $idx),
            ]);

            // If lunas, create pembayaran record
            if ($isLunas) {
                Pembayaran::create([
                    'tagihan_id' => $tagihan->id,
                    'jumlah_bayar' => $nominal,
                    'metode_bayar' => ($idx % 3 === 0) ? 'Transfer' : 'Tunai',
                    'tgl_bayar' => $now->copy()->subDays($idx % 10)->setTime(9 + ($idx % 8), rand(10, 59)),
                    'status_pembayaran' => 'Sukses',
                    'created_at' => $now->copy()->subDays($idx % 10),
                    'updated_at' => $now->copy()->subDays($idx % 10),
                ]);
            }
        }

        // Adjust counts if needed to ensure exactly:
        // Lunas: 407
        // Belum Lunas (Valid): 248
        // Ter-flag: 45
        $currentTerflagCount = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();
        if ($currentTerflagCount > 45) {
            $excess = $currentTerflagCount - 45;
            $candidates = BacaanMeter::where('status_verifikasi', 'ter_flag')
                ->whereHas('unit', fn ($u) => $u->whereNotIn('nomor_rumah', $specialTerflagHomes))
                ->take($excess)
                ->get();
            foreach ($candidates as $c) {
                $c->status_verifikasi = 'valid';
                $c->catatan_anomali = null;
                $c->confidence_score = 95.0;
                $c->save();
            }
        } elseif ($currentTerflagCount < 45) {
            $needed = 45 - $currentTerflagCount;
            $candidates = BacaanMeter::where('status_verifikasi', 'valid')
                ->whereHas('unit', fn ($u) => $u->whereNotIn('nomor_rumah', $specialTerflagHomes))
                ->take($needed)
                ->get();
            foreach ($candidates as $c) {
                $c->status_verifikasi = 'ter_flag';
                $c->catatan_anomali = 'Pola pemakaian tidak wajar atau angka meter fluktuatif';
                $c->confidence_score = 68.0;
                $c->save();
                if ($c->tagihan) {
                    $c->tagihan->status_bayar = 'belum_lunas';
                    $c->tagihan->save();
                    Pembayaran::where('tagihan_id', $c->tagihan->id)->delete();
                }
            }
        }

        $currentLunasCount = Tagihan::where('status_bayar', 'lunas')->count();
        if ($currentLunasCount < 407) {
            $diff = 407 - $currentLunasCount;
            $toLunas = Tagihan::where('status_bayar', 'belum_lunas')
                ->whereHas('bacaanMeter', fn ($b) => $b->where('status_verifikasi', 'valid'))
                ->take($diff)
                ->get();
            foreach ($toLunas as $tl) {
                $tl->status_bayar = 'lunas';
                $tl->save();
                Pembayaran::create([
                    'tagihan_id' => $tl->id,
                    'jumlah_bayar' => $tl->total_tagihan,
                    'metode_bayar' => 'Transfer',
                    'tgl_bayar' => $now->copy()->subDays(2),
                    'status_pembayaran' => 'Sukses',
                ]);
            }
        } elseif ($currentLunasCount > 407) {
            $diff = $currentLunasCount - 407;
            $toUnpaid = Tagihan::where('status_bayar', 'lunas')->take($diff)->get();
            foreach ($toUnpaid as $tu) {
                $tu->status_bayar = 'belum_lunas';
                $tu->save();
                Pembayaran::where('tagihan_id', $tu->id)->delete();
            }
        }

        $realUnits = UnitHunian::count();
        $realTagihans = Tagihan::count();
        $realLunas = Tagihan::where('status_bayar', 'lunas')->count();
        $realBelumLunas = Tagihan::where('status_bayar', 'belum_lunas')->count();
        $realTerflag = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();
        $realSumNominal = Tagihan::sum('total_tagihan');
        $realSumVol = Tagihan::sum('volume_pemakaian');

        $this->info('=== SEEDING RESULT ===');
        $this->info("Total Units: {$realUnits}");
        $this->info("Total Tagihan: {$realTagihans}");
        $this->info("Lunas: {$realLunas} (".round(($realLunas / $realTagihans) * 100).'%)');
        $this->info("Belum Lunas: {$realBelumLunas} (".round(($realBelumLunas / $realTagihans) * 100).'%)');
        $this->info("Ter-flag: {$realTerflag}");
        $this->info('Total Nilai Tagihan: Rp '.number_format($realSumNominal, 0, ',', '.'));
        $this->info('Total Volume: '.number_format($realSumVol, 0, ',', '.').' m3');
    }
}
