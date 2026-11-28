<?php

namespace Tests\Feature;

use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TarifAir;
use App\Models\UnitHunian;
use App\Models\User;
use App\Services\BatchProcessing\BatchTagihanService;
use App\Services\BisnisDigital\OcrMeterService;
use App\Services\CyberSecurity\QrVerificationService;
use App\Services\CyberSecurity\RsaSignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KonsentrasiServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_bisnis_digital_ocr_service_detects_anomalies_and_valid_readings(): void
    {
        $ocrService = new OcrMeterService;

        // DATA-01: Konsumsi melonjak (1700 -> 1780 = 80 m³)
        $lonjakan = $ocrService->evaluateReadingAnomaly(1700, 1780, 95.0, 'Dihuni');
        $this->assertSame('ter_flag', $lonjakan['status_verifikasi']);

        // DATA-02: Confidence OCR rendah < 70% (67%)
        $confRendah = $ocrService->evaluateReadingAnomaly(1713, 1725, 67.0, 'Dihuni');
        $this->assertSame('ter_flag', $confRendah['status_verifikasi']);

        // DATA-03: Meteran mundur (1726 -> 1700 = -26 m³)
        $mundur = $ocrService->evaluateReadingAnomaly(1726, 1700, 95.0, 'Dihuni');
        $this->assertSame('ter_flag', $mundur['status_verifikasi']);

        // DATA-04: Normal 14 m³ (1739 -> 1753)
        $normal = $ocrService->evaluateReadingAnomaly(1739, 1753, 96.0, 'Dihuni');
        $this->assertSame('valid', $normal['status_verifikasi']);
        $this->assertSame(14.0, $normal['pemakaian']);

        // DATA-06: Pemakaian nol pada unit dihuni
        $nol = $ocrService->evaluateReadingAnomaly(1550, 1550, 88.0, 'Dihuni');
        $this->assertSame('ter_flag', $nol['status_verifikasi']);

        // Validasi Geofencing GPS Unit (Titik unit: -4.007468, 119.661131 | Radius: 50 meter)
        $dalamRadius = $ocrService->validateGpsLocation(-4.007500, 119.661200, -4.007468, 119.661131, 50.0);
        $this->assertTrue($dalamRadius['is_valid']);
        $this->assertLessThanOrEqual(50.0, $dalamRadius['distance_meters']);

        // Di luar radius (> 50 meter dari titik unit)
        $luarRadius = $ocrService->validateGpsLocation(-4.020000, 119.670000, -4.007468, 119.661131, 50.0);
        $this->assertFalse($luarRadius['is_valid']);
        $this->assertGreaterThan(50.0, $luarRadius['distance_meters']);

        // GPS tidak aktif (null)
        $tanpaGps = $ocrService->validateGpsLocation(null, null);
        $this->assertFalse($tanpaGps['is_valid']);
    }

    public function test_batch_processing_tarif_progresif_and_batch_billing(): void
    {
        $batchService = app(BatchTagihanService::class);

        // DATA-04: 14 m³ -> (10 × 2000) + (4 × 3000) + 11.000 admin = Rp 43.000
        $hasil14 = $batchService->hitungTagihanProgresif(14);
        $this->assertSame(43000.0, $hasil14['total_tagihan']);

        // 25 m³ -> Blok 1 (10 × 2000) + Blok 2 (10 × 3000) + Blok 3 (5 × 5000) + 11.000 admin = Rp 86.000
        $hasil25 = $batchService->hitungTagihanProgresif(25);
        $this->assertSame(86000.0, $hasil25['total_tagihan']);

        $user = User::factory()->create();
        $blok = BlokHunian::create(['nama_blok' => 'Blok A', 'keterangan_wilayah' => 'Sektor A']);
        $unit = UnitHunian::create([
            'blok_id' => $blok->id,
            'nomor_rumah' => 'A-05',
            'nama_pemilik' => 'Nur Hidayah',
            'nomor_wa' => '081234567804',
            'status_huni' => 'Dihuni',
        ]);

        TarifAir::create([
            'blok_konsumsi' => '0 - 10 m³',
            'batas_bawah' => 0,
            'batas_atas' => 10,
            'tarif_per_m3' => 2000,
        ]);

        BacaanMeter::create([
            'unit_id' => $unit->id,
            'user_id' => $user->id,
            'foto_meter' => 'sample.jpg',
            'bulan' => 11,
            'tahun' => 2026,
            'angka_meter_bulanlalu' => 1739,
            'angka_meter' => 1753,
            'status_verifikasi' => 'valid',
            'confidence_score' => 96.0,
        ]);

        $batchService = app(BatchTagihanService::class);
        $result = $batchService->executeBatchBilling('2026-11');

        $this->assertSame(1, $result['processed_count']);
        $this->assertDatabaseHas('tagihans', [
            'unit_id' => $unit->id,
            'priode' => '2026-11',
            'volume_pemakaian' => 14,
            'total_tagihan' => 43000,
        ]);
    }

    public function test_cyber_security_rsa_signature_and_qr_verification_detects_tampering(): void
    {
        $user = User::factory()->create();
        $blok = BlokHunian::create(['nama_blok' => 'Blok A', 'keterangan_wilayah' => 'Sektor A']);
        $unit = UnitHunian::create([
            'blok_id' => $blok->id,
            'nomor_rumah' => 'A-12',
            'nama_pemilik' => 'Budi Santoso',
            'nomor_wa' => '081234567801',
            'status_huni' => 'Dihuni',
        ]);
        $tarif = TarifAir::create([
            'blok_konsumsi' => '0 - 10 m³',
            'batas_bawah' => 0,
            'batas_atas' => 10,
            'tarif_per_m3' => 2000,
        ]);
        $bacaan = BacaanMeter::create([
            'unit_id' => $unit->id,
            'user_id' => $user->id,
            'foto_meter' => 'sample.jpg',
            'bulan' => 10,
            'tahun' => 2026,
            'angka_meter_bulanlalu' => 4210,
            'angka_meter' => 4224,
            'status_verifikasi' => 'valid',
        ]);
        $tagihan = Tagihan::create([
            'unit_id' => $unit->id,
            'bacaan_meter_id' => $bacaan->id,
            'tarif_id' => $tarif->id,
            'priode' => '2026-10',
            'volume_pemakaian' => 14,
            'total_tagihan' => 32000,
            'status_bayar' => 'lunas',
            'tgl_terbit' => '2026-10-01',
            'tgl_jatuh_tempo' => '2026-10-20',
        ]);
        $pembayaran = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'jumlah_bayar' => 32000,
            'metode_bayar' => 'Tunai',
            'tgl_bayar' => '2026-10-05',
            'status_pembayaran' => 'Sukses',
        ]);

        $rsaService = app(RsaSignatureService::class);
        $qrVerifyService = app(QrVerificationService::class);

        $issued = $rsaService->issueReceiptSignature($pembayaran);
        $this->assertNotEmpty($issued['hash_sha256']);
        $this->assertNotEmpty($issued['signature_rsa']);

        // Verifikasi nota asli
        $verifyValid = $qrVerifyService->verifyScannedReceipt($pembayaran->id, $issued['hash_sha256']);
        $this->assertTrue($verifyValid['is_authentic']);
        $this->assertSame('NOTA ASLI & SAH (LUNAS)', $verifyValid['status_label']);
        $this->assertSame('A-12 - Budi S.', $verifyValid['data_nota']['unit_penghuni']);

        // Manipulasi nominal tagihan di database setelah ditandatangani -> harus terdeteksi TIDAK VALID
        $tagihan->update(['total_tagihan' => 15000]);
        $verifyTampered = $qrVerifyService->verifyScannedReceipt($pembayaran->id, $issued['hash_sha256']);
        $this->assertFalse($verifyTampered['is_authentic']);
    }

    public function test_public_verification_page_from_scanned_qr_shows_authentic_and_tampered_status(): void
    {
        $user = User::factory()->create();
        $blok = BlokHunian::create(['nama_blok' => 'Blok A', 'keterangan_wilayah' => 'Sektor A']);
        $unit = UnitHunian::create([
            'blok_id' => $blok->id,
            'nomor_rumah' => 'A-12',
            'nama_pemilik' => 'Budi Santoso',
            'nomor_wa' => '081234567801',
            'status_huni' => 'Dihuni',
        ]);
        $tarif = TarifAir::create(['blok_konsumsi' => '0 - 10 m³', 'batas_bawah' => 0, 'batas_atas' => 10, 'tarif_per_m3' => 2000]);
        $bacaan = BacaanMeter::create([
            'unit_id' => $unit->id,
            'user_id' => $user->id,
            'foto_meter' => 'sample.jpg',
            'bulan' => 10,
            'tahun' => 2026,
            'angka_meter_bulanlalu' => 4210,
            'angka_meter' => 4224,
            'status_verifikasi' => 'valid',
        ]);
        $tagihan = Tagihan::create([
            'unit_id' => $unit->id,
            'bacaan_meter_id' => $bacaan->id,
            'tarif_id' => $tarif->id,
            'priode' => '2026-10',
            'volume_pemakaian' => 14,
            'total_tagihan' => 32000,
            'status_bayar' => 'lunas',
            'tgl_terbit' => '2026-10-01',
            'tgl_jatuh_tempo' => '2026-10-20',
        ]);
        $pembayaran = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'jumlah_bayar' => 32000,
            'metode_bayar' => 'Tunai',
            'tgl_bayar' => '2026-10-05',
            'status_pembayaran' => 'Sukses',
        ]);

        $issued = app(RsaSignatureService::class)->issueReceiptSignature($pembayaran);
        $scannedPath = parse_url($issued['qr_payload'], PHP_URL_PATH).'?'.parse_url($issued['qr_payload'], PHP_URL_QUERY);

        $this->get($scannedPath)
            ->assertOk()
            ->assertSee('NOTA ASLI &amp; SAH', false)
            ->assertSee('Budi S.');

        $tagihan->update(['total_tagihan' => 15000]);

        $this->get($scannedPath)
            ->assertOk()
            ->assertSee('NOTA TIDAK VALID')
            ->assertDontSee('Budi S.');
    }
}
