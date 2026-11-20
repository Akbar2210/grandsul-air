<?php

namespace App\Services\BatchProcessing;

use App\Models\BacaanMeter;
use App\Models\Tagihan;
use App\Services\CyberSecurity\RsaSignatureService;
use Carbon\Carbon;

/**
 * Konsentrasi Enterprise / Batch Processing (Wahyu Anwar)
 * Fokus: Pemrosesan Tagihan Massal Bertarif Progresif, Penerbitan Nota Otomatis, dan Surat Penyampaian (>= 4 bulan tunggakan).
 */
class BatchTagihanService
{
    public function __construct(
        private TarifProgresifService $tarifService,
        private SuratTunggakanService $suratTunggakanService,
        private RsaSignatureService $rsaSignatureService,
    ) {}

    /**
     * Memproses seluruh pembacaan meter berstatus 'valid' menjadi tagihan bertarif progresif,
     * sekaligus menerbitkan Nota Rekening Air Sumur Bor ber-QR kriptografis secara otomatis
     * agar nota fisik dapat dicetak terlebih dahulu sebelum ditagihkan/dibayar oleh pelanggan.
     *
     * @return array{
     *     periode: string,
     *     processed_count: int,
     *     new_bills_count: int,
     *     nota_issued_count: int,
     *     surat_generated_count: int
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

        $newBillsCount = 0;

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

            $newBillsCount++;
        }

        // Terbitkan Nota Rekening Air Sumur Bor + tanda tangan RSA-2048 otomatis untuk seluruh tagihan terdaftar
        $allTagihans = Tagihan::with('unit')->get();
        $notaIssuedCount = 0;

        foreach ($allTagihans as $tagihan) {
            $this->rsaSignatureService->issueTagihanSignature($tagihan);
            $notaIssuedCount++;
        }

        $suratResult = $this->suratTunggakanService->generateSuratForArrears();

        return [
            'periode' => $targetPeriode,
            'processed_count' => $notaIssuedCount,
            'new_bills_count' => $newBillsCount,
            'nota_issued_count' => $notaIssuedCount,
            'surat_generated_count' => $suratResult['generated_count'],
        ];
    }
}
