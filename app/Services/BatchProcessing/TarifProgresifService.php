<?php

namespace App\Services\BatchProcessing;

use App\Models\TarifAir;

/**
 * Konsentrasi Enterprise / Batch Processing (Wahyu Anwar)
 * Fokus: Kalkulasi Skema Tarif Air Progresif Bertingkat (Permendagri No. 71 Tahun 2016).
 */
class TarifProgresifService
{
    /**
     * Menghitung rincian dan total tagihan air berdasarkan skema tarif progresif bertingkat:
     * - Blok 1 (0 - 10 m³): Rp 2.000 / m³
     * - Blok 2 (11 - 20 m³): Rp 3.000 / m³
     * - Blok 3 (> 20 m³): Rp 5.000 / m³
     *
     * Rumus SRS FR-005: T = ∑ min(max(V − b_{i-1}, 0), b_i − b_{i-1}) × t_i
     *
     * @return array{
     *     volume_total: int,
     *     total_tagihan: float,
     *     tarif_utama_id: int,
     *     rincian_blok: array<int, array{blok: string, volume: int, tarif_per_m3: float, subtotal: float}>
     * }
     */
    public function hitungTagihanProgresif(int|float $volumePemakaian): array
    {
        $volume = max(0, (int) round($volumePemakaian));

        $tarifDefault = TarifAir::orderBy('batas_bawah')->first();
        if (! $tarifDefault) {
            $tarifDefault = TarifAir::create([
                'blok_konsumsi' => '0 - 10 m³',
                'batas_bawah' => 0,
                'batas_atas' => 10,
                'tarif_per_m3' => 2000,
            ]);
        }

        $volBlok1 = min(10, $volume);
        $subBlok1 = $volBlok1 * 2000.0;

        $volBlok2 = max(0, min(10, $volume - 10));
        $subBlok2 = $volBlok2 * 3000.0;

        $volBlok3 = max(0, $volume - 20);
        $subBlok3 = $volBlok3 * 5000.0;

        $totalTagihan = $subBlok1 + $subBlok2 + $subBlok3;

        return [
            'volume_total' => $volume,
            'total_tagihan' => $totalTagihan,
            'tarif_utama_id' => (int) $tarifDefault->id,
            'rincian_blok' => [
                [
                    'blok' => 'Blok 1 (0 - 10 m³)',
                    'volume' => $volBlok1,
                    'tarif_per_m3' => 2000.0,
                    'subtotal' => $subBlok1,
                ],
                [
                    'blok' => 'Blok 2 (11 - 20 m³)',
                    'volume' => $volBlok2,
                    'tarif_per_m3' => 3000.0,
                    'subtotal' => $subBlok2,
                ],
                [
                    'blok' => 'Blok 3 (> 20 m³)',
                    'volume' => $volBlok3,
                    'tarif_per_m3' => 5000.0,
                    'subtotal' => $subBlok3,
                ],
            ],
        ];
    }
}
