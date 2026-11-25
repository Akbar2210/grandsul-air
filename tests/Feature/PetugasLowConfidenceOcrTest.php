<?php

namespace Tests\Feature;

use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\UnitHunian;
use App\Models\User;
use App\Services\BisnisDigital\OcrMeterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PetugasLowConfidenceOcrTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_confidence_ocr_within_valid_gps_radius_saves_as_ter_flag(): void
    {
        Role::firstOrCreate(['name' => 'petugas']);

        $petugas = User::create([
            'name' => 'Hendra (Petugas Lapangan)',
            'email' => 'petugas1@grandsul.web.id',
            'password' => Hash::make('password'),
            'zona_penugasan' => 'Semua Blok (A, B, C, D, E, F)',
            'is_active' => true,
        ]);
        $petugas->assignRole('petugas');

        $blok = BlokHunian::create(['nama_blok' => 'Blok B']);
        $unit = UnitHunian::create([
            'blok_id' => $blok->id,
            'nomor_rumah' => 'B-07',
            'nama_pemilik' => 'Siti Aminah',
            'nomor_wa' => '081213376984',
            'status_huni' => 'Dihuni',
            'latitude' => -4.007468,
            'longitude' => 119.661131,
            'radius_toleransi' => 50,
        ]);

        // Bacaan bulan lalu (Oktober)
        $lastMonth = now()->subMonth();
        BacaanMeter::create([
            'unit_id' => $unit->id,
            'user_id' => $petugas->id,
            'foto_meter' => 'sample_meter.jpg',
            'bulan' => $lastMonth->month,
            'tahun' => $lastMonth->year,
            'angka_meter_bulanlalu' => 1780,
            'angka_meter' => 1800,
            'status_verifikasi' => 'valid',
            'confidence_score' => 96,
        ]);

        // Simulasikan lokasi petugas ~25 meter dari unit (-4.007468 + 0.000225 ≈ 25m)
        $response = $this->actingAs($petugas)->post(route('petugas.pindai.store'), [
            'unit_id' => $unit->id,
            'angka_meter' => 1820,
            'latitude' => -4.007243,
            'longitude' => 119.661131,
            'confidence_score' => 67,
            'is_ocr_corrected' => '0',
        ]);

        $response->assertRedirect();

        $bacaan = BacaanMeter::where('unit_id', $unit->id)
            ->where('bulan', now()->month)
            ->where('tahun', now()->year)
            ->first();

        $this->assertNotNull($bacaan);
        $this->assertEquals(1820, $bacaan->angka_meter);
        $this->assertEquals('ter_flag', $bacaan->status_verifikasi);
        $this->assertEquals(67.0, (float) $bacaan->confidence_score);
        $this->assertStringContainsString('OCR confidence rendah', (string) $bacaan->catatan_anomali);
    }

    public function test_low_confidence_ocr_on_first_reading_still_saves_as_ter_flag(): void
    {
        Role::firstOrCreate(['name' => 'petugas']);

        $petugas = User::create([
            'name' => 'Hendra (Petugas Lapangan)',
            'email' => 'petugas_first_'.time().'@grandsul.web.id',
            'password' => Hash::make('password'),
            'zona_penugasan' => 'Semua Blok (A, B, C, D, E, F)',
            'is_active' => true,
        ]);
        $petugas->assignRole('petugas');

        $blok = BlokHunian::create(['nama_blok' => 'Blok C']);
        $unit = UnitHunian::create([
            'blok_id' => $blok->id,
            'nomor_rumah' => 'C-01',
            'nama_pemilik' => 'Budi',
            'nomor_wa' => '081234567890',
            'status_huni' => 'Dihuni',
            'latitude' => -4.007468,
            'longitude' => 119.661131,
            'radius_toleransi' => 50,
        ]);

        $response = $this->actingAs($petugas)->post(route('petugas.pindai.store'), [
            'unit_id' => $unit->id,
            'angka_meter' => 12344,
            'latitude' => -4.007243,
            'longitude' => 119.661131,
            'confidence_score' => 67,
            'is_ocr_corrected' => '0',
        ]);

        $response->assertRedirect();

        $bacaan = BacaanMeter::where('unit_id', $unit->id)
            ->where('bulan', now()->month)
            ->where('tahun', now()->year)
            ->first();

        $this->assertNotNull($bacaan);
        $this->assertEquals('ter_flag', $bacaan->status_verifikasi);
        $this->assertStringContainsString('OCR confidence rendah', (string) $bacaan->catatan_anomali);
    }

    public function test_low_confidence_ocr_distorts_digits_to_reflect_blur_misread(): void
    {
        $service = new OcrMeterService;

        $misread = $service->simulateLowConfidenceOcrMisread(12344);
        $this->assertNotEquals(12344, $misread);
        $this->assertSame(12849, $misread);

        $misreadShort = $service->simulateLowConfidenceOcrMisread(1820);
        $this->assertNotEquals(1820, $misreadShort);
        $this->assertSame(1328, $misreadShort);
    }
}
