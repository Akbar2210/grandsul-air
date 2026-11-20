<?php

namespace App\Services\BatchProcessing;

use App\Models\Tagihan;
use App\Models\UnitHunian;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Konsentrasi Enterprise / Batch Processing (Wahyu Anwar)
 * Fokus: Pembuatan Surat Penyampaian Otomatis bagi warga dengan tunggakan >= 4 bulan
 * (menggantikan WhatsApp Gateway sesuai kebijakan operasional PT. Arista Jaya).
 */
class SuratTunggakanService
{
    public const MIN_BULAN_TUNGGAKAN = 4;

    public const DENDA_PER_BULAN = 10000;

    /**
     * Mendeteksi seluruh unit hunian dengan tunggakan >= 4 bulan dan menyusun data Surat Penyampaian otomatis.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getDaftarSuratOtomatis(): Collection
    {
        $unpaidByUnit = Tagihan::with(['unit.blok'])
            ->where('status_bayar', '!=', 'lunas')
            ->orderBy('priode', 'asc')
            ->get()
            ->groupBy('unit_id');

        $daftarSurat = collect();

        foreach ($unpaidByUnit as $unitId => $tagihans) {
            if ($tagihans->count() < self::MIN_BULAN_TUNGGAKAN) {
                continue;
            }

            $unit = $tagihans->first()?->unit;
            if (! $unit) {
                continue;
            }

            $daftarSurat->push($this->buildSuratPayload($unit, $tagihans));
        }

        return $daftarSurat->sortByDesc('jumlah_bulan')->values();
    }

    /**
     * Menyusun payload Surat Penyampaian untuk satu unit tertentu.
     *
     * @return array<string, mixed>|null
     */
    public function getSuratUntukUnit(int $unitId): ?array
    {
        $unit = UnitHunian::with('blok')->find($unitId);
        if (! $unit) {
            return null;
        }

        $tagihans = Tagihan::where('unit_id', $unit->id)
            ->where('status_bayar', '!=', 'lunas')
            ->orderBy('priode', 'asc')
            ->get();

        if ($tagihans->isEmpty()) {
            return null;
        }

        return $this->buildSuratPayload($unit, $tagihans);
    }

    /**
     * Menjalankan deteksi otomatis saat batch tagihan diproses dan mengembalikan ringkasan jumlah surat.
     *
     * @return array{generated_count: int, unit_numbers: array<int, string>}
     */
    public function generateSuratForArrears(): array
    {
        $daftar = $this->getDaftarSuratOtomatis();

        return [
            'generated_count' => $daftar->count(),
            'unit_numbers' => $daftar->pluck('nomor_rumah')->all(),
        ];
    }

    /**
     * Membangun struktur data resmi Surat Penyampaian PT. Arista Jaya.
     *
     * @param  Collection<int, Tagihan>  $tagihans
     * @return array<string, mixed>
     */
    private function buildSuratPayload(UnitHunian $unit, Collection $tagihans): array
    {
        $now = Carbon::now();
        $jumlahBulan = $tagihans->count();
        $totalPemakaian = (float) $tagihans->sum('total_tagihan');

        // Denda keterlambatan @Rp.10.000,-/bln untuk bulan yang telah melewati jatuh tempo
        $jumlahBulanDenda = max(1, $jumlahBulan - 1);
        $totalDenda = (float) ($jumlahBulanDenda * self::DENDA_PER_BULAN);
        $grandTotal = $totalPemakaian + $totalDenda;

        $firstPriode = $tagihans->first()->priode ?? $now->copy()->subMonths($jumlahBulan - 1)->format('Y-m');
        $lastPriode = $tagihans->last()->priode ?? $now->format('Y-m');

        $periodeAwal = $this->formatPeriode($firstPriode, false);
        $periodeAkhir = $this->formatPeriode($lastPriode, true);

        $romawiBulan = $this->toRomanMonth($now->month);
        $nomorUrut = 490 + (int) $unit->id;
        $nomorSurat = "No. {$nomorUrut}/J-DEV/GS/{$romawiBulan}/{$now->year}";

        return [
            'unit_id' => $unit->id,
            'nomor_rumah' => $unit->nomor_rumah,
            'nama_blok' => $unit->blok->nama_blok ?? 'Blok A',
            'nama_pemilik' => strtoupper($unit->nama_pemilik),
            'nomor_surat' => $nomorSurat,
            'jumlah_bulan' => $jumlahBulan,
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
            'periode_rentang' => "{$periodeAwal} s/d {$periodeAkhir}",
            'total_pemakaian' => $totalPemakaian,
            'jumlah_bulan_denda' => $jumlahBulanDenda,
            'denda_per_bulan' => self::DENDA_PER_BULAN,
            'total_denda' => $totalDenda,
            'grand_total' => $grandTotal,
            'batas_pembayaran' => $now->copy()->endOfMonth()->translatedFormat('d F Y'),
            'tanggal_surat' => $now->translatedFormat('d F Y'),
            'rincian_tagihan' => $tagihans,
        ];
    }

    private function formatPeriode(string $priode, bool $withYear): string
    {
        try {
            $date = Carbon::createFromFormat('Y-m', $priode);

            return $withYear ? $date->translatedFormat('F Y') : $date->translatedFormat('F');
        } catch (\Throwable) {
            return $priode;
        }
    }

    private function toRomanMonth(int $month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $map[$month] ?? 'XI';
    }
}
