<?php

namespace Tests\Feature;

use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TarifAir;
use App\Models\UnitHunian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LaporanHarianDanBulananTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_harian_and_bulanan_views_and_pdf_export_work(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::create([
            'name' => 'Admin Pengelola Air',
            'email' => 'admin@grandsul.web.id',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $blok = BlokHunian::create(['nama_blok' => 'Blok A']);
        $unit = UnitHunian::create([
            'blok_id' => $blok->id,
            'nomor_rumah' => 'A-01',
            'nama_pemilik' => 'Warga A 01',
            'nomor_wa' => '081234567890',
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
            'user_id' => $admin->id,
            'foto_meter' => 'sample.jpg',
            'bulan' => 11,
            'tahun' => 2026,
            'angka_meter_bulanlalu' => 1000,
            'angka_meter' => 1010,
            'status_verifikasi' => 'valid',
        ]);
        $tagihan = Tagihan::create([
            'unit_id' => $unit->id,
            'bacaan_meter_id' => $bacaan->id,
            'tarif_id' => $tarif->id,
            'priode' => '2026-11',
            'volume_pemakaian' => 10,
            'total_tagihan' => 31000,
            'status_bayar' => 'lunas',
            'tgl_terbit' => '2026-11-01',
            'tgl_jatuh_tempo' => '2026-11-20',
        ]);
        Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'jumlah_bayar' => 31000,
            'metode_bayar' => 'Tunai',
            'tgl_bayar' => '2026-11-28',
            'status_pembayaran' => 'Sukses',
        ]);

        // 1. Mode Bulanan
        $this->actingAs($admin)
            ->get(route('admin.laporan', ['mode' => 'bulanan', 'bulan' => 11, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('Cetak Laporan')
            ->assertDontSee('Cetak Layar')
            ->assertSee('mode=bulanan')
            ->assertSee('LAPORAN REKAPITULASI TAGIHAN');

        // 2. Mode Harian
        $this->actingAs($admin)
            ->get(route('admin.laporan', ['mode' => 'harian', 'tanggal' => '2026-11-28']))
            ->assertOk()
            ->assertSee('Cetak Laporan')
            ->assertSee('mode=harian')
            ->assertSee('LAPORAN HARIAN TRANSAKSI');

        // 3. Export PDF Harian berhasil saat ada data
        $pdfHarian = $this->actingAs($admin)
            ->get(route('admin.laporan.pdf', ['mode' => 'harian', 'tanggal' => '2026-11-28']));
        $pdfHarian->assertOk();
        $this->assertStringContainsString('Laporan_Harian_2026-11-28.pdf', (string) $pdfHarian->headers->get('content-disposition'));

        // 4. Export PDF Bulanan berhasil saat ada data
        $pdfBulanan = $this->actingAs($admin)
            ->get(route('admin.laporan.pdf', ['mode' => 'bulanan', 'bulan' => 11, 'tahun' => 2026]));
        $pdfBulanan->assertOk();
        $this->assertStringContainsString('Laporan_Bulanan_', (string) $pdfBulanan->headers->get('content-disposition'));
    }
}
