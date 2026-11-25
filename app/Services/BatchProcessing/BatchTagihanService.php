<?php

namespace App\Services\BatchProcessing;

use App\Models\BacaanMeter;
use App\Models\Tagihan;
use App\Models\TarifAir;
use App\Models\UnitHunian;
use App\Services\CyberSecurity\RsaSignatureService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * ============================================================================
 * KONSENTRASI ENTERPRISE: METODE BATCH PROCESSING (Wahyu Anwar)
 * Sistem Pengelolaan Air Bersih Kompleks Perumahan Grand Sulawesi
 * ============================================================================
 * File ini adalah pusat logika metode Batch Processing yang mencakup:
 * 1. Eksekusi Tagihan Massal (Batch Billing Queue)
 * 2. Perhitungan Tarif Air Progresif Bertingkat (Permendagri No. 71/2016)
 * 3. Penerbitan Otomatis Surat Penyampaian Tunggakan (≥ 4 Bulan)
 * ============================================================================
 */
class BatchTagihanService
{
    // Batas minimal tunggakan untuk diterbitkan surat resmi (4 bulan)
    public const MIN_BULAN_TUNGGAKAN = 4;

    // Denda keterlambatan per bulan sesuai aturan PT. Arista Jaya (Rp 10.000)
    public const DENDA_PER_BULAN = 10000;

    public function __construct(
        private RsaSignatureService $rsaSignatureService,
    ) {}

    // ========================================================================
    // BAGIAN 1: EKSEKUSI PEMROSESAN TAGIHAN MASSAL (BATCH BILLING)
    // ========================================================================

    /**
     * FUNGSI: Memproses seluruh data pembacaan meteran valid menjadi tagihan sekaligus.
     * PENJELASAN UNTUK SIDANG:
     * Alur kerja metode batch: data yang sudah difoto dan divalidasi tidak langsung jadi nota satu per satu,
     * melainkan dikumpulkan di antrean (queue). Fungsi ini akan memproses semua antrean tersebut secara
     * serentak dalam satu kali jalan agar server tidak timeout dan data tagihan 700 unit rapi.
     */
    public function executeBatchBilling(?string $periode = null): array
    {
        $targetPeriode = $periode ?? now()->format('Y-m');

        // Langkah 1: Ambil seluruh data meteran yang berstatus 'valid' tapi belum dibuatkan tagihannya
        $bacaans = BacaanMeter::where('status_verifikasi', 'valid')
            ->whereDoesntHave('tagihan')
            ->get();

        $tagihanBaru = 0;

        // Langkah 2: Loop / iterasi batch untuk menghitung tagihan tiap unit secara otomatis
        foreach ($bacaans as $bacaan) {
            $tagihan = $this->generateSingleTagihan($bacaan, $targetPeriode);
            if ($tagihan?->wasRecentlyCreated) {
                $tagihanBaru++;
            }
        }

        // Langkah 3: Integrasi dengan modul Cyber Security untuk memberi Tanda Tangan RSA & QR Code pada setiap nota
        foreach (Tagihan::all() as $tagihan) {
            $this->rsaSignatureService->issueTagihanSignature($tagihan);
        }

        // Langkah 4: Otomatis mendeteksi warga yang menunggak >= 4 bulan untuk dibuatkan surat peringatan
        $suratList = $this->getDaftarSuratOtomatis();

        return [
            'periode' => $targetPeriode,
            'processed_count' => $bacaans->count(),
            'new_bills_count' => $tagihanBaru,
            'nota_issued_count' => Tagihan::count(),
            'surat_generated_count' => $suratList->count(),
        ];
    }

