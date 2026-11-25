<?php

namespace Database\Seeders;

use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\Notifikasi;
use App\Models\UnitHunian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class NotifikasiTestSeeder extends Seeder
{
    /**
     * Membuat data uji notifikasi lengkap untuk TC_049 s/d TC_052:
     * - TC_049 & TC_052: Akun Admin (admin@grandsul.web.id) memiliki notifikasi Data Ter-flag & Batch Selesai
     * - TC_050: Akun Petugas (petugas1@grandsul.web.id) memiliki notifikasi 'Pembacaan unit D-14 ditolak' ('Foto buram')
     * - TC_051: Akun Petugas Baru (petugasbaru@grandsul.web.id) memiliki 0 notifikasi (Empty State)
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

        $petugas1 = User::query()->where('email', 'petugas1@grandsul.web.id')->first()
            ?? User::query()->role('petugas')->first();
        if ($petugas1) {
            $petugas1->assignRole($petugasRole);
        }

        // Akun Petugas Baru tanpa notifikasi (TC_051 Empty State)
        $petugasBaru = User::query()->firstOrCreate(
            ['email' => 'petugasbaru@grandsul.web.id'],
            [
                'name' => 'Petugas Baru (Uji Kosong)',
                'password' => Hash::make('password123'),
            ]
        );
        $petugasBaru->syncRoles([$petugasRole]);

        $blokAId = BlokHunian::query()->where('nama_blok', 'Blok A')->value('id') ?? 1;
        $blokDId = BlokHunian::query()->where('nama_blok', 'Blok D')->value('id') ?? 4;

        $now = Carbon::now();

        // 1. TC_049 & TC_052 — Data Ter-flag & Notifikasi untuk Admin
        $unitA03 = UnitHunian::query()->where('nomor_rumah', 'A-03')->first()
            ?? UnitHunian::query()->create([
                'blok_id' => $blokAId,
                'nomor_rumah' => 'A-03',
                'nama_pemilik' => 'Ibu Siti Nurhaliza',
                'nomor_wa' => '081244000003',
                'status_huni' => 'Dihuni',
                'radius_toleransi' => 50,
            ]);

        $bacaanTerflag = BacaanMeter::query()->updateOrCreate(
            [
                'unit_id' => $unitA03->id,
                'bulan' => 11,
                'tahun' => 2026,
            ],
            [
                'user_id' => $petugas1?->id ?? 2,
                'foto_meter' => 'meter-photos/sample-meter-a03.jpg',
                'angka_meter_bulanlalu' => 210,
                'angka_meter' => 295,
                'latitude' => -4.007468,
                'longitude' => 119.661131,
                'status_verifikasi' => 'ter_flag',
                'gps_unverified' => false,
                'confidence_score' => 71.0,
                'is_ocr_corrected' => false,
                'catatan_anomali' => 'Lonjakan pemakaian tinggi (+85 m³) & OCR rendah (71%)',
                'alasan_penolakan' => null,
            ]
        );

        if ($admin) {
            Notifikasi::query()->updateOrCreate(
                [
                    'user_id' => $admin->id,
                    'judul' => 'Data Meter Ter-flag Anomali',
                ],
                [
                    'unit_id' => $unitA03->id,
                    'pesan' => 'Unit A-03 (Ibu Siti Nurhaliza) terdeteksi anomali: Lonjakan pemakaian tinggi & OCR 71%. Segera tinjau foto fisik meteran.',
                    'tipe' => 'flag',
                    'link' => "/admin/terflag?id={$bacaanTerflag->id}",
                    'status_terkirim' => true,
                    'is_read' => false,
                    'tgl_kirim' => $now->copy(),
                ]
            );

            Notifikasi::query()->updateOrCreate(
                [
                    'user_id' => $admin->id,
                    'judul' => 'Batch Tagihan Massal Selesai',
                ],
                [
                    'unit_id' => null,
                    'pesan' => 'Kalkulasi tarif progresif periode 2026-11 selesai diproses. Semua nota rekening siap dicetak.',
                    'tipe' => 'batch',
                    'link' => '/admin/tagihan',
                    'status_terkirim' => true,
                    'is_read' => false,
                    'tgl_kirim' => $now->copy()->subMinutes(5),
                ]
            );
        }

        // 2. TC_050 — Bacaan Unit D-14 ditolak Admin ('Foto buram') & Notifikasi untuk Petugas 1
        $unitD14 = UnitHunian::query()->where('nomor_rumah', 'D-14')->first()
            ?? UnitHunian::query()->create([
                'blok_id' => $blokDId,
                'nomor_rumah' => 'D-14',
                'nama_pemilik' => 'Hasanuddin',
                'nomor_wa' => '081244000014',
                'status_huni' => 'Dihuni',
                'radius_toleransi' => 50,
            ]);

        if ($petugas1) {
            BacaanMeter::query()->updateOrCreate(
                [
                    'unit_id' => $unitD14->id,
                    'bulan' => 11,
                    'tahun' => 2026,
                ],
                [
                    'user_id' => $petugas1->id,
                    'foto_meter' => 'meter-photos/sample-meter-tunggakan.jpg',
                    'angka_meter_bulanlalu' => 4149,
                    'angka_meter' => 4174,
                    'latitude' => -4.007468,
                    'longitude' => 119.661131,
                    'status_verifikasi' => 'ditolak',
                    'gps_unverified' => false,
                    'confidence_score' => 64.0,
                    'is_ocr_corrected' => false,
                    'catatan_anomali' => 'Ditolak Admin: Foto buram',
                    'alasan_penolakan' => 'Foto buram',
                ]
            );

            Notifikasi::query()->updateOrCreate(
                [
                    'user_id' => $petugas1->id,
                    'judul' => 'Pembacaan unit D-14 ditolak',
                ],
                [
                    'unit_id' => $unitD14->id,
                    'pesan' => 'Alasan penolakan: Foto buram. Silakan lakukan foto ulang meteran Unit D-14 (Hasanuddin).',
                    'tipe' => 'flag',
                    'link' => "/petugas/pindai?unit_id={$unitD14->id}&blok_id={$unitD14->blok_id}",
                    'status_terkirim' => true,
                    'is_read' => false,
                    'tgl_kirim' => $now->copy(),
                ]
            );
        }

        // 3. TC_051 — Pastikan akun Petugas Baru tidak punya notifikasi sama sekali (Empty State)
        Notifikasi::query()->where('user_id', $petugasBaru->id)->delete();
    }
}
