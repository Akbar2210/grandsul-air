<?php

namespace Database\Seeders;

use App\Models\BacaanMeter;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TarifAir;
use App\Models\UnitHunian;
use App\Models\User;
use App\Services\CyberSecurity\RsaSignatureService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VerifikasiNotaTestSeeder extends Seeder
{
    /**
     * Membuat data uji khusus di Daftar Tagihan untuk pengujian QR Verifikasi Nota (TC_034, TC_035, TC_036).
     */
    public function run(): void
    {
        $rsaService = app(RsaSignatureService::class);
        $baseUrl = rtrim((string) config('app.url', 'http://localhost:8000'), '/');

        $petugasId = User::query()->where('email', 'petugas1@grandsul.web.id')->value('id')
            ?? User::query()->value('id');
        $tarifId = TarifAir::query()->orderBy('id')->value('id') ?? 1;

        $now = Carbon::now();

        // 1. Data Uji Utama (Unit A-12 - Budi Santoso, Status Lunas, 18 m3 = Rp 44.000)
        // Dibuat paling baru agar langsung tampil di baris teratas halaman Daftar Tagihan.
        $unitA12 = UnitHunian::query()->where('nomor_rumah', 'A-12')->first()
            ?? UnitHunian::query()->find(2);

        if ($unitA12) {
            $bacaanA12 = BacaanMeter::query()->updateOrCreate(
                [
                    'unit_id' => $unitA12->id,
                    'bulan' => 11,
                    'tahun' => 2026,
                ],
                [
                    'user_id' => $petugasId,
                    'foto_meter' => 'meter-photos/sample-meter-a12.jpg',
                    'angka_meter_bulanlalu' => 100,
                    'angka_meter' => 118,
                    'status_verifikasi' => 'valid',
                    'gps_unverified' => false,
                    'confidence_score' => 99.5,
                    'is_ocr_corrected' => false,
                ]
            );

            $tagihanA12 = Tagihan::query()->updateOrCreate(
                [
                    'unit_id' => $unitA12->id,
                    'priode' => '2026-11',
                ],
                [
                    'bacaan_meter_id' => $bacaanA12->id,
                    'tarif_id' => $tarifId,
                    'volume_pemakaian' => 18,
                    'denda' => 0,
                    'biaya_admin' => 0,
                    'jumlah_tunggakan' => 0,
                    'total_tagihan' => 44000.00,
                    'status_bayar' => 'lunas',
                    'tgl_terbit' => '2026-11-25',
                    'tgl_jatuh_tempo' => '2026-12-15',
                    'created_at' => $now->copy()->addSeconds(10),
                    'updated_at' => $now->copy()->addSeconds(10),
                ]
            );

            Pembayaran::query()->updateOrCreate(
                ['tagihan_id' => $tagihanA12->id],
                [
                    'jumlah_bayar' => 44000.00,
                    'metode_bayar' => 'Tunai',
                    'tgl_bayar' => $now,
                    'status_pembayaran' => 'lunas',
                ]
            );

            $rsaService->issueTagihanSignature($tagihanA12, $baseUrl);
        }

        // 2. Data Uji Kedua (NOTA PALSU / DIMANIPULASI — Unit A-03, Ibu Siti Nurhaliza, 12 m3 = Rp 26.000)
        // Tampil di baris ke-2 halaman Daftar Tagihan. Signature RSA-2048 sengaja dimanipulasi agar saat Cetak Nota
        // & scan QR Code langsung terdeteksi MERAH (NOTA TIDAK VALID / PALSU / DIMANIPULASI).
        $unitA03 = UnitHunian::query()->where('nomor_rumah', 'A-03')->first()
            ?? UnitHunian::query()->find(1);

        if ($unitA03) {
            $bacaanA03 = BacaanMeter::query()->updateOrCreate(
                [
                    'unit_id' => $unitA03->id,
                    'bulan' => 11,
                    'tahun' => 2026,
                ],
                [
                    'user_id' => $petugasId,
                    'foto_meter' => 'meter-photos/sample-meter-a03.jpg',
                    'angka_meter_bulanlalu' => 210,
                    'angka_meter' => 222,
                    'status_verifikasi' => 'valid',
                    'gps_unverified' => false,
                    'confidence_score' => 98.8,
                    'is_ocr_corrected' => false,
                ]
            );

            $tagihanA03 = Tagihan::query()->updateOrCreate(
                [
                    'unit_id' => $unitA03->id,
                    'priode' => '2026-11',
                ],
                [
                    'bacaan_meter_id' => $bacaanA03->id,
                    'tarif_id' => $tarifId,
                    'volume_pemakaian' => 12,
                    'denda' => 0,
                    'biaya_admin' => 0,
                    'jumlah_tunggakan' => 0,
                    'total_tagihan' => 26000.00,
                    'status_bayar' => 'belum_lunas',
                    'tgl_terbit' => '2026-11-25',
                    'tgl_jatuh_tempo' => '2026-12-15',
                    'created_at' => $now->copy()->addSeconds(5),
                    'updated_at' => $now->copy()->addSeconds(5),
                ]
            );

            $issuedFake = $rsaService->issueTagihanSignature($tagihanA03, $baseUrl);
            $issuedFake['record']->update([
                'digital_signature' => base64_encode('PALSU_DIMANIPULASI_TC035_'.str_repeat('X', 231)),
            ]);
        }

        // 3. Pastikan Tagihan ID #12 (TGH-2611-0012 / Unit E-25) juga sudah memiliki RSA Digital Signature
        $tagihan12 = Tagihan::query()->find(12);
        if ($tagihan12) {
            $rsaService->issueTagihanSignature($tagihan12, $baseUrl);
        }
    }
}
