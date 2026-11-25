<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\Tagihan;
use App\Models\TarifAir;
use App\Models\UnitHunian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuratTunggakanPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'petugas']);
    }

    public function test_admin_can_download_surat_peringatan_pdf_for_unit_with_four_or_more_unpaid_months(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $petugas = User::factory()->create();
        $petugas->assignRole('petugas');

        $unit = $this->createUnitWithUnpaidMonths('B-11', 'Rina Wulandari', $petugas, [
            '2026-06',
            '2026-07',
            '2026-08',
            '2026-09',
            '2026-10',
            '2026-11',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.surat-tunggakan.cetak', $unit->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString(
            'Surat_Peringatan_B-11.pdf',
            (string) $response->headers->get('content-disposition')
        );
        $this->assertStringStartsWith('%PDF-', (string) $response->getContent());

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
        ]);
        $this->assertStringContainsString(
            'Unit B-11 (6 bulan)',
            (string) AuditLog::query()->latest('id')->value('aktivitas')
        );
    }

    public function test_admin_cannot_download_surat_peringatan_for_unit_with_fewer_than_four_unpaid_months(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $petugas = User::factory()->create();
        $petugas->assignRole('petugas');

        $unit = $this->createUnitWithUnpaidMonths('A-05', 'Nur Hidayah', $petugas, [
            '2026-09',
            '2026-10',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.surat-tunggakan.cetak', $unit->id));

        $response->assertRedirect(route('admin.surat-tunggakan'));
        $response->assertSessionHas('error', 'Unit tidak memenuhi kriteria tunggakan >= 4 bulan.');
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_petugas_receives_forbidden_when_accessing_surat_tunggakan_routes(): void
    {
        $petugas = User::factory()->create();
        $petugas->assignRole('petugas');

        $unit = $this->createUnitWithUnpaidMonths('B-11', 'Rina Wulandari', $petugas, [
            '2026-06',
            '2026-07',
            '2026-08',
            '2026-09',
        ]);

        $this->actingAs($petugas)
            ->get(route('admin.surat-tunggakan'))
            ->assertForbidden();

        $this->actingAs($petugas)
            ->get(route('admin.surat-tunggakan.cetak', $unit->id))
            ->assertForbidden();
    }

    /**
     * @param  array<int, string>  $periods
     */
    private function createUnitWithUnpaidMonths(string $nomorRumah, string $namaPemilik, User $petugas, array $periods): UnitHunian
    {
        $blok = BlokHunian::query()->firstOrCreate(['nama_blok' => 'Blok B']);
        $tarif = TarifAir::query()->firstOrCreate(
            ['blok_konsumsi' => '0 - 10 m³'],
            ['batas_bawah' => 0, 'batas_atas' => 10, 'tarif_per_m3' => 2000]
        );

        $unit = UnitHunian::query()->create([
            'blok_id' => $blok->id,
            'nomor_rumah' => $nomorRumah,
            'nama_pemilik' => $namaPemilik,
            'nomor_wa' => '081200000001',
            'status_huni' => 'Dihuni',
            'radius_toleransi' => 50,
        ]);

        $meter = 1000;
        foreach ($periods as $periode) {
            [$tahun, $bulan] = explode('-', $periode);
            $bacaan = BacaanMeter::query()->create([
                'unit_id' => $unit->id,
                'user_id' => $petugas->id,
                'foto_meter' => 'meter-photos/test.jpg',
                'bulan' => (int) $bulan,
                'tahun' => (int) $tahun,
                'angka_meter_bulanlalu' => $meter,
                'angka_meter' => $meter + 15,
                'status_verifikasi' => 'valid',
                'gps_unverified' => false,
                'confidence_score' => 98.0,
                'is_ocr_corrected' => false,
            ]);

            Tagihan::query()->create([
                'unit_id' => $unit->id,
                'bacaan_meter_id' => $bacaan->id,
                'tarif_id' => $tarif->id,
                'priode' => $periode,
                'volume_pemakaian' => 15,
                'denda' => 0,
                'biaya_admin' => 0,
                'jumlah_tunggakan' => 0,
                'total_tagihan' => 35000,
                'status_bayar' => 'belum_lunas',
                'tgl_terbit' => "{$periode}-01",
                'tgl_jatuh_tempo' => "{$periode}-20",
            ]);

            $meter += 15;
        }

        return $unit;
    }
}
