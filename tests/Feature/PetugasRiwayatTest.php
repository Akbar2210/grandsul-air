<?php

namespace Tests\Feature;

use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\UnitHunian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PetugasRiwayatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'petugas']);
    }

    public function test_petugas_only_sees_own_valid_records_and_gets_403_on_other_petugas_detail(): void
    {
        $petugasA = User::factory()->create(['name' => 'Rahmat']);
        $petugasA->assignRole('petugas');

        $petugasB = User::factory()->create(['name' => 'Petugas Lain']);
        $petugasB->assignRole('petugas');

        $blok = BlokHunian::query()->create(['nama_blok' => 'Blok A']);
        $unitA = UnitHunian::query()->create([
            'blok_id' => $blok->id,
            'nomor_rumah' => 'A-12',
            'nama_pemilik' => 'Budi Santoso',
            'nomor_wa' => '081200000012',
            'status_huni' => 'Dihuni',
            'radius_toleransi' => 50,
        ]);
        $unitB = UnitHunian::query()->create([
            'blok_id' => $blok->id,
            'nomor_rumah' => 'B-99',
            'nama_pemilik' => 'Warga Lain',
            'nomor_wa' => '081200000099',
            'status_huni' => 'Dihuni',
            'radius_toleransi' => 50,
        ]);

        $now = now();

        BacaanMeter::query()->create([
            'unit_id' => $unitA->id,
            'user_id' => $petugasA->id,
            'foto_meter' => 'bacaan_meters/sample.jpg',
            'bulan' => $now->month,
            'tahun' => $now->year,
            'angka_meter_bulanlalu' => 4200,
            'angka_meter' => 4224,
            'status_verifikasi' => 'valid',
            'gps_unverified' => false,
        ]);

        $otherRecord = BacaanMeter::query()->create([
            'unit_id' => $unitB->id,
            'user_id' => $petugasB->id,
            'foto_meter' => 'bacaan_meters/other.jpg',
            'bulan' => $now->month,
            'tahun' => $now->year,
            'angka_meter_bulanlalu' => 100,
            'angka_meter' => 120,
            'status_verifikasi' => 'valid',
            'gps_unverified' => false,
        ]);

        $listResponse = $this->actingAs($petugasA)->get(route('petugas.riwayat', [
            'bulan' => $now->month,
            'tahun' => $now->year,
            'status' => 'valid',
        ]));

        $listResponse->assertOk();
        $listResponse->assertSee('A-12');
        $listResponse->assertDontSee('B-99');

        $this->actingAs($petugasA)
            ->get(route('petugas.riwayat.show', $otherRecord->id))
            ->assertForbidden();
    }

    public function test_petugas_can_view_own_detail_with_photo_gps_and_rejection_reason(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('bacaan_meters/unit-a12.jpg', 'fake-image-bytes');

        $petugas = User::factory()->create(['name' => 'Rahmat']);
        $petugas->assignRole('petugas');

        $blok = BlokHunian::query()->create(['nama_blok' => 'Blok A']);
        $unit = UnitHunian::query()->create([
            'blok_id' => $blok->id,
            'nomor_rumah' => 'A-12',
            'nama_pemilik' => 'Budi Santoso',
            'nomor_wa' => '081200000012',
            'status_huni' => 'Dihuni',
            'radius_toleransi' => 50,
        ]);

        $record = BacaanMeter::query()->create([
            'unit_id' => $unit->id,
            'user_id' => $petugas->id,
            'foto_meter' => 'bacaan_meters/unit-a12.jpg',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'angka_meter_bulanlalu' => 4200,
            'angka_meter' => 4224,
            'latitude' => -4.007468,
            'longitude' => 119.661131,
            'status_verifikasi' => 'ditolak',
            'alasan_penolakan' => 'Foto buram, harap ulangi pemotretan.',
            'gps_unverified' => false,
            'confidence_score' => 71.5,
        ]);

        $response = $this->actingAs($petugas)
            ->get(route('petugas.riwayat.show', $record->id));

        $response->assertOk();
        $response->assertSee('Foto Bukti Meteran');
        $response->assertSee('storage/bacaan_meters/unit-a12.jpg');
        $response->assertSee('A-12');
        $response->assertSee('Budi Santoso');
        $response->assertSee('4.224');
        $response->assertSee('-4.007468');
        $response->assertSee('119.661131');
        $response->assertSee('Foto buram, harap ulangi pemotretan.');
    }

    public function test_petugas_search_for_unknown_unit_shows_empty_state_message(): void
    {
        $petugas = User::factory()->create(['name' => 'Rahmat']);
        $petugas->assignRole('petugas');

        $response = $this->actingAs($petugas)->get(route('petugas.riwayat', [
            'search' => 'WM-9999999',
        ]));

        $response->assertOk();
        $response->assertSee('Data tidak ditemukan');
    }
}
