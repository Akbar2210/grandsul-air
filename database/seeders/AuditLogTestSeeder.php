<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuditLogTestSeeder extends Seeder
{
    /**
     * Membuat data uji khusus Audit Log (DU-045 s.d. DU-047) untuk TC_053 s/d TC_056:
     * - Akun Petugas 'Rahmat' (rahmat@grandsul.web.id)
     * - Log dalam rentang 01 Okt 2026 - 14 Okt 2026 (TC_053 & TC_054)
     * - Log spesifik Rahmat (IP 103.28.12.46): 'Mengirim pencatatan meter unit A-12 (OCR conf. 71%)'
     * - Log pembanding di luar rentang (25 Okt 2026) agar filter tanggal terbukti menyaring akurat
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $petugasRole = Role::firstOrCreate(['name' => 'petugas']);

        $admin = User::query()->where('email', 'admin@grandsul.web.id')->first()
            ?? User::query()->role('admin')->first();
        if ($admin) {
            $admin->assignRole($adminRole);
        }

        $rahmat = User::query()->firstOrCreate(
            ['email' => 'rahmat@grandsul.web.id'],
            [
                'name' => 'Rahmat',
                'password' => Hash::make('password123'),
            ]
        );
        $rahmat->name = 'Rahmat';
        $rahmat->save();
        $rahmat->syncRoles([$petugasRole]);

        $entries = [
            // DU-045 (TC_053 & TC_054): Log spesifik Rahmat dalam rentang 01-14 Okt 2026
            [
                'user_id' => $rahmat->id,
                'aktivitas' => 'Mengirim pencatatan meter unit A-12 (OCR conf. 71%)',
                'ip_address' => '103.28.12.46',
                'timestamp' => Carbon::parse('2026-10-12 09:15:22'),
            ],
            // Log lain milik Rahmat (aktivitas berbeda) untuk menguji filter kombinasi User + Aktivitas (TC_054)
            [
                'user_id' => $rahmat->id,
                'aktivitas' => 'Login ke sistem',
                'ip_address' => '103.28.12.46',
                'timestamp' => Carbon::parse('2026-10-12 08:00:10'),
            ],
            // DU-046 (TC_053): Log Admin dalam rentang 01-14 Okt 2026
            [
                'user_id' => $admin?->id ?? $rahmat->id,
                'aktivitas' => 'Memproses tagihan batch periode Okt 2026 (700 unit)',
                'ip_address' => '103.28.12.40',
                'timestamp' => Carbon::parse('2026-10-10 14:20:05'),
            ],
            // DU-047 (TC_053): Log Admin dalam rentang 01-14 Okt 2026
            [
                'user_id' => $admin?->id ?? $rahmat->id,
                'aktivitas' => 'Memvalidasi data ter-flag unit A-12',
                'ip_address' => '103.28.12.41',
                'timestamp' => Carbon::parse('2026-10-13 10:05:41'),
            ],
            // Pembanding di luar rentang 01-14 Okt 2026 (25 Okt 2026)
            [
                'user_id' => $admin?->id ?? $rahmat->id,
                'aktivitas' => 'Mencatat pembayaran PAY-2610-0031 (Transfer)',
                'ip_address' => '103.28.12.49',
                'timestamp' => Carbon::parse('2026-10-25 11:30:00'),
            ],
        ];

        foreach ($entries as $entry) {
            AuditLog::query()->updateOrCreate(
                [
                    'user_id' => $entry['user_id'],
                    'aktivitas' => $entry['aktivitas'],
                    'timestamp' => $entry['timestamp'],
                ],
                [
                    'ip_address' => $entry['ip_address'],
                ]
            );
        }
    }
}
