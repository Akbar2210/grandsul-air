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

class LaporanBulananTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'petugas']);
    }

    public function test_admin_can_export_monthly_report_pdf_and_log_audit(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $petugas = User::factory()->create();
        $petugas->assignRole('petugas');

        $blokA = BlokHunian::query()->create(['nama_blok' => 'Blok A']);
        $this->seedBlockOctBill($blokA, $petugas, 'A-01', 1200, 19800000, 'lunas');
        $this->seedBlockOctBill($blokA, $petugas, 'A-02', 1210, 6300000, 'belum_lunas');

        $response = $this->actingAs($admin)->get(route('admin.laporan.pdf', [
            'bulan' => 10,
            'tahun' => 2026,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString(
            'Laporan_Bulanan_Okt2026.pdf',
            (string) $response->headers->get('content-disposition')
        );
        $this->assertStringStartsWith('%PDF-', (string) $response->getContent());

        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id]);
        $this->assertStringContainsString(
            'Laporan_Bulanan_Okt2026.pdf',
            (string) AuditLog::query()->latest('id')->value('aktivitas')
        );
    }

    public function test_admin_block_filter_calculates_exact_unpaid_difference(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $petugas = User::factory()->create();
        $petugas->assignRole('petugas');

        $blokA = BlokHunian::query()->create(['nama_blok' => 'Blok A']);
        $blokB = BlokHunian::query()->create(['nama_blok' => 'Blok B']);

        $this->seedBlockOctBill($blokA, $petugas, 'A-01', 1410, 19800000, 'lunas');
        $this->seedBlockOctBill($blokA, $petugas, 'A-02', 1000, 6300000, 'belum_lunas');
        $this->seedBlockOctBill($blokB, $petugas, 'B-01', 500, 5000000, 'lunas');

        $response = $this->actingAs($admin)->get(route('admin.laporan', [
            'bulan' => 10,
            'tahun' => 2026,
            'blok' => $blokA->id,
        ]));

        $response->assertOk();
        $response->assertSee('Blok A');
        $response->assertSee('2.410 m³');
        $response->assertSee('Rp 26.100.000');
        $response->assertSee('Rp 19.800.000');
        $response->assertSee('Rp 6.300.000');
        $response->assertDontSee('Rp 5.000.000');
    }

    public function test_admin_cannot_export_empty_pdf_for_future_period_without_transactions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.laporan.pdf', [
            'bulan' => 12,
            'tahun' => 2027,
        ]));

        $response->assertRedirect(route('admin.laporan', ['bulan' => 12, 'tahun' => 2027]));
        $response->assertSessionHas('error', 'Tidak ada data transaksi pada periode terpilih');
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_petugas_is_forbidden_from_laporan_and_export_pdf_endpoints(): void
    {
        $petugas = User::factory()->create();
        $petugas->assignRole('petugas');

        $this->actingAs($petugas)
            ->get(route('admin.laporan'))
            ->assertForbidden();

        $this->actingAs($petugas)
            ->get(route('admin.laporan.pdf', ['bulan' => 10, 'tahun' => 2026]))
            ->assertForbidden();
    }

    private function seedBlockOctBill(BlokHunian $blok, User $petugas, string $nomorRumah, int $vol, float $nominal, string $status): void
    {
        $tarif = TarifAir::query()->firstOrCreate(
            ['blok_konsumsi' => '0 - 10 m³'],
            ['batas_bawah' => 0, 'batas_atas' => 10, 'tarif_per_m3' => 2000]
        );

        $unit = UnitHunian::query()->create([
            'blok_id' => $blok->id,
            'nomor_rumah' => $nomorRumah,
            'nama_pemilik' => "Warga {$nomorRumah}",
            'nomor_wa' => '081200000001',
            'status_huni' => 'Dihuni',
            'radius_toleransi' => 50,
        ]);

        $bacaan = BacaanMeter::query()->create([
            'unit_id' => $unit->id,
            'user_id' => $petugas->id,
            'foto_meter' => 'meter-photos/sample.jpg',
            'bulan' => 10,
            'tahun' => 2026,
            'angka_meter_bulanlalu' => 1000,
            'angka_meter' => 1000 + $vol,
            'status_verifikasi' => 'valid',
            'gps_unverified' => false,
        ]);

        Tagihan::query()->create([
            'unit_id' => $unit->id,
            'bacaan_meter_id' => $bacaan->id,
            'tarif_id' => $tarif->id,
            'priode' => '2026-10',
            'volume_pemakaian' => $vol,
            'denda' => 0,
            'biaya_admin' => 0,
            'jumlah_tunggakan' => 0,
            'total_tagihan' => $nominal,
            'status_bayar' => $status,
            'tgl_terbit' => '2026-10-01',
            'tgl_jatuh_tempo' => '2026-10-20',
        ]);
    }
}
