<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'petugas']);
    }

    public function test_admin_can_filter_audit_log_by_date_range_and_delete_is_rejected(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Pengelola Air']);
        $admin->assignRole('admin');

        $rahmat = User::factory()->create(['name' => 'Rahmat']);
        $rahmat->assignRole('petugas');

        $inRange = AuditLog::query()->create([
            'user_id' => $rahmat->id,
            'aktivitas' => 'Mengirim pencatatan meter unit A-12 (OCR conf. 71%)',
            'ip_address' => '103.28.12.46',
            'timestamp' => Carbon::parse('2026-10-12 09:15:22'),
        ]);

        AuditLog::query()->create([
            'user_id' => $admin->id,
            'aktivitas' => 'Mencatat pembayaran PAY-2610-0031 (Transfer)',
            'ip_address' => '103.28.12.49',
            'timestamp' => Carbon::parse('2026-10-25 11:30:00'),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.audit', [
            'tanggal_awal' => '2026-10-01',
            'tanggal_akhir' => '2026-10-14',
        ]));

        $response->assertOk();
        $response->assertSee('Mengirim pencatatan meter unit A-12 (OCR conf. 71%)');
        $response->assertSee('103.28.12.46');
        $response->assertDontSee('Mencatat pembayaran PAY-2610-0031 (Transfer)');

        // Immutable check: HTTP DELETE ke rute log ditolak (405 / 403)
        $this->actingAs($admin)
            ->delete('/admin/audit')
            ->assertStatus(405);

        $this->actingAs($admin)
            ->delete("/admin/audit/{$inRange->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('audit_logs', ['id' => $inRange->id]);
    }

    public function test_admin_can_filter_audit_log_by_user_and_specific_activity(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Pengelola Air']);
        $admin->assignRole('admin');

        $rahmat = User::factory()->create(['name' => 'Rahmat']);
        $rahmat->assignRole('petugas');

        AuditLog::query()->create([
            'user_id' => $rahmat->id,
            'aktivitas' => 'Mengirim pencatatan meter unit A-12 (OCR conf. 71%)',
            'ip_address' => '103.28.12.46',
            'timestamp' => Carbon::parse('2026-10-12 09:15:22'),
        ]);

        AuditLog::query()->create([
            'user_id' => $rahmat->id,
            'aktivitas' => 'Login ke sistem',
            'ip_address' => '103.28.12.46',
            'timestamp' => Carbon::parse('2026-10-12 08:00:10'),
        ]);

        AuditLog::query()->create([
            'user_id' => $admin->id,
            'aktivitas' => 'Memproses tagihan batch periode Okt 2026 (700 unit)',
            'ip_address' => '103.28.12.40',
            'timestamp' => Carbon::parse('2026-10-10 14:20:05'),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.audit', [
            'user_id' => $rahmat->id,
            'jenis_aktivitas' => 'Mengirim pencatatan meter',
        ]));

        $response->assertOk();
        $response->assertSee('Rahmat');
        $response->assertSee('103.28.12.46');
        $response->assertSee('Mengirim pencatatan meter unit A-12 (OCR conf. 71%)');
        $response->assertDontSee('Memproses tagihan batch periode Okt 2026 (700 unit)');
    }

    public function test_admin_gets_validation_error_for_reversed_date_range(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        AuditLog::query()->create([
            'user_id' => $admin->id,
            'aktivitas' => 'Memproses tagihan batch periode Okt 2026 (700 unit)',
            'ip_address' => '103.28.12.40',
            'timestamp' => Carbon::parse('2026-10-10 14:20:05'),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.audit', [
            'tanggal_awal' => '2026-10-20',
            'tanggal_akhir' => '2026-10-01',
        ]));

        $response->assertOk();
        $response->assertSee('Rentang tanggal tidak valid (Tanggal akhir harus setelah tanggal awal)');
        $response->assertDontSee('Memproses tagihan batch periode Okt 2026 (700 unit)');
    }

    public function test_petugas_is_forbidden_from_accessing_audit_log_routes(): void
    {
        $petugas = User::factory()->create();
        $petugas->assignRole('petugas');

        $this->actingAs($petugas)
            ->get('/admin/audit')
            ->assertForbidden();

        $this->actingAs($petugas)
            ->get('/admin/audit-log')
            ->assertForbidden();
    }
}
