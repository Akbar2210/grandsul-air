<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\UnitHunian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PetugasGpsOutOfRadiusTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_outside_gps_radius_rejects_save_shows_warning_and_writes_audit_log(): void
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

        $blok = BlokHunian::create(['nama_blok' => 'Blok F']);
        $unit = UnitHunian::create([
            'blok_id' => $blok->id,
            'nomor_rumah' => 'F-06',
            'nama_pemilik' => 'Warga F 06',
            'nomor_wa' => '081213376984',
            'status_huni' => 'Dihuni',
            'latitude' => -4.03107348,
            'longitude' => 119.62739768,
            'radius_toleransi' => 50,
        ]);

        $response = $this->actingAs($petugas)
            ->from(route('petugas.pindai', ['blok_id' => $blok->id, 'unit_id' => $unit->id]))
            ->post(route('petugas.pindai.store'), [
                'unit_id' => $unit->id,
                'angka_meter' => 12344,
                'foto_meter' => new UploadedFile(public_path('images/logo.png'), 'meter.png', 'image/png', null, true),
                // 0.001619 deg latitude ~= 180 meters north (> 50m radius)
                'latitude' => -4.02945448,
                'longitude' => 119.62739768,
                'confidence_score' => 0,
                'is_ocr_corrected' => 1,
            ]);

        $response->assertRedirect(route('petugas.pindai', ['blok_id' => $blok->id, 'unit_id' => $unit->id]));
        $response->assertSessionHas('error', function (string $message): bool {
            return str_contains($message, 'Lokasi tidak sesuai / di luar radius unit');
        });

        $this->assertDatabaseCount('bacaan_meters', 0);
        $this->assertSame(0, BacaanMeter::where('unit_id', $unit->id)->count());

        $audit = AuditLog::where('user_id', $petugas->id)->first();
        $this->assertNotNull($audit);
        $this->assertStringContainsString('F-06', $audit->aktivitas);
        $this->assertStringContainsString('Lokasi tidak sesuai / di luar radius unit', $audit->aktivitas);
    }
}
