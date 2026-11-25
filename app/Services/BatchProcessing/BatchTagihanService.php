<?php

namespace App\Services\BatchProcessing;

use App\Models\BacaanMeter;
use App\Models\Tagihan;
use App\Models\UnitHunian;
use App\Services\CyberSecurity\RsaSignatureService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    // Biaya beban / administrasi tetap per bulan (Rp 11.000)
    public const BIAYA_BEBAN_ADMIN = 11000;

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
     * serentak menggunakan bulk insert per chunk agar 700 unit selesai dalam hitungan detik tanpa timeout
     * sekaligus mencegah duplikasi tagihan pada periode yang sama.
     *
     * @return array{
     *     periode: string,
     *     already_processed: bool,
     *     existing_bills_count: int,
     *     processed_count: int,
     *     new_bills_count: int,
     *     nota_issued_count: int,
     *     surat_generated_count: int
     * }
     */
    public function executeBatchBilling(?string $periode = null): array
    {
        $targetPeriode = ($periode !== null && preg_match('/^\d{4}-\d{2}$/', $periode) === 1)
            ? $periode
            : now()->format('Y-m');

        [$targetYear, $targetMonth] = array_map('intval', explode('-', $targetPeriode));
        $periodeDate = Carbon::createFromDate($targetYear, $targetMonth, 1);

        // Cek unit yang sudah memiliki tagihan pada periode target (pencegahan duplikasi TC_028)
        $billedUnitIds = Tagihan::query()
            ->where('priode', $targetPeriode)
            ->pluck('unit_id')
            ->all();
        $existingBillsCount = count($billedUnitIds);

        // Langkah 1: Ambil seluruh bacaan meteran 'valid' pada periode target yang belum ditagihkan
        $bacaans = BacaanMeter::query()
            ->where('bulan', $targetMonth)
            ->where('tahun', $targetYear)
            ->where('status_verifikasi', 'valid')
            ->when(! empty($billedUnitIds), fn ($q) => $q->whereNotIn('unit_id', $billedUnitIds))
            ->orderBy('id')
            ->get()
            ->unique('unit_id')
            ->values();

        if ($bacaans->isEmpty() && $existingBillsCount > 0) {
            $suratList = $this->getDaftarSuratOtomatis();

            return [
                'periode' => $targetPeriode,
                'already_processed' => true,
                'existing_bills_count' => $existingBillsCount,
                'processed_count' => 0,
                'new_bills_count' => 0,
                'nota_issued_count' => $existingBillsCount,
                'surat_generated_count' => $suratList->count(),
            ];
        }

        // Langkah 2: Susun seluruh baris tagihan dengan rumus tarif progresif + biaya beban lalu bulk insert per chunk
        $now = now();
        $tglTerbit = $periodeDate->copy()->startOfMonth()->toDateString();
        $tglJatuhTempo = $periodeDate->copy()->day(20)->toDateString();
        $rowsToInsert = [];

        foreach ($bacaans as $bacaan) {
            $isStandAwal = ((float) $bacaan->angka_meter_bulanlalu <= 0);
            $volume = $isStandAwal ? 0 : max(0, (int) $bacaan->angka_meter - (int) $bacaan->angka_meter_bulanlalu);
            $kalkulasi = $this->hitungTagihanProgresif($volume);

            $rowsToInsert[] = [
                'unit_id' => $bacaan->unit_id,
                'bacaan_meter_id' => $bacaan->id,
                'tarif_id' => $kalkulasi['tarif_utama_id'],
                'priode' => $targetPeriode,
                'volume_pemakaian' => $kalkulasi['volume_total'],
                'denda' => 0,
                'biaya_admin' => $kalkulasi['biaya_admin'],
                'jumlah_tunggakan' => 0,
                'total_tagihan' => $kalkulasi['total_tagihan'],
                'status_bayar' => 'belum_lunas',
                'tgl_terbit' => $tglTerbit,
                'tgl_jatuh_tempo' => $tglJatuhTempo,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $tagihanBaru = count($rowsToInsert);

        if ($tagihanBaru > 0) {
            DB::transaction(function () use ($rowsToInsert) {
                foreach (array_chunk($rowsToInsert, 250) as $chunk) {
                    Tagihan::query()->insert($chunk);
                }
            });
        }

        // Langkah 3: Otomatis mendeteksi warga yang menunggak >= 4 bulan untuk dibuatkan surat peringatan
        $suratList = $this->getDaftarSuratOtomatis();
        $totalPeriodeCount = Tagihan::query()->where('priode', $targetPeriode)->count();

        return [
            'periode' => $targetPeriode,
            'already_processed' => false,
            'existing_bills_count' => $existingBillsCount,
            'processed_count' => $bacaans->count(),
            'new_bills_count' => $tagihanBaru,
            'nota_issued_count' => $totalPeriodeCount,
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

        // Hitung nominal menggunakan rumus tarif progresif bertingkat + biaya beban
        $kalkulasi = $this->hitungTagihanProgresif($volume);

        // Simpan data tagihan resmi ke database dengan status awal belum_lunas
        $tagihan = Tagihan::create([
            'unit_id' => $bacaan->unit_id,
            'bacaan_meter_id' => $bacaan->id,
            'tarif_id' => $kalkulasi['tarif_utama_id'],
            'priode' => $targetPeriode,
            'volume_pemakaian' => $kalkulasi['volume_total'],
            'denda' => 0,
            'biaya_admin' => $kalkulasi['biaya_admin'],
            'jumlah_tunggakan' => 0,
            'total_tagihan' => $kalkulasi['total_tagihan'],
            'status_bayar' => 'belum_lunas',
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
     * FUNGSI: Menghitung total biaya air berdasarkan rumus tarif progresif bertingkat + biaya beban.
     * PENJELASAN UNTUK SIDANG:
     * Tarif air dihitung bertingkat (progresif) sesuai Permendagri + biaya beban tetap Rp 11.000:
     * - Blok 1 (0 s/d 10 m³)    : Rp 2.000 per m³ (Kebutuhan Dasar)
     * - Blok 2 (11 s/d 20 m³)   : Rp 3.000 per m³ (Kebutuhan Menengah)
     * - Blok 3 (Lebih dari 20 m³): Rp 5.000 per m³ (Konsumsi Tinggi)
     * - Biaya Beban / Admin     : Rp 11.000
     * Contoh pemakaian 14 m³: (10 × 2.000) + (4 × 3.000) + 11.000 = Rp 43.000.
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

        $hargaAir = $subtotal1 + $subtotal2 + $subtotal3;
        $biayaAdmin = (float) self::BIAYA_BEBAN_ADMIN;

        // Total akumulasi tagihan dari seluruh blok + biaya beban/admin
        $totalTagihan = $hargaAir + $biayaAdmin;

        return [
            'volume_total' => $volume,
            'harga_air' => $hargaAir,
            'biaya_admin' => $biayaAdmin,
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

        return ($unit && $tagihans->count() >= self::MIN_BULAN_TUNGGAKAN)
            ? $this->buildSuratPayload($unit, $tagihans)
            : null;
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
        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$now->month - 1] ?? 'XI';
        $nomorSurat = 'No. '.(490 + (int) $unit->id)."/J-DEV/GS/{$romawi}/{$now->year}";

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
