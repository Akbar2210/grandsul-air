<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminMasterPetugasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan role admin dan petugas tersedia
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'petugas']);
    }

    public function test_admin_can_view_petugas_in_master_data(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@grandsul.web.id'],
            ['name' => 'Admin Test', 'password' => Hash::make('password')]
        );
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.master', ['tab' => 'petugas']));

        $response->assertStatus(200);
        $response->assertSee('Daftar Petugas Lapangan');
        $response->assertSee('Tambah Petugas');
    }

    public function test_admin_can_store_new_petugas_with_zona(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@grandsul.web.id'],
            ['name' => 'Admin Test', 'password' => Hash::make('password')]
        );
        $admin->assignRole('admin');

        $email = 'petugas_unit_test_'.time().'@grandsul.web.id';

        $response = $this->actingAs($admin)->post(route('admin.master.petugas.store'), [
            'name' => 'Petugas Baru Test',
            'email' => $email,
            'password' => 'secret123',
            'zona_type' => 'spesifik',
            'zona_blok' => ['Blok A', 'Blok B'],
        ]);

        $response->assertRedirect(route('admin.master', ['tab' => 'petugas']));
        $this->assertDatabaseHas('users', [
            'name' => 'Petugas Baru Test',
            'email' => $email,
            'zona_penugasan' => 'Blok A, Blok B',
        ]);

        $user = User::where('email', $email)->first();
        $this->assertTrue($user->hasRole('petugas'));
        $this->assertTrue(Hash::check('secret123', $user->password));

        // Bersihkan
        $user->delete();
    }

    public function test_admin_can_update_petugas_username_password_and_zona(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@grandsul.web.id'],
            ['name' => 'Admin Test', 'password' => Hash::make('password')]
        );
        $admin->assignRole('admin');

        $petugas = User::create([
            'name' => 'Petugas Update Test',
            'email' => 'petugas_upd_'.time().'@grandsul.web.id',
            'password' => Hash::make('oldpassword'),
            'zona_penugasan' => 'Semua Blok (A, B, C, D, E, F)',
        ]);
        $petugas->assignRole('petugas');

        $newEmail = 'petugas_upd_new_'.time().'@grandsul.web.id';

        $response = $this->actingAs($admin)->post(route('admin.master.petugas.update', $petugas->id), [
            'name' => 'Petugas Update Sukses',
            'email' => $newEmail,
            'password' => 'newpassword123',
            'zona_type' => 'spesifik',
            'zona_blok' => ['Blok C', 'Blok D'],
        ]);

        $response->assertRedirect(route('admin.master', ['tab' => 'petugas']));
        $petugas->refresh();

        $this->assertEquals('Petugas Update Sukses', $petugas->name);
        $this->assertEquals($newEmail, $petugas->email);
        $this->assertEquals('Blok C, Blok D', $petugas->zona_penugasan);
        $this->assertTrue(Hash::check('newpassword123', $petugas->password));

        // Bersihkan
        $petugas->delete();
    }

    public function test_admin_cannot_delete_last_petugas(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@grandsul.web.id'],
            ['name' => 'Admin Test', 'password' => Hash::make('password')]
        );
        $admin->assignRole('admin');

        // Pastikan hanya ada 1 petugas di sistem
        $petugasCount = User::role('petugas')->count();
        if ($petugasCount === 1) {
            $singlePetugas = User::role('petugas')->first();
            $response = $this->actingAs($admin)->delete(route('admin.master.petugas.destroy', $singlePetugas->id));
            $response->assertRedirect(route('admin.master', ['tab' => 'petugas']));
            $response->assertSessionHas('error');
            $this->assertDatabaseHas('users', ['id' => $singlePetugas->id]);
        }
    }

    public function test_admin_can_toggle_petugas_status_active_and_inactive(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@grandsul.web.id'],
            ['name' => 'Admin Test', 'password' => Hash::make('password')]
        );
        $admin->assignRole('admin');

        $petugas = User::create([
            'name' => 'Petugas Toggle Test',
            'email' => 'petugas_toggle_'.time().'@grandsul.web.id',
            'password' => Hash::make('password123'),
            'zona_penugasan' => 'Semua Blok (A, B, C, D, E, F)',
            'is_active' => true,
        ]);
        $petugas->assignRole('petugas');

        // Toggle to inactive
        $response = $this->actingAs($admin)->post(route('admin.master.petugas.toggle-status', $petugas->id));
        $response->assertRedirect(route('admin.master', ['tab' => 'petugas']));
        $petugas->refresh();
        $this->assertFalse($petugas->is_active);

        // Toggle back to active
        $response = $this->actingAs($admin)->post(route('admin.master.petugas.toggle-status', $petugas->id));
        $response->assertRedirect(route('admin.master', ['tab' => 'petugas']));
        $petugas->refresh();
        $this->assertTrue($petugas->is_active);

        $petugas->delete();
    }

    public function test_admin_can_store_new_blok(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@grandsul.web.id'],
            ['name' => 'Admin Test', 'password' => Hash::make('password')]
        );
        $admin->assignRole('admin');

        $blokName = 'Blok Test '.time();

        $response = $this->actingAs($admin)->post(route('admin.master.blok.store'), [
            'nama_blok' => $blokName,
            'keterangan_wilayah' => 'Perluasan Wilayah Uji',
        ]);

        $response->assertRedirect(route('admin.master', ['tab' => 'blok']));
        $this->assertDatabaseHas('blok_hunians', [
            'nama_blok' => $blokName,
            'keterangan_wilayah' => 'Perluasan Wilayah Uji',
        ]);
    }
}
