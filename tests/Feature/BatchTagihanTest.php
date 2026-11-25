<?php

namespace Tests\Feature;

use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\Tagihan;
use App\Models\TarifAir;
use App\Models\UnitHunian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BatchTagihanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'petugas']);
    }

    public function test_batch_calculates_tier_1_and_tier_2_plus_beban_for_unit_a05_and_prevents_duplicates(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $petugas = User::factory()->create();
        $petugas->assignRole('petugas');

        $blokA = BlokHunian::query()->create(['nama_blok' => 'Blok A']);
        TarifAir::query()->create([
            'blok_konsumsi' => '0 - 10 m³',
            'batas_bawah' => 0,
            'batas_atas' => 10,
            'tarif_per_m3' => 2000,
        ]);

        $unitA05 = UnitHunian::query()->create([
            'blok_id' => $blokA->id,
            'nomor_rumah' => 'A-05',
            'nama_pemilik' => 'Nur Hidayah',
            'nomor_wa' => '081200000005',
            'status_huni' => 'Dihuni',
            'radius_toleransi' => 50,
        ]);

        BacaanMeter::query()->create([
            'unit_id' => $unitA05->id,
            'user_id' => $petugas->id,
            'foto_meter' => 'meter-photos/sample.jpg',
            'bulan' => 10,
            'tahun' => 2026,
            'angka_meter_bulanlalu' => 2112,
            'angka_meter' => 2126, // 14 m³ -> (10*2000) + (4*3000) + 11000 = 43.000
            'status_verifikasi' => 'valid',
            'gps_unverified' => false,
        ]);

        // 1. Eksekusi Batch pertama (TC_026 & TC_027)
        $firstRun = $this->actingAs($admin)->post(route('admin.tagihan.batch'), [
            'periode' => '2026-10',
        ]);

        $firstRun->assertRedirect(route('admin.tagihan', ['periode' => '2026-10']));
        $firstRun->assertSessionHas('success');

        $tagihan = Tagihan::query()
            ->where('unit_id', $unitA05->id)
            ->where('priode', '2026-10')
            ->first();

        $this->assertNotNull($tagihan);
        $this->assertSame(14, (int) $tagihan->volume_pemakaian);
        $this->assertEquals(11000.0, (float) $tagihan->biaya_admin);
        $this->assertEquals(43000.0, (float) $tagihan->total_tagihan);
        $this->assertSame('belum_lunas', $tagihan->status_bayar);

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $admin->id,
            'judul' => 'Batch Tagihan Massal Selesai',
        ]);

        // 2. Eksekusi Batch kedua pada periode sama harus ditolak tanpa duplikasi (TC_028)
        $secondRun = $this->actingAs($admin)->post(route('admin.tagihan.batch'), [
            'periode' => '2026-10',
        ]);

        $secondRun->assertRedirect(route('admin.tagihan', ['periode' => '2026-10']));
        $secondRun->assertSessionHas('error');
        $this->assertStringContainsString(
            'sudah terbit sebelumnya',
            (string) session('error')
        );
        $this->assertSame(1, Tagihan::query()->where('priode', '2026-10')->count());
    }
}