    /**
     * FUNGSI: Membuat tagihan untuk satu unit meteran tertentu.
     * PENJELASAN UNTUK SIDANG:
     * Digunakan saat ada data anomali (ter-flag) yang sudah selesai ditinjau dan divalidasi oleh Admin,
     * sehingga data tersebut bisa langsung diterbitkan tagihannya tanpa harus menunggu siklus batch baru.
     */
    public function generateSingleTagihan(BacaanMeter $bacaan, ?string $periode = null): ?Tagihan
    {
        $targetPeriode = $periode ?? now()->format('Y-m');

        // Cegah tagihan ganda untuk rumah yang sama di bulan yang sama
        $cekTagihan = Tagihan::where('unit_id', $bacaan->unit_id)->where('priode', $targetPeriode)->first();
        if ($cekTagihan) {
            return $cekTagihan;
        }

        // Hitung pemakaian air: Stand Akhir Bulan Ini - Stand Awal Bulan Lalu
        $isStandAwal = ((float) $bacaan->angka_meter_bulanlalu <= 0);
        $volume = $isStandAwal ? 0 : max(0, (int) $bacaan->angka_meter - (int) $bacaan->angka_meter_bulanlalu);

        // Hitung nominal menggunakan rumus tarif progresif bertingkat
        $kalkulasi = $this->hitungTagihanProgresif($volume);

        // Status awal: Unit 1-407 berstatus lunas (data historis perumahan), sisanya belum lunas
        $statusBayar = ($bacaan->unit_id <= 407) ? 'lunas' : 'belum_lunas';

        // Simpan data tagihan resmi ke database
        $tagihan = Tagihan::create([
            'unit_id' => $bacaan->unit_id,
            'bacaan_meter_id' => $bacaan->id,
            'tarif_id' => $kalkulasi['tarif_utama_id'],
            'priode' => $targetPeriode,
            'volume_pemakaian' => $kalkulasi['volume_total'],
            'total_tagihan' => $kalkulasi['total_tagihan'],
            'status_bayar' => $statusBayar,
            'tgl_terbit' => now()->startOfMonth(),
            'tgl_jatuh_tempo' => now()->addDays(20),
        ]);

        // Otomatis terbitkan tanda tangan digital kriptografi RSA-2048
        $this->rsaSignatureService->issueTagihanSignature($tagihan);

        return $tagihan;
    }

    // ========================================================================
    // BAGIAN 2: KALKULASI TARIF PROGRESIF (Permendagri No. 71 Tahun 2016)
    // ========================================================================

    /**
     * FUNGSI: Menghitung total biaya air berdasarkan rumus tarif progresif bertingkat.
     * PENJELASAN UNTUK SIDANG:
     * Tarif air tidak dihitung dengan tarif flat/tetap, melainkan bertingkat (progresif) sesuai Permendagri:
     * - Blok 1 (0 s/d 10 m³)   : Rp 2.000 per m³ (Kebutuhan Dasar)
     * - Blok 2 (11 s/d 20 m³)  : Rp 3.000 per m³ (Kebutuhan Menengah)
     * - Blok 3 (Lebih dari 20 m³): Rp 5.000 per m³ (Konsumsi Tinggi)
     * Contoh jika pemakaian 25 m³: (10 * 2.000) + (10 * 3.000) + (5 * 5.000) = Rp 75.000.
     */
    public function hitungTagihanProgresif(int|float $volumePemakaian): array
    {
        $volume = max(0, (int) round($volumePemakaian));

        // Hitung pemakaian per blok
        $volBlok1 = min(10, $volume);
        $subtotal1 = $volBlok1 * 2000.0;

        $volBlok2 = max(0, min(10, $volume - 10));
        $subtotal2 = $volBlok2 * 3000.0;

        $volBlok3 = max(0, $volume - 20);
        $subtotal3 = $volBlok3 * 5000.0;

        // Total akumulasi tagihan dari seluruh blok
        $totalTagihan = $subtotal1 + $subtotal2 + $subtotal3;

        return [
            'volume_total' => $volume,
            'total_tagihan' => $totalTagihan,
            'tarif_utama_id' => 1,
            'rincian_blok' => [
                ['blok' => 'Blok 1 (0 - 10 m³)', 'volume' => $volBlok1, 'tarif_per_m3' => 2000.0, 'subtotal' => $subtotal1],
                ['blok' => 'Blok 2 (11 - 20 m³)', 'volume' => $volBlok2, 'tarif_per_m3' => 3000.0, 'subtotal' => $subtotal2],
                ['blok' => 'Blok 3 (> 20 m³)', 'volume' => $volBlok3, 'tarif_per_m3' => 5000.0, 'subtotal' => $subtotal3],
            ],
        ];
    }

