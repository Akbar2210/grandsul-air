<?php

namespace App\Services\BatchProcessing;

use App\Models\Notifikasi;
use App\Models\Tagihan;
use App\Models\UnitHunian;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * Konsentrasi Enterprise / Batch Processing (Wahyu Anwar)
 * Fokus: Pengiriman Pengingat Tunggakan Otomatis (> 4 Bulan) melalui Fonnte WhatsApp Gateway API.
 */
class WhatsappGatewayService
{
    /**
     * Ambang batas jumlah bulan tunggakan untuk memicu pengiriman pengingat WhatsApp (SRS FR-009).
     */
    private const MIN_ARREARS_MONTHS = 4;

    /**
     * Merangkai format pesan pengingat tunggakan air untuk warga.
     */
    public function formatPesanTunggakan(UnitHunian $unit, int $jumlahBulan, float $totalTunggakan): string
    {
        $nominalFormat = number_format($totalTunggakan, 0, ',', '.');

        return "Yth. Bapak/Ibu {$unit->nama_pemilik} (Unit {$unit->nomor_rumah}), "
            ."kami informasikan terdapat tunggakan tagihan air bersih Grand Sulawesi selama {$jumlahBulan} bulan "
            ."dengan total Rp {$nominalFormat}. Mohon kesediaannya melakukan pelunasan melalui Pengelola. Terima kasih.";
    }

    /**
     * Mengirim pesan pengingat ke nomor WhatsApp warga via Fonnte API dan mencatat hasilnya di tabel notifikasis.
     * Kegagalan pengiriman satu pesan tidak menghentikan proses batch (SRS FR-009 AC-009.2).
     */
    public function sendReminderToUnit(UnitHunian $unit, int $jumlahBulan, float $totalTunggakan): Notifikasi
    {
        $pesan = $this->formatPesanTunggakan($unit, $jumlahBulan, $totalTunggakan);
        $nomorWa = trim((string) $unit->nomor_wa);
        $token = (string) config('services.fonnte.token', '');
        $endpoint = (string) config('services.fonnte.endpoint', 'https://api.fonnte.com/send');

        $terkirim = false;

        if ($nomorWa !== '' && $token !== '') {
            try {
                $response = Http::connectTimeout(3)
                    ->timeout(8)
                    ->withHeaders(['Authorization' => $token])
                    ->post($endpoint, [
                        'target' => $nomorWa,
                        'message' => $pesan,
                        'countryCode' => '62',
                    ]);

                $terkirim = $response->successful() && (bool) data_get($response->json(), 'status', true);
            } catch (\Throwable) {
                $terkirim = false;
            }
        } elseif ($nomorWa !== '') {
            // Mode simulasi lokal saat token Fonnte belum dikonfigurasi di environment lokal
            $terkirim = true;
        }

        return Notifikasi::create([
            'unit_id' => $unit->id,
            'pesan' => $pesan,
            'tgl_kirim' => Carbon::now(),
            'status_terkirim' => $terkirim,
        ]);
    }

    /**
     * Memeriksa seluruh unit yang memiliki tunggakan > 4 bulan (atau ditandai menunggak > 4 bulan)
     * dan mengirimkan WhatsApp Auto-Reminder.
     *
     * @return array{sent_count: int, failed_count: int}
     */
    public function dispatchRemindersForArrears(): array
    {
        $sentCount = 0;
        $failedCount = 0;

        $units = UnitHunian::with(['tagihans' => function ($query): void {
            $query->where('status_bayar', '!=', 'lunas');
        }, 'bacaanMeters'])->get();

        foreach ($units as $unit) {
            $unpaidCount = $unit->tagihans->count();
            $hasArrearsNote = $unit->bacaanMeters->contains(function ($bacaan): bool {
                return str_contains((string) $bacaan->catatan_anomali, 'Tunggakan > 4 bulan');
            });

            if ($unpaidCount > self::MIN_ARREARS_MONTHS || $hasArrearsNote) {
                $bulanTunggak = max($unpaidCount, 5);
                $totalTunggakan = (float) Tagihan::where('unit_id', $unit->id)
                    ->where('status_bayar', '!=', 'lunas')
                    ->sum('total_tagihan');

                $notifikasi = $this->sendReminderToUnit($unit, $bulanTunggak, $totalTunggakan);
                if ($notifikasi->status_terkirim) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }
            }
        }

        return [
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ];
    }
}
