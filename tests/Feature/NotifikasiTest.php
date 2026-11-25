<?php

namespace Tests\Feature;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotifikasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'petugas']);
    }

    public function test_admin_can_receive_and_mark_notification_as_read(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $older = Notifikasi::query()->create([
            'user_id' => $admin->id,
            'judul' => 'Batch Tagihan Massal Selesai',
            'pesan' => 'Batch selesai diproses.',
            'tipe' => 'batch',
            'link' => '/admin/tagihan',
            'status_terkirim' => true,
            'is_read' => false,
            'tgl_kirim' => now()->subMinutes(10),
        ]);

        $newer = Notifikasi::query()->create([
            'user_id' => $admin->id,
            'judul' => 'Data Meter Ter-flag Anomali',
            'pesan' => 'Unit A-03 terdeteksi anomali.',
            'tipe' => 'flag',
            'link' => '/admin/terflag',
            'status_terkirim' => true,
            'is_read' => false,
            'tgl_kirim' => now(),
        ]);

        $listResponse = $this->actingAs($admin)
            ->getJson(route('admin.notifikasi.unread-list'));

        $listResponse->assertOk();
        $listResponse->assertJsonPath('unread_count', 2);
        $listResponse->assertJsonPath('notifications.0.id', $newer->id);
        $listResponse->assertJsonPath('notifications.1.id', $older->id);

        $this->actingAs($admin)
            ->postJson(route('admin.notifikasi.read', $newer->id))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertTrue((bool) $newer->fresh()->is_read);
    }

    public function test_petugas_receives_rejection_notification_and_can_mark_it_read(): void
    {
        $petugas = User::factory()->create();
        $petugas->assignRole('petugas');

        $notif = Notifikasi::query()->create([
            'user_id' => $petugas->id,
            'judul' => 'Pembacaan unit D-14 ditolak',
            'pesan' => 'Alasan penolakan: Foto buram.',
            'tipe' => 'flag',
            'link' => '/petugas/pindai?unit_id=6&blok_id=4',
            'status_terkirim' => true,
            'is_read' => false,
            'tgl_kirim' => now(),
        ]);

        $this->actingAs($petugas)
            ->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('Pembacaan unit D-14 ditolak')
            ->assertSee('Foto buram');

        $this->actingAs($petugas)
            ->postJson(route('petugas.notifikasi.read', $notif->id))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'link' => '/petugas/pindai?unit_id=6&blok_id=4',
            ]);

        $this->assertTrue((bool) $notif->fresh()->is_read);
    }

    public function test_new_petugas_sees_empty_notification_state(): void
    {
        $petugasBaru = User::factory()->create();
        $petugasBaru->assignRole('petugas');

        $this->actingAs($petugasBaru)
            ->getJson(route('petugas.notifikasi.unread-list'))
            ->assertOk()
            ->assertJsonPath('unread_count', 0)
            ->assertJsonCount(0, 'notifications');

        $this->actingAs($petugasBaru)
            ->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('Belum ada notifikasi saat ini');
    }

    public function test_petugas_cannot_read_admin_notification_across_users(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $petugas = User::factory()->create();
        $petugas->assignRole('petugas');

        $adminNotif = Notifikasi::query()->create([
            'user_id' => $admin->id,
            'judul' => 'Data Meter Ter-flag Anomali',
            'pesan' => 'Khusus Admin.',
            'tipe' => 'flag',
            'link' => '/admin/terflag',
            'status_terkirim' => true,
            'is_read' => false,
            'tgl_kirim' => now(),
        ]);

        // Rute /notifikasi/{id}/read (GET & POST)
        $this->actingAs($petugas)
            ->get(route('notifikasi.read', $adminNotif->id))
            ->assertForbidden();

        $this->actingAs($petugas)
            ->postJson(route('notifikasi.read', $adminNotif->id))
            ->assertForbidden();

        // Rute /petugas/notifikasi/{id}/read
        $this->actingAs($petugas)
            ->postJson(route('petugas.notifikasi.read', $adminNotif->id))
            ->assertForbidden();

        // Rute /admin/notifikasi/{id}/read
        $this->actingAs($petugas)
            ->postJson(route('admin.notifikasi.read', $adminNotif->id))
            ->assertForbidden();

        $this->assertFalse((bool) $adminNotif->fresh()->is_read);
    }
}
