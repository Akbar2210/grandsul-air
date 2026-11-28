<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_profile_with_optional_password_and_logs_audit(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::create([
            'name' => 'Admin Pengelola Air',
            'email' => 'admin@grandsul.web.id',
            'password' => Hash::make('oldpassword'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // 1. Update nama & email tanpa ganti password
        $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->post(route('admin.profile.update'), [
                '_profile_modal' => '1',
                'name' => 'Admin Baru',
                'email' => 'admin.baru@grandsul.web.id',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success');

        $admin->refresh();
        $this->assertSame('Admin Baru', $admin->name);
        $this->assertSame('admin.baru@grandsul.web.id', $admin->email);
        $this->assertTrue(Hash::check('oldpassword', $admin->password));

        // 2. Update password dengan konfirmasi cocok
        $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->post(route('admin.profile.update'), [
                '_profile_modal' => '1',
                'name' => 'Admin Baru',
                'email' => 'admin.baru@grandsul.web.id',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success');

        $admin->refresh();
        $this->assertTrue(Hash::check('newpassword123', $admin->password));
        $this->assertGreaterThanOrEqual(2, AuditLog::where('user_id', $admin->id)->count());
    }

    public function test_admin_profile_update_rejects_mismatched_password_confirmation(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::create([
            'name' => 'Admin Pengelola Air',
            'email' => 'admin@grandsul.web.id',
            'password' => Hash::make('oldpassword'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->post(route('admin.profile.update'), [
                '_profile_modal' => '1',
                'name' => 'Admin Pengelola Air',
                'email' => 'admin@grandsul.web.id',
                'password' => 'newpassword123',
                'password_confirmation' => 'tidaksama',
            ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasErrors('password');
    }
}
