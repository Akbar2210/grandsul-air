<?php

namespace App\Services\BatchProcessing;

use App\Models\BacaanMeter;
use App\Models\Tagihan;
use Carbon\Carbon;

/**
 * Konsentrasi Enterprise / Batch Processing (Wahyu Anwar)
 * Fokus: Pemrosesan Tagihan Massal Bertarif Progresif dan Orkestrasi Pengingat Tunggakan WhatsApp.
 */
class BatchTagihanService
{
    public function __construct(
        private TarifProgresifService $tarifService,
        private WhatsappGatewayService $whatsappService,
    ) {}

    /**
     * Memproses seluruh pembacaan meter berstatus 'valid' yang belum memiliki tagihan pada periode berjalan,
     * menghitung tarif progresif bertingkat, menerbitkan tagihan secara unik, serta memicu pengingat WA.
     *
     * @return array{
     *     periode: string,
     *     processed_count: int,
     *     wa_sent_count: int,
     *     wa_failed_count: int
     * }
     */
    public function executeBatchBilling(?string $periode = null): array
    {
        $now = Carbon::now();
        $targetPeriode = $periode ?? $now->format('Y-m');

        $bacaans = BacaanMeter::with('unit')
            ->where('status_verifikasi', 'valid')
            ->whereDoesntHave('tagihan')
            ->get();

        $processedCount = 0;

        foreach ($bacaans as $bacaan) {
            $sudahAdaTagihan = Tagihan::where('unit_id', $bacaan->unit_id)
                ->where('priode', $targetPeriode)
                ->exists();

            if ($sudahAdaTagihan) {
                continue;
            }

            $volume = max(0, (int) $bacaan->angka_meter - (int) $bacaan->angka_meter_bulanlalu);
            $kalkulasi = $this->tarifService->hitungTagihanProgresif($volume);

            Tagihan::create([
                'unit_id' => $bacaan->unit_id,
                'bacaan_meter_id' => $bacaan->id,
                'tarif_id' => $kalkulasi['tarif_utama_id'],
                'priode' => $targetPeriode,
                'volume_pemakaian' => $kalkulasi['volume_total'],
                'total_tagihan' => $kalkulasi['total_tagihan'],
                'status_bayar' => 'belum_lunas',
                'tgl_terbit' => $now->copy()->startOfMonth(),
                'tgl_jatuh_tempo' => $now->copy()->addDays(20),
            ]);

            $processedCount++;
        }

        $waResult = $this->whatsappService->dispatchRemindersForArrears();

        return [
            'periode' => $targetPeriode,
            'processed_count' => $processedCount,
            'wa_sent_count' => $waResult['sent_count'],
            'wa_failed_count' => $waResult['failed_count'],
        ];
    }
}