    // ========================================================================
    // BAGIAN 3: OTOMASI SURAT PENYAMPAIAN TUNGGAKAN (≥ 4 BULAN)
    // ========================================================================

    /**
     * FUNGSI: Mendeteksi seluruh unit rumah yang memiliki tunggakan air minimal 4 bulan.
     * PENJELASAN UNTUK SIDANG:
     * Sebagai pengganti WhatsApp Gateway, pengelola (PT. Arista Jaya) menggunakan Surat Penyampaian resmi.
     * Fungsi ini otomatis mencari data warga yang belum lunas, dan jika jumlah bulannya >= 4,
     * otomatis dibuatkan draf surat beserta kalkulasi dendanya.
     */
    public function getDaftarSuratOtomatis(): Collection
    {
        return Tagihan::with(['unit.blok'])
            ->where('status_bayar', '!=', 'lunas')
            ->orderBy('priode', 'asc')
            ->get()
            ->groupBy('unit_id')
            ->filter(fn ($tagihans) => $tagihans->count() >= self::MIN_BULAN_TUNGGAKAN)
            ->map(fn ($tagihans) => $this->buildSuratPayload($tagihans->first()->unit, $tagihans))
            ->sortByDesc('jumlah_bulan')
            ->values();
    }

    /**
     * FUNGSI: Mengambil rincian data surat tunggakan untuk satu unit rumah yang akan dicetak.
     */
    public function getSuratUntukUnit(int $unitId): ?array
    {
        $unit = UnitHunian::with('blok')->find($unitId);
        $tagihans = Tagihan::where('unit_id', $unitId)->where('status_bayar', '!=', 'lunas')->orderBy('priode', 'asc')->get();

        return ($unit && $tagihans->isNotEmpty()) ? $this->buildSuratPayload($unit, $tagihans) : null;
    }

    /**
     * FUNGSI: Rekapitulasi jumlah surat tunggakan yang otomatis terbit setelah proses batch selesai.
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
     * FUNGSI BANTU: Menyusun struktur data resmi Surat Penyampaian PT. Arista Jaya.
     * Menghitung total pokok tunggakan + denda keterlambatan (Rp 10.000 per bulan).
     */
    private function buildSuratPayload(UnitHunian $unit, Collection $tagihans): array
    {
        $now = now();
        $jumlahBulan = $tagihans->count();
        $totalPokok = (float) $tagihans->sum('total_tagihan');

        // Denda dihitung untuk bulan yang melewati jatuh tempo: (jumlah bulan - 1) * Rp 10.000
        $bulanDenda = max(1, $jumlahBulan - 1);
        $totalDenda = (float) ($bulanDenda * self::DENDA_PER_BULAN);
        $grandTotal = $totalPokok + $totalDenda;

        // Penomoran resmi surat developer: No. [ID]/J-DEV/GS/[Romawi Bulan]/[Tahun]
        $romawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$now->month - 1] ?? 'XI';
        $nomorSurat = "No. " . (490 + (int) $unit->id) . "/J-DEV/GS/{$romawi}/{$now->year}";

        $periodeAwal = Carbon::parse($tagihans->first()->priode ?? $now)->translatedFormat('F');
        $periodeAkhir = Carbon::parse($tagihans->last()->priode ?? $now)->translatedFormat('F Y');

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
            'total_pemakaian' => $totalPokok,
            'jumlah_bulan_denda' => $bulanDenda,
            'denda_per_bulan' => self::DENDA_PER_BULAN,
            'total_denda' => $totalDenda,
            'grand_total' => $grandTotal,
            'batas_pembayaran' => $now->copy()->endOfMonth()->translatedFormat('d F Y'),
            'tanggal_surat' => $now->translatedFormat('d F Y'),
            'rincian_tagihan' => $tagihans,
        ];
    }
}
