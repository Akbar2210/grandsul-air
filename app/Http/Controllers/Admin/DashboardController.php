<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TarifAir;
use App\Models\UnitHunian;
use App\Models\User;
use App\Services\BatchProcessing\BatchTagihanService;
use App\Services\BatchProcessing\SuratTunggakanService;
use App\Services\CyberSecurity\RsaSignatureService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DashboardController extends Controller
{
    /**
     * 1. Dashboard Utama Admin
     */
    public function index()
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $periodeStr = $now->translatedFormat('F Y');

        $totalUnit = UnitHunian::count();
        $unitAktif = UnitHunian::where('status_huni', 'Dihuni')->count();
        $persenAktif = $totalUnit > 0 ? round(($unitAktif / $totalUnit) * 100) : 0;

        $totalTerflag = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();

        // Total pembacaan valid yang sudah terverifikasi masuk bulan ini
        $totalBacaanBulanIni = BacaanMeter::where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->where('status_verifikasi', 'valid')
            ->distinct('unit_id')
            ->count('unit_id');
        $progresPembacaan = $totalUnit > 0 ? round(($totalBacaanBulanIni / $totalUnit) * 100) : 0;

        $totalTagihanCount = Tagihan::count();
        $tagihanLunasCount = Tagihan::where('status_bayar', 'lunas')->count();
        $tagihanBelumLunasCount = Tagihan::where('status_bayar', '!=', 'lunas')->count();

        $nominalBelumLunas = (float) Tagihan::where('status_bayar', '!=', 'lunas')->sum('total_tagihan');
        $totalNilaiTagihan = (float) Tagihan::sum('total_tagihan');
        $totalNilaiLunas = (float) Tagihan::where('status_bayar', 'lunas')->sum('total_tagihan');

        $donutDenom = $tagihanLunasCount + $tagihanBelumLunasCount + $totalTerflag;
        $persenLunas = $donutDenom > 0 ? round(($tagihanLunasCount / $donutDenom) * 100) : 0;
        $persenBelumLunas = $donutDenom > 0 ? round(($tagihanBelumLunasCount / $donutDenom) * 100) : 0;
        $persenTerflag = $donutDenom > 0 ? max(0, 100 - $persenLunas - $persenBelumLunas) : 0;
        $efisiensiKoleksi = $totalNilaiTagihan > 0 ? round(($totalNilaiLunas / $totalNilaiTagihan) * 100, 1) : 0.0;

        // Data tren pemakaian air real 12 bulan (Jan - Des) tahun berjalan
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $tagihanPerBulan = Tagihan::query()
            ->where('priode', 'like', $currentYear.'-%')
            ->selectRaw('priode, SUM(volume_pemakaian) as total_volume')
            ->groupBy('priode')
            ->pluck('total_volume', 'priode');

        $bacaanPerBulan = BacaanMeter::query()
            ->where('tahun', $currentYear)
            ->where('status_verifikasi', 'valid')
            ->selectRaw('bulan, SUM(GREATEST(0, angka_meter - angka_meter_bulanlalu)) as total_volume')
            ->groupBy('bulan')
            ->pluck('total_volume', 'bulan');

        $trendPemakaian = [];
        for ($m = 1; $m <= 12; $m++) {
            $priodeKey = sprintf('%04d-%02d', $currentYear, $m);
            $vol = $tagihanPerBulan->has($priodeKey)
                ? (int) $tagihanPerBulan->get($priodeKey)
                : (int) ($bacaanPerBulan->get($m) ?? 0);

            $trendPemakaian[] = [
                'bulan_num' => $m,
                'bulan' => $monthNames[$m - 1],
                'volume' => $vol,
                'is_current' => $m === $currentMonth,
                'has_data' => $vol > 0,
            ];
        }

        $maxTrendVolume = max(array_column($trendPemakaian, 'volume'));
        $chartYMax = $maxTrendVolume > 0 ? max(2000, (int) ceil($maxTrendVolume / 2000) * 2000) : 10000;

        $volumeBulanIni = $trendPemakaian[$currentMonth - 1]['volume'] ?? 0;
        $totalVolumeTahunIni = array_sum(array_column($trendPemakaian, 'volume'));
        $filledVolumes = array_values(array_filter(array_column($trendPemakaian, 'volume'), fn (int $v) => $v > 0));
        $rataRataBulanan = count($filledVolumes) > 0 ? round(array_sum($filledVolumes) / count($filledVolumes), 1) : 0.0;

        $dataTerflag = BacaanMeter::with(['unit.blok', 'petugas'])
            ->where('status_verifikasi', 'ter_flag')
            ->latest()
            ->take(6)
            ->get();

        $recentTagihans = Tagihan::with(['unit.blok', 'bacaanMeter'])
            ->latest()
            ->take(6)
            ->get();

        $batchStatus = [
            'total_target' => $totalUnit,
            'completed' => $totalTagihanCount,
            'percent' => round(($totalTagihanCount / max(1, $totalUnit)) * 100),
            'berhasil' => $totalTagihanCount,
            'gagal' => 0,
            'waktu_proses' => '1 mnt 12 dtk',
            'wa_terkirim' => 41,
        ];

        return view('admin.dashboard', compact(
            'totalUnit',
            'unitAktif',
            'persenAktif',
            'totalBacaanBulanIni',
            'progresPembacaan',
            'totalTerflag',
            'totalTagihanCount',
            'tagihanLunasCount',
            'tagihanBelumLunasCount',
            'persenLunas',
            'persenBelumLunas',
            'persenTerflag',
            'nominalBelumLunas',
            'totalNilaiTagihan',
            'efisiensiKoleksi',
            'volumeBulanIni',
            'totalVolumeTahunIni',
            'rataRataBulanan',
            'trendPemakaian',
            'chartYMax',
            'currentMonth',
            'currentYear',
            'dataTerflag',
            'recentTagihans',
            'batchStatus',
            'periodeStr'
        ));
    }

    /**
     * 2. Tinjau Data Ter-flag (Exception-based review)
     * Sesuai SRS Page 9 & Lampiran B (b)
     */
    public function terflag(Request $request)
    {
        $now = Carbon::now();
        $periodeStr = $now->translatedFormat('F Y');

        $query = BacaanMeter::with(['unit.blok', 'petugas'])
            ->where('status_verifikasi', 'ter_flag')
            ->orderBy('id', 'desc');

        $terflagList = $query->get();

        // Ambil item yang sedang dibuka di panel kanan (default item pertama)
        $selectedId = $request->get('id');
        $selected = null;

        if ($selectedId) {
            $selected = BacaanMeter::with(['unit.blok', 'petugas'])->find($selectedId);
        }

        if (! $selected && $terflagList->isNotEmpty()) {
            $selected = $terflagList->first();
        }

        $totalTerflagCount = $terflagList->count();
        $totalUnit = UnitHunian::count() ?: 140;
        $validBacaanCount = BacaanMeter::where('status_verifikasi', 'valid')->count();

        return view('admin.terflag', compact('terflagList', 'selected', 'periodeStr', 'totalTerflagCount', 'totalUnit', 'validBacaanCount'));
    }

    /**
     * Aksi Validasi / Perbaikan / Penolakan Data Ter-flag
     */
    public function resolveTerflag(Request $request, $id, BatchTagihanService $batchTagihanService)
    {
        $bacaan = BacaanMeter::with('unit')->findOrFail($id);
        $action = $request->input('action'); // validate | edit | reject
        $user = Auth::user();

        if ($action === 'validate') {
            $bacaan->status_verifikasi = 'valid';
            $bacaan->save();

            // Masuk antrean batch tagihan (tidak langsung dibuat tagihan individual sesuai permintaan user & alur batch)
            Notifikasi::create([
                'unit_id' => $bacaan->unit_id,
                'judul' => 'Data Ter-flag Divalidasi (Masuk Antrean Batch)',
                'pesan' => "Unit {$bacaan->unit->nomor_rumah} ({$bacaan->unit->nama_pemilik}) telah divalidasi oleh admin ({$bacaan->angka_meter} m³). Siap diproses pada antrean batch tagihan.",
                'tipe' => 'valid',
                'link' => route('admin.tagihan'),
                'status_terkirim' => true,
                'is_read' => false,
                'tgl_kirim' => Carbon::now(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'aktivitas' => "Memvalidasi data meter unit {$bacaan->unit->nomor_rumah} (Stand: {$bacaan->angka_meter} m³) - masuk antrean batch tagihan",
                'ip_address' => $request->ip(),
                'timestamp' => Carbon::now(),
            ]);

            return redirect()->route('admin.terflag')->with('success', "Data meter Unit {$bacaan->unit->nomor_rumah} berhasil divalidasi dan masuk antrean batch tagihan.");
        }

        if ($action === 'edit') {
            $angkaBaru = (float) $request->input('angka_meter');
            $catatan = $request->input('catatan');

            $angkaLama = $bacaan->angka_meter;
            $bacaan->angka_meter = $angkaBaru;
            $bacaan->is_ocr_corrected = true;
            $bacaan->status_verifikasi = 'valid';
            $bacaan->catatan_anomali = 'Dikoreksi Admin: '.$catatan;
            $bacaan->save();

            // Masuk antrean batch tagihan (tidak langsung dibuat tagihan individual sesuai permintaan user & alur batch)
            Notifikasi::create([
                'unit_id' => $bacaan->unit_id,
                'judul' => 'Data Ter-flag Dikoreksi (Masuk Antrean Batch)',
                'pesan' => "Unit {$bacaan->unit->nomor_rumah} dikoreksi dari {$angkaLama} m³ menjadi {$angkaBaru} m³. Siap diproses pada antrean batch tagihan.",
                'tipe' => 'valid',
                'link' => route('admin.tagihan'),
                'status_terkirim' => true,
                'is_read' => false,
                'tgl_kirim' => Carbon::now(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'aktivitas' => "Mengoreksi angka meter unit {$bacaan->unit->nomor_rumah} dari {$angkaLama} m³ menjadi {$angkaBaru} m³ - masuk antrean batch tagihan",
                'ip_address' => $request->ip(),
                'timestamp' => Carbon::now(),
            ]);

            return redirect()->route('admin.terflag')->with('success', "Koreksi angka meter Unit {$bacaan->unit->nomor_rumah} berhasil disimpan dan masuk antrean batch tagihan.");
        }

        if ($action === 'reject') {
            $alasan = $request->input('catatan', 'Foto tidak jelas/blur');
            $bacaan->status_verifikasi = 'ditolak';
            $bacaan->alasan_penolakan = $alasan;
            $bacaan->catatan_anomali = 'Ditolak Admin: '.$alasan;
            $bacaan->save();

            // Notifikasi ke petugas lapangan pemilik bacaan
            Notifikasi::create([
                'user_id' => $bacaan->user_id,
                'unit_id' => $bacaan->unit_id,
                'judul' => "Pembacaan unit {$bacaan->unit->nomor_rumah} ditolak",
                'pesan' => "Alasan penolakan: {$alasan}. Silakan lakukan foto ulang atau koreksi angka meteran Unit {$bacaan->unit->nomor_rumah} ({$bacaan->unit->nama_pemilik}).",
                'tipe' => 'flag',
                'link' => route('petugas.pindai', ['unit_id' => $bacaan->unit_id, 'blok_id' => $bacaan->unit->blok_id]),
                'status_terkirim' => true,
                'is_read' => false,
                'tgl_kirim' => Carbon::now(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'aktivitas' => "Menolak pembacaan meter unit {$bacaan->unit->nomor_rumah} dengan alasan: {$alasan}",
                'ip_address' => $request->ip(),
                'timestamp' => Carbon::now(),
            ]);

            return redirect()->route('admin.terflag')->with('success', "Pembacaan meter Unit {$bacaan->unit->nomor_rumah} ditolak. Petugas diminta melakukan pemindaian ulang.");
        }

        return redirect()->route('admin.terflag');
    }

    /**
     * Bangun daftar opsi dropdown periode (YYYY-MM => Label Bulan Tahun).
     *
     * @param  array<int, string>  $extraPeriods
     * @return array<string, string>
     */
    private function buildPeriodeOptions(array $extraPeriods = []): array
    {
        $now = Carbon::now();
        $keys = [];
        for ($m = 1; $m <= 12; $m++) {
            $keys[] = sprintf('%04d-%02d', $now->year, $m);
        }
        foreach ($extraPeriods as $p) {
            if (preg_match('/^\d{4}-\d{2}$/', (string) $p) === 1) {
                $keys[] = (string) $p;
            }
        }
        $keys = array_values(array_unique($keys));
        rsort($keys);

        $options = [];
        foreach ($keys as $ym) {
            [$y, $m] = explode('-', $ym);
            $options[$ym] = Carbon::createFromDate((int) $y, (int) $m, 1)->translatedFormat('F Y');
        }

        return $options;
    }

    /**
     * 3. Tagihan & Batch Processing
     * Sesuai SRS Page 10 & Lampiran B (c)
     */
    public function tagihan(Request $request)
    {
        $now = Carbon::now();
        $periodeStr = $now->translatedFormat('F Y');
        $bloks = BlokHunian::orderBy('nama_blok')->get();

        $selectedPeriode = (string) $request->get('periode', '');
        $selectedBlok = $request->get('blok');
        $selectedStatus = $request->get('status');
        $search = trim((string) $request->get('search', ''));

        $existingPeriods = Tagihan::query()->distinct()->pluck('priode')->all();
        $periodeOptions = $this->buildPeriodeOptions($existingPeriods);

        $query = Tagihan::with(['unit.blok', 'bacaanMeter'])
            ->when($selectedPeriode !== '', function ($q) use ($selectedPeriode) {
                $q->where('priode', $selectedPeriode);
            })
            ->when($selectedBlok, function ($q) use ($selectedBlok) {
                $q->whereHas('unit', function ($u) use ($selectedBlok) {
                    $u->where('blok_id', $selectedBlok);
                });
            })
            ->when($selectedStatus, function ($q) use ($selectedStatus) {
                $q->where('status_bayar', $selectedStatus);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('unit', function ($u) use ($search) {
                    $u->where('nomor_rumah', 'like', "%{$search}%")
                        ->orWhere('nama_pemilik', 'like', "%{$search}%");
                });
            })
            ->latest('id');

        $tagihans = $query->paginate(12)->withQueryString();

        $totalTerflagCount = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();
        $totalTargetUnit = max(1, UnitHunian::count());

        $activeBatchPeriode = ($selectedPeriode !== '' && preg_match('/^\d{4}-\d{2}$/', $selectedPeriode) === 1)
            ? $selectedPeriode
            : $now->format('Y-m');
        [$batchYear, $batchMonth] = array_map('intval', explode('-', $activeBatchPeriode));

        $billedIdsInPeriode = Tagihan::query()
            ->where('priode', $activeBatchPeriode)
            ->pluck('unit_id')
            ->all();
        $currentTagihanCount = count($billedIdsInPeriode);

        // Data pembacaan valid pada periode terpilih yang siap diproses menjadi tagihan
        $unbilledCount = BacaanMeter::query()
            ->where('bulan', $batchMonth)
            ->where('tahun', $batchYear)
            ->where('status_verifikasi', 'valid')
            ->when(! empty($billedIdsInPeriode), fn ($q) => $q->whereNotIn('unit_id', $billedIdsInPeriode))
            ->distinct('unit_id')
            ->count('unit_id');

        // Persentase progress bar antrean batch: kosong (0%) saat tidak ada antrean, terisi proporsional saat ada data valid siap diproses
        $queuePercent = $unbilledCount > 0
            ? min(100, max(5, round(($unbilledCount / $totalTargetUnit) * 100)))
            : 0;

        // Status Pemrosesan Antrean
        $workerStatus = $unbilledCount > 0
            ? "Antrean Siap ({$unbilledCount} unit siap diproses)"
            : 'Selesai (Antrean Kosong)';

        $batchInfo = [
            'periode' => $activeBatchPeriode,
            'total_target' => $totalTargetUnit,
            'processed' => $currentTagihanCount,
            'unbilled_count' => $unbilledCount,
            'sisa_target' => max(0, $totalTargetUnit - $currentTagihanCount),
            'status' => $workerStatus,
            'percent' => $queuePercent,
            'success_count' => $currentTagihanCount,
            'failed_count' => 0,
            'duration' => '< 1 dtk',
        ];

        return view('admin.tagihan', compact('tagihans', 'bloks', 'periodeStr', 'periodeOptions', 'selectedPeriode', 'selectedBlok', 'selectedStatus', 'search', 'batchInfo', 'totalTerflagCount'));
    }

    /**
     * Eksekusi Batch Processing Tagihan Massal, Penerbitan Nota Otomatis, & Deteksi Surat Penyampaian (>= 4 Bulan)
     */
    public function batchTagihan(Request $request, BatchTagihanService $batchTagihanService)
    {
        $user = Auth::user();
        $requestedPeriode = trim((string) $request->input('periode', ''));
        $targetPeriode = preg_match('/^\d{4}-\d{2}$/', $requestedPeriode) === 1
            ? $requestedPeriode
            : now()->format('Y-m');

        $startedAt = microtime(true);
        $result = $batchTagihanService->executeBatchBilling($targetPeriode);
        $elapsedSeconds = round(microtime(true) - $startedAt, 2);

        $priode = $result['periode'];
        $periodeLabel = Carbon::createFromFormat('Y-m', $priode)->translatedFormat('F Y');

        // Pencegahan duplikasi batch pada periode yang sama (TC_028)
        if ($result['already_processed']) {
            return redirect()
                ->route('admin.tagihan', ['periode' => $priode])
                ->with('error', "Tagihan periode {$periodeLabel} sudah terbit sebelumnya ({$result['existing_bills_count']} unit). Sistem menolak pembuatan tagihan ganda.");
        }

        if ($result['new_bills_count'] === 0) {
            return redirect()
                ->route('admin.tagihan', ['periode' => $priode])
                ->with('error', "Tidak ada data bacaan meter valid yang menunggu diproses untuk periode {$periodeLabel}.");
        }

        $notaCount = $result['nota_issued_count'];
        $newCount = $result['new_bills_count'];
        $suratCount = $result['surat_generated_count'];

        Notifikasi::create([
            'user_id' => $user->id,
            'unit_id' => null,
            'judul' => 'Batch Tagihan Massal Selesai',
            'pesan' => "Kalkulasi tarif progresif untuk {$notaCount} tagihan periode {$periodeLabel} selesai diproses dalam {$elapsedSeconds} detik ({$newCount} tagihan baru terbit, {$suratCount} surat tunggakan).",
            'tipe' => 'batch',
            'link' => route('admin.tagihan', ['periode' => $priode]),
            'status_terkirim' => true,
            'is_read' => false,
            'tgl_kirim' => Carbon::now(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'aktivitas' => "Mengeksekusi proses batch tagihan air massal periode {$periodeLabel} ({$newCount} tagihan baru terbit, total {$notaCount} unit selesai dalam {$elapsedSeconds} detik)",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()
            ->route('admin.tagihan', ['periode' => $priode])
            ->with('success', "Batch Processing periode {$periodeLabel} selesai dalam {$elapsedSeconds} detik: {$newCount} tagihan baru terbit (Total {$notaCount}/{$notaCount} unit siap cetak, {$suratCount} surat tunggakan >= 4 bulan).");
    }

    /**
     * Cetak Nota Fisik Rekening Air Sumur Bor langsung dari Daftar Tagihan (Sebelum / Sesudah Dibayar)
     */
    public function cetakNotaTagihan(int $id, RsaSignatureService $rsaSignatureService)
    {
        $tagihan = Tagihan::with(['unit.blok', 'bacaanMeter', 'pembayarans'])->findOrFail($id);
        $pembayaran = $tagihan->pembayarans?->sortByDesc('id')->first();

        $baseUrl = request()->root();
        $signatureData = $rsaSignatureService->issueTagihanSignature($tagihan, $baseUrl);

        $canonicalData = $signatureData['canonical_data'];
        $hashSha256 = $signatureData['hash_sha256'];
        $signatureRsa = $signatureData['signature_rsa'];
        $qrPayload = $signatureData['qr_payload'];

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Mencetak nota fisik rekening air untuk Unit {$tagihan->unit->nomor_rumah} (Tagihan #TGH-2611-".str_pad((string) $tagihan->id, 4, '0', STR_PAD_LEFT).')',
            'ip_address' => request()->ip(),
            'timestamp' => Carbon::now(),
        ]);

        $qrSvg = (string) QrCode::format('svg')
            ->size(160)
            ->margin(1)
            ->errorCorrection('M')
            ->generate($qrPayload);

        return view('admin.nota-cetak', compact('tagihan', 'pembayaran', 'hashSha256', 'signatureRsa', 'canonicalData', 'qrPayload', 'qrSvg'));
    }

    /**
     * Daftar Surat Penyampaian Otomatis untuk Unit Menunggak >= 4 Bulan
     */
    public function suratTunggakan(SuratTunggakanService $suratService)
    {
        $now = Carbon::now();
        $periodeStr = $now->translatedFormat('F Y');
        $daftarSurat = $suratService->getDaftarSuratOtomatis();
        $totalTerflagCount = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();

        return view('admin.surat-tunggakan', compact('daftarSurat', 'periodeStr', 'totalTerflagCount'));
    }

    /**
     * Cetak / Unduh PDF Surat Penyampaian Resmi (Template PT. Arista Jaya) via DomPDF
     */
    public function cetakSuratTunggakan(int $unitId, SuratTunggakanService $suratService)
    {
        $surat = $suratService->getSuratUntukUnit($unitId);

        if (! $surat) {
            return redirect()->route('admin.surat-tunggakan')->with('error', 'Unit tidak memenuhi kriteria tunggakan >= 4 bulan.');
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Mencetak Surat Penyampaian Tunggakan ({$surat['nomor_surat']}) untuk Unit {$surat['nomor_rumah']} ({$surat['jumlah_bulan']} bulan)",
            'ip_address' => request()->ip(),
            'timestamp' => Carbon::now(),
        ]);

        $pdf = Pdf::loadView('admin.surat-tunggakan-pdf', compact('surat'))
            ->setPaper('a4', 'portrait');

        $safeUnit = preg_replace('/[^A-Za-z0-9\-_]/', '_', (string) $surat['nomor_rumah']);

        return $pdf->download("Surat_Peringatan_{$safeUnit}.pdf");
    }

    /**
     * 4. Pembayaran (Pencatatan Transaksi)
     * Sesuai SRS Page 11 & Lampiran B (d)
     */
    public function pembayaran(Request $request)
    {
        $now = Carbon::now();
        $periodeStr = $now->translatedFormat('F Y');

        // Daftar tagihan belum lunas untuk panel kanan
        $unpaidBills = Tagihan::with(['unit.blok', 'bacaanMeter'])
            ->where('status_bayar', '!=', 'lunas')
            ->orderBy('id', 'asc')
            ->get();

        // Tagihan terpilih jika ada query param
        $selectedTagihanId = $request->get('tagihan_id');
        $selectedTagihan = null;
        if ($selectedTagihanId) {
            $selectedTagihan = Tagihan::with(['unit.blok'])->find($selectedTagihanId);
        } elseif ($unpaidBills->isNotEmpty()) {
            $selectedTagihan = $unpaidBills->first();
        }

        $totalTerflagCount = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();

        return view('admin.pembayaran', compact('unpaidBills', 'selectedTagihan', 'periodeStr', 'totalTerflagCount'));
    }

    /**
     * Simpan Pembayaran
     */
    public function storePembayaran(Request $request)
    {
        $request->validate([
            'tagihan_id' => 'required|exists:tagihans,id',
            'jumlah_bayar' => 'required|numeric|min:1000',
            'metode_bayar' => 'required|in:Tunai,Transfer',
            'tgl_bayar' => 'required|date',
        ]);

        $tagihan = Tagihan::with('unit')->findOrFail($request->tagihan_id);
        $jumlahBayar = (float) $request->jumlah_bayar;
        $user = Auth::user();

        $pembayaran = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'jumlah_bayar' => $jumlahBayar,
            'metode_bayar' => $request->metode_bayar,
            'tgl_bayar' => $request->tgl_bayar,
            'status_pembayaran' => 'Sukses',
        ]);

        // Aturan status: total bayar >= total tagihan -> lunas, selain itu dibayar sebagian
        if ($jumlahBayar >= $tagihan->total_tagihan) {
            $tagihan->status_bayar = 'lunas';
        } else {
            $tagihan->status_bayar = 'sebagian';
        }
        $tagihan->save();

        AuditLog::create([
            'user_id' => $user->id,
            'aktivitas' => 'Mencatat pembayaran Rp '.number_format($jumlahBayar, 0, ',', '.')." ({$request->metode_bayar}) untuk Unit {$tagihan->unit->nomor_rumah} - Status: {$tagihan->status_bayar}",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.riwayat-pembayaran')->with('success', "Pembayaran Unit {$tagihan->unit->nomor_rumah} berhasil dicatat dengan status ".strtoupper($tagihan->status_bayar).'.');
    }

    /**
     * 5. Riwayat Pembayaran & Cetak Nota Kriptografis
     * Sesuai SRS Page 12 & Lampiran B (e)
     */
    public function riwayatPembayaran(Request $request)
    {
        $now = Carbon::now();
        $periodeStr = $now->translatedFormat('F Y');
        $bloks = BlokHunian::orderBy('nama_blok')->get();

        $selectedPeriode = $request->has('periode') ? (string) $request->get('periode') : $now->format('Y-m');
        $selectedBlok = $request->get('blok');
        $selectedMetode = $request->get('metode');
        $selectedStatus = $request->get('status');

        $existingPaymentMonths = Pembayaran::query()
            ->selectRaw("DISTINCT DATE_FORMAT(tgl_bayar, '%Y-%m') as ym")
            ->pluck('ym')
            ->filter()
            ->all();
        $periodeOptions = $this->buildPeriodeOptions($existingPaymentMonths);

        $query = Pembayaran::with(['tagihan.unit.blok'])
            ->when($selectedPeriode !== '' && preg_match('/^\d{4}-\d{2}$/', $selectedPeriode) === 1, function ($q) use ($selectedPeriode) {
                [$year, $month] = explode('-', $selectedPeriode);
                $q->whereYear('tgl_bayar', (int) $year)->whereMonth('tgl_bayar', (int) $month);
            })
            ->when($selectedBlok, function ($q) use ($selectedBlok) {
                $q->whereHas('tagihan.unit', function ($u) use ($selectedBlok) {
                    $u->where('blok_id', $selectedBlok);
                });
            })
            ->when($selectedMetode, function ($q) use ($selectedMetode) {
                $q->where('metode_bayar', $selectedMetode);
            })
            ->when($selectedStatus, function ($q) use ($selectedStatus) {
                $q->whereHas('tagihan', function ($t) use ($selectedStatus) {
                    $t->where('status_bayar', $selectedStatus);
                });
            })
            ->latest('tgl_bayar');

        $pembayarans = $query->paginate(12)->withQueryString();
        $totalTerflagCount = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();

        return view('admin.riwayat-pembayaran', compact('pembayarans', 'bloks', 'periodeStr', 'periodeOptions', 'selectedPeriode', 'selectedBlok', 'selectedMetode', 'selectedStatus', 'totalTerflagCount'));
    }

    /**
     * Cetak Nota Kriptografis Ber-QR (RSA-2048 + SHA-256) dari Riwayat Pembayaran
     */
    public function cetakNota($id, RsaSignatureService $rsaSignatureService)
    {
        $pembayaran = Pembayaran::with(['tagihan.unit.blok', 'tagihan.bacaanMeter'])->findOrFail($id);
        $tagihan = $pembayaran->tagihan;

        $user = Auth::user();
        $baseUrl = request()->root();
        $signatureData = $rsaSignatureService->issueTagihanSignature($tagihan, $baseUrl);

        $canonicalData = $signatureData['canonical_data'];
        $hashSha256 = $signatureData['hash_sha256'];
        $signatureRsa = $signatureData['signature_rsa'];
        $qrPayload = $signatureData['qr_payload'];

        AuditLog::create([
            'user_id' => $user->id,
            'aktivitas' => "Mencetak nota kriptografis resmi untuk Unit {$tagihan->unit->nomor_rumah} (Hash: ".substr($hashSha256, 0, 10).'...)',
            'ip_address' => request()->ip(),
            'timestamp' => Carbon::now(),
        ]);

        $qrSvg = (string) QrCode::format('svg')
            ->size(160)
            ->margin(1)
            ->errorCorrection('M')
            ->generate($qrPayload);

        return view('admin.nota-cetak', compact('tagihan', 'pembayaran', 'hashSha256', 'signatureRsa', 'canonicalData', 'qrPayload', 'qrSvg'));
    }

    /**
     * Helper agregasi data Laporan Bulanan (Per Blok) sesuai filter Bulan, Tahun, Blok, dan Status.
     *
     * @return array<string, mixed>
     */
    private function buildLaporanBulananData(Request $request): array
    {
        $now = Carbon::now();
        $selectedBulan = max(1, min(12, (int) $request->query('bulan', $now->month)));
        $selectedTahun = max(2020, min(2099, (int) $request->query('tahun', $now->year)));
        $selectedBlok = $request->query('blok');
        $selectedStatus = trim((string) $request->query('status', ''));

        $periodeKey = sprintf('%04d-%02d', $selectedTahun, $selectedBulan);

        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $bulanShort = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $periodeLabel = ($bulanNames[$selectedBulan] ?? 'Bulan').' '.$selectedTahun;
        $periodeFileSuffix = ($bulanShort[$selectedBulan] ?? 'Bln').$selectedTahun;

        $bloks = BlokHunian::withCount('unitHunians')->orderBy('nama_blok')->get();

        // Satu kueri agregasi terkelompok per blok untuk periode terpilih (anti N+1)
        $statsQuery = Tagihan::query()
            ->join('unit_hunians', 'tagihans.unit_id', '=', 'unit_hunians.id')
            ->where('tagihans.priode', $periodeKey)
            ->when($selectedBlok, function ($q) use ($selectedBlok) {
                $q->where('unit_hunians.blok_id', (int) $selectedBlok);
            })
            ->when($selectedStatus !== '' && $selectedStatus !== 'semua', function ($q) use ($selectedStatus) {
                if ($selectedStatus === 'lunas') {
                    $q->where('tagihans.status_bayar', 'lunas');
                } elseif ($selectedStatus === 'belum_lunas') {
                    $q->where('tagihans.status_bayar', '!=', 'lunas');
                }
            })
            ->selectRaw("
                unit_hunians.blok_id,
                COUNT(DISTINCT tagihans.unit_id) AS unit_count,
                COUNT(tagihans.id) AS transaksi_count,
                COALESCE(SUM(tagihans.volume_pemakaian), 0) AS total_vol,
                COALESCE(SUM(tagihans.total_tagihan), 0) AS total_tagihan,
                COALESCE(SUM(CASE WHEN tagihans.status_bayar = 'lunas' THEN tagihans.total_tagihan ELSE 0 END), 0) AS total_bayar
            ")
            ->groupBy('unit_hunians.blok_id')
            ->get()
            ->keyBy('blok_id');

        $rekapBlok = [];
        $grandTotalTagihan = 0.0;
        $grandTotalTerbayar = 0.0;
        $grandTotalVolume = 0.0;
        $grandTotalUnit = 0;
        $transaksiCount = 0;

        foreach ($bloks as $blok) {
            if ($selectedBlok && (int) $selectedBlok !== (int) $blok->id) {
                continue;
            }

            $row = $statsQuery->get($blok->id);
            $unitCount = (int) ($row->unit_count ?? 0);
            $rowTransaksi = (int) ($row->transaksi_count ?? 0);
            $totalVol = (float) ($row->total_vol ?? 0);
            $totalTagihan = (float) ($row->total_tagihan ?? 0);
            $totalBayar = (float) ($row->total_bayar ?? 0);

            $rekapBlok[] = [
                'nama' => $blok->nama_blok,
                'jumlah_unit' => $unitCount,
                'total_pemakaian' => $totalVol,
                'total_tagihan' => $totalTagihan,
                'terbayar' => $totalBayar,
                'sisa' => max(0, $totalTagihan - $totalBayar),
            ];

            $grandTotalUnit += $unitCount;
            $transaksiCount += $rowTransaksi;
            $grandTotalVolume += $totalVol;
            $grandTotalTagihan += $totalTagihan;
            $grandTotalTerbayar += $totalBayar;
        }

        $grandBelumLunas = max(0, $grandTotalTagihan - $grandTotalTerbayar);

        $selectedBlokModel = $selectedBlok ? $bloks->firstWhere('id', (int) $selectedBlok) : null;
        $selectedBlokName = $selectedBlokModel ? $selectedBlokModel->nama_blok : 'Semua Blok (A – F)';
        $selectedStatusLabel = match ($selectedStatus) {
            'lunas' => 'Lunas',
            'belum_lunas' => 'Belum Lunas',
            default => 'Semua',
        };

        return [
            'bloks' => $bloks,
            'bulanNames' => $bulanNames,
            'selectedBulan' => $selectedBulan,
            'selectedTahun' => $selectedTahun,
            'selectedBlok' => $selectedBlok,
            'selectedStatus' => $selectedStatus,
            'selectedBlokName' => $selectedBlokName,
            'selectedStatusLabel' => $selectedStatusLabel,
            'periodeKey' => $periodeKey,
            'periodeLabel' => $periodeLabel,
            'periodeStr' => $periodeLabel,
            'periodeFileSuffix' => $periodeFileSuffix,
            'rekapBlok' => $rekapBlok,
            'transaksiCount' => $transaksiCount,
            'grandTotalUnit' => $grandTotalUnit,
            'grandTotalVolume' => $grandTotalVolume,
            'grandTotalTagihan' => $grandTotalTagihan,
            'grandTotalTerbayar' => $grandTotalTerbayar,
            'grandBelumLunas' => $grandBelumLunas,
        ];
    }

    /**
     * 6. Laporan Rekapitulasi Bulanan & Harian + Ekspor PDF
     * Sesuai SRS Page 13 & Lampiran B (f)
     */
    public function laporan(Request $request)
    {
        $now = Carbon::now();
        $mode = $request->get('mode', 'bulanan'); // bulanan | harian
        $selectedTanggal = $request->get('tanggal', $now->format('Y-m-d'));
        $tanggalFormatted = Carbon::parse($selectedTanggal)->translatedFormat('d F Y');

        $bulanan = $this->buildLaporanBulananData($request);
        $selectedBlok = $bulanan['selectedBlok'];

        // Data Laporan Harian (Per Hari)
        $pembayaranHarian = Pembayaran::with(['tagihan.unit.blok'])
            ->whereDate('tgl_bayar', $selectedTanggal)
            ->when($selectedBlok, function ($q) use ($selectedBlok) {
                $q->whereHas('tagihan.unit', function ($u) use ($selectedBlok) {
                    $u->where('blok_id', $selectedBlok);
                });
            })
            ->latest('created_at')
            ->get();

        $bacaanHarian = BacaanMeter::with(['unit.blok', 'petugas'])
            ->whereDate('created_at', $selectedTanggal)
            ->when($selectedBlok, function ($q) use ($selectedBlok) {
                $q->whereHas('unit', function ($u) use ($selectedBlok) {
                    $u->where('blok_id', $selectedBlok);
                });
            })
            ->latest('created_at')
            ->get();

        $harianTotalMasuk = (float) $pembayaranHarian->sum('jumlah_bayar');
        $harianTotalTunai = (float) $pembayaranHarian->where('metode_bayar', 'Tunai')->sum('jumlah_bayar');
        $harianTotalTransfer = (float) $pembayaranHarian->where('metode_bayar', 'Transfer')->sum('jumlah_bayar');
        $harianJumlahTransaksi = $pembayaranHarian->count();
        $harianJumlahBacaan = $bacaanHarian->count();
        $harianTotalVolume = (float) $bacaanHarian->sum(fn ($b) => max(0, (float) $b->angka_meter - (float) $b->angka_meter_bulanlalu));

        $totalTerflagCount = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();

        return view('admin.laporan', array_merge($bulanan, [
            'mode' => $mode,
            'selectedTanggal' => $selectedTanggal,
            'tanggalFormatted' => $tanggalFormatted,
            'pembayaranHarian' => $pembayaranHarian,
            'bacaanHarian' => $bacaanHarian,
            'harianTotalMasuk' => $harianTotalMasuk,
            'harianTotalTunai' => $harianTotalTunai,
            'harianTotalTransfer' => $harianTotalTransfer,
            'harianJumlahTransaksi' => $harianJumlahTransaksi,
            'harianJumlahBacaan' => $harianJumlahBacaan,
            'harianTotalVolume' => $harianTotalVolume,
            'totalTerflagCount' => $totalTerflagCount,
        ]));
    }

    /**
     * Ekspor Laporan Bulanan ke PDF via DomPDF (dengan pencegahan cetak PDF kosong)
     */
    public function exportLaporanPdf(Request $request)
    {
        $data = $this->buildLaporanBulananData($request);

        // Pencegahan cetak PDF kosong jika periode belum memiliki data transaksi (TC_059)
        if ($data['transaksiCount'] === 0) {
            return redirect()
                ->route('admin.laporan', $request->query())
                ->with('error', 'Tidak ada data transaksi pada periode terpilih');
        }

        $filename = "Laporan_Bulanan_{$data['periodeFileSuffix']}.pdf";

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Mengekspor laporan rekapitulasi bulanan periode {$data['periodeLabel']} format PDF ({$filename})",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        $pdf = Pdf::loadView('admin.laporan-pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * 7. Data Master (Unit, Tarif Progresif, Petugas, Blok)
     * Sesuai SRS Page 14 & Lampiran B (g)
     */
    public function master(Request $request)
    {
        $now = Carbon::now();
        $periodeStr = $now->translatedFormat('F Y');

        $activeTab = $request->get('tab', 'unit'); // unit | tarif | petugas | blok

        $bloks = BlokHunian::withCount('unitHunians')->orderBy('nama_blok')->get();
        $tarifs = TarifAir::orderBy('batas_bawah')->get();
        $petugasList = User::role('petugas')->get();

        $selectedBlok = $request->get('blok');
        $selectedStatus = $request->get('status');

        $units = UnitHunian::with('blok')
            ->when($selectedBlok, function ($q) use ($selectedBlok) {
                $q->where('blok_id', $selectedBlok);
            })
            ->when($selectedStatus, function ($q) use ($selectedStatus) {
                $q->where('status_huni', $selectedStatus);
            })
            ->orderBy('nomor_rumah')
            ->paginate(15)
            ->withQueryString();

        $totalUnitsCount = UnitHunian::count();
        $totalBloksCount = $bloks->count();
        $totalTarifsCount = $tarifs->count();
        $totalTerflagCount = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();
        $petugasUtama = $petugasList->first()?->name ?? 'Belum Ditugaskan';

        $blokMasterRows = BacaanMeter::query()
            ->join('unit_hunians', 'bacaan_meters.unit_id', '=', 'unit_hunians.id')
            ->where('bacaan_meters.bulan', $now->month)
            ->where('bacaan_meters.tahun', $now->year)
            ->whereIn('bacaan_meters.status_verifikasi', ['valid', 'ter_flag'])
            ->selectRaw('unit_hunians.blok_id, COUNT(DISTINCT bacaan_meters.unit_id) AS tercatat_count, SUM(CASE WHEN (bacaan_meters.angka_meter - bacaan_meters.angka_meter_bulanlalu) > 0 THEN (bacaan_meters.angka_meter - bacaan_meters.angka_meter_bulanlalu) ELSE 0 END) AS total_volume')
            ->groupBy('unit_hunians.blok_id')
            ->get()
            ->keyBy('blok_id');

        $blokMasterStats = [];
        foreach ($bloks as $b) {
            $row = $blokMasterRows->get($b->id);
            $blokMasterStats[$b->id] = [
                'tercatat' => (int) ($row->tercatat_count ?? 0),
                'volume' => (int) ($row->total_volume ?? 0),
            ];
        }

        return view('admin.master', compact(
            'activeTab',
            'units',
            'bloks',
            'tarifs',
            'petugasList',
            'petugasUtama',
            'blokMasterStats',
            'totalUnitsCount',
            'totalBloksCount',
            'totalTarifsCount',
            'totalTerflagCount',
            'periodeStr'
        ));
    }

    /**
     * Tambah Unit Baru pada Data Master
     */
    public function storeUnit(Request $request)
    {
        $request->validate([
            'nomor_rumah' => 'required|string|unique:unit_hunians,nomor_rumah',
            'nama_pemilik' => 'required|string',
            'blok_id' => 'required|exists:blok_hunians,id',
            'nomor_wa' => 'nullable|string',
        ]);

        UnitHunian::create([
            'nomor_rumah' => $request->nomor_rumah,
            'nama_pemilik' => $request->nama_pemilik,
            'blok_id' => $request->blok_id,
            'nomor_wa' => $request->nomor_wa ?? '0812'.rand(10000000, 99999999),
            'status_huni' => 'Dihuni',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Menambahkan unit hunian baru: {$request->nomor_rumah} atas nama {$request->nama_pemilik}",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.master', ['tab' => 'unit'])->with('success', "Unit {$request->nomor_rumah} berhasil ditambahkan ke Data Master.");
    }

    /**
     * Update data Unit Hunian (Kelola)
     */
    public function updateUnit(Request $request, int|string $id)
    {
        $unit = UnitHunian::findOrFail($id);

        $request->validate([
            'nomor_rumah' => 'required|string|unique:unit_hunians,nomor_rumah,'.$unit->id,
            'nama_pemilik' => 'required|string',
            'blok_id' => 'required|exists:blok_hunians,id',
            'nomor_wa' => 'nullable|string',
            'status_huni' => 'required|in:Dihuni,Kosong',
        ]);

        $unit->update([
            'nomor_rumah' => $request->nomor_rumah,
            'nama_pemilik' => $request->nama_pemilik,
            'blok_id' => $request->blok_id,
            'nomor_wa' => $request->nomor_wa ?? $unit->nomor_wa,
            'status_huni' => $request->status_huni,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Memperbarui data unit: {$unit->nomor_rumah} ({$unit->nama_pemilik})",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.master', ['tab' => 'unit'])->with('success', "Data unit {$unit->nomor_rumah} berhasil diperbarui.");
    }

    /**
     * Hapus Unit Hunian
     */
    public function destroyUnit(int|string $id)
    {
        $unit = UnitHunian::findOrFail($id);
        $nomor = $unit->nomor_rumah;
        $unit->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Menghapus unit hunian: {$nomor}",
            'ip_address' => request()->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.master', ['tab' => 'unit'])->with('success', "Unit {$nomor} berhasil dihapus dari sistem.");
    }

    /**
     * Download format template CSV untuk impor data unit
     */
    public function downloadUnitTemplate()
    {
        $csv = "nomor_unit,blok,nama_pemilik,nomor_wa,status_huni\n";
        $csv .= "A-01,Blok A,Warga A 01,081222014309,Dihuni\n";
        $csv .= "A-02,Blok A,Warga A 02,081267712722,Dihuni\n";
        $csv .= "B-01,Blok B,Bapak Rudi,081234567890,Dihuni\n";
        $csv .= "C-05,Blok C,Ibu Rahma,081298765432,Kosong\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_data_unit.csv"',
        ]);
    }

    /**
     * Impor data unit hunian dari file Excel / CSV
     */
    public function importUnits(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return back()->with('error', 'Gagal membaca berkas yang diunggah.');
        }

        $allBloks = BlokHunian::all()->keyBy(function ($item) {
            return strtolower(trim(str_replace('blok', '', $item->nama_blok)));
        });

        $countSuccess = 0;
        $rowNumber = 0;

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = (strpos($firstLine, ';') !== false && strpos($firstLine, ',') === false) ? ';' : ',';

        while (($data = fgetcsv($handle, 2000, $delimiter)) !== false) {
            $rowNumber++;
            if ($rowNumber === 1) {
                if (isset($data[0]) && in_array(strtolower(trim($data[0])), ['nomor_unit', 'nomor unit', 'nomor_rumah', 'unit', 'no'])) {
                    continue;
                }
            }

            if (empty($data[0])) {
                continue;
            }

            $nomorUnit = trim($data[0]);
            $blokName = isset($data[1]) ? trim($data[1]) : '';
            $namaPemilik = isset($data[2]) ? trim($data[2]) : 'Warga '.$nomorUnit;
            $nomorWa = isset($data[3]) ? trim($data[3]) : '0812'.rand(10000000, 99999999);
            $statusHuni = isset($data[4]) && strtolower(trim($data[4])) === 'kosong' ? 'Kosong' : 'Dihuni';

            $cleanBlokKey = strtolower(trim(str_replace('blok', '', $blokName)));
            $blok = $allBloks->get($cleanBlokKey);

            if (! $blok && preg_match('/^([a-fA-F])/i', $nomorUnit, $matches)) {
                $blok = $allBloks->get(strtolower($matches[1]));
            }

            if (! $blok) {
                $blok = BlokHunian::first();
            }

            UnitHunian::updateOrCreate(
                ['nomor_rumah' => $nomorUnit],
                [
                    'blok_id' => $blok ? $blok->id : 1,
                    'nama_pemilik' => $namaPemilik ?: 'Warga '.$nomorUnit,
                    'nomor_wa' => $nomorWa,
                    'status_huni' => $statusHuni,
                ]
            );

            $countSuccess++;
        }

        fclose($handle);

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Mengimpor {$countSuccess} data unit hunian via berkas Excel/CSV",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.master', ['tab' => 'unit'])->with('success', "Berhasil memproses dan mengimpor {$countSuccess} data unit hunian.");
    }

    /**
     * Tambah / Ubah Skema Tarif Progresif
     */
    public function storeTarif(Request $request)
    {
        $request->validate([
            'blok_konsumsi' => 'required|string',
            'batas_bawah' => 'required|numeric',
            'batas_atas' => 'nullable|numeric',
            'tarif_per_m3' => 'required|numeric',
        ]);

        TarifAir::create($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Menambahkan tingkatan tarif progresif baru: {$request->blok_konsumsi} (Rp {$request->tarif_per_m3}/m³)",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.master', ['tab' => 'tarif'])->with('success', 'Skema tarif baru berhasil disimpan.');
    }

    /**
     * Update Skema Tarif Progresif
     */
    public function updateTarif(Request $request, int|string $id)
    {
        $tarif = TarifAir::findOrFail($id);

        $request->validate([
            'blok_konsumsi' => 'required|string',
            'batas_bawah' => 'required|numeric',
            'batas_atas' => 'nullable|numeric',
            'tarif_per_m3' => 'required|numeric',
        ]);

        $tarif->update([
            'blok_konsumsi' => $request->blok_konsumsi,
            'batas_bawah' => $request->batas_bawah,
            'batas_atas' => $request->filled('batas_atas') ? $request->batas_atas : null,
            'tarif_per_m3' => $request->tarif_per_m3,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Memperbarui skema tarif: {$tarif->blok_konsumsi} (Rp {$tarif->tarif_per_m3}/m³)",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.master', ['tab' => 'tarif'])->with('success', "Skema tarif {$tarif->blok_konsumsi} berhasil diperbarui.");
    }

    /**
     * Hapus Skema Tarif Progresif
     */
    public function destroyTarif(Request $request, int|string $id)
    {
        $tarif = TarifAir::findOrFail($id);
        $label = $tarif->blok_konsumsi;

        if ($tarif->tagihans()->exists()) {
            return redirect()->route('admin.master', ['tab' => 'tarif'])
                ->with('error', "Skema tarif {$label} tidak dapat dihapus karena sudah digunakan pada data tagihan.");
        }

        $tarif->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Menghapus skema tarif progresif: {$label}",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.master', ['tab' => 'tarif'])->with('success', "Skema tarif {$label} berhasil dihapus.");
    }

    /**
     * Tambah Petugas Lapangan Baru pada Data Master
     */
    public function storePetugas(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'zona_type' => 'nullable|string',
            'zona_blok' => 'nullable|array',
        ]);

        $emailInput = trim($request->input('email'));
        if (! str_contains($emailInput, '@')) {
            $emailInput = strtolower(str_replace(' ', '', $emailInput)).'@grandsul.web.id';
        }

        // Validasi keunikan email
        if (User::where('email', $emailInput)->exists()) {
            return back()->withInput()->with('error', "Username/Email '{$emailInput}' sudah digunakan oleh pengguna lain.");
        }

        $zonaPenugasan = 'Semua Blok (A, B, C, D, E, F)';
        if ($request->input('zona_type') === 'spesifik' && ! empty($request->input('zona_blok'))) {
            $zonaPenugasan = implode(', ', $request->input('zona_blok'));
        }

        $petugas = User::create([
            'name' => $request->input('name'),
            'email' => $emailInput,
            'password' => Hash::make($request->input('password')),
            'zona_penugasan' => $zonaPenugasan,
        ]);

        $petugas->assignRole('petugas');

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Menambahkan petugas lapangan baru: {$petugas->name} ({$petugas->email}) dengan zona {$zonaPenugasan}",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.master', ['tab' => 'petugas'])
            ->with('success', "Petugas lapangan '{$petugas->name}' berhasil ditambahkan dengan username/email: {$petugas->email}.");
    }

    /**
     * Update data Petugas Lapangan (Kelola Username, Password, dan Zona Penugasan)
     */
    public function updatePetugas(Request $request, int|string $id)
    {
        $petugas = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
            'zona_type' => 'nullable|string',
            'zona_blok' => 'nullable|array',
        ]);

        $emailInput = trim($request->input('email'));
        if (! str_contains($emailInput, '@')) {
            $emailInput = strtolower(str_replace(' ', '', $emailInput)).'@grandsul.web.id';
        }

        // Validasi keunikan email selain user ini
        if (User::where('email', $emailInput)->where('id', '!=', $petugas->id)->exists()) {
            return back()->withInput()->with('error', "Username/Email '{$emailInput}' sudah digunakan oleh akun lain.");
        }

        $zonaPenugasan = 'Semua Blok (A, B, C, D, E, F)';
        if ($request->input('zona_type') === 'spesifik' && ! empty($request->input('zona_blok'))) {
            $zonaPenugasan = implode(', ', $request->input('zona_blok'));
        }

        $petugas->name = $request->input('name');
        $petugas->email = $emailInput;
        $petugas->zona_penugasan = $zonaPenugasan;

        if ($request->has('is_active')) {
            $petugas->is_active = $request->boolean('is_active');
        }

        if ($request->filled('password')) {
            $petugas->password = Hash::make($request->input('password'));
        }

        $petugas->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Memperbarui akun petugas: {$petugas->name} ({$petugas->email}) dengan zona {$zonaPenugasan}".($request->filled('password') ? ' (password diperbarui)' : ''),
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.master', ['tab' => 'petugas'])
            ->with('success', "Data akun petugas '{$petugas->name}' berhasil diperbarui.");
    }

    /**
     * Toggle status aktif/non-aktif Petugas Lapangan
     */
    public function togglePetugasStatus(Request $request, int|string $id)
    {
        $petugas = User::findOrFail($id);

        if ($petugas->id === Auth::id()) {
            return redirect()->route('admin.master', ['tab' => 'petugas'])
                ->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.');
        }

        $petugas->is_active = ! (bool) ($petugas->is_active ?? true);
        $petugas->save();

        $statusStr = $petugas->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Mengubah status petugas {$petugas->name} ({$petugas->email}) menjadi {$statusStr}",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.master', ['tab' => 'petugas'])
            ->with('success', "Akun petugas '{$petugas->name}' berhasil {$statusStr}.");
    }

    /**
     * Hapus Petugas Lapangan
     */
    public function destroyPetugas(Request $request, int|string $id)
    {
        $petugas = User::findOrFail($id);

        if ($petugas->id === Auth::id()) {
            return redirect()->route('admin.master', ['tab' => 'petugas'])
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if (User::role('petugas')->count() <= 1) {
            return redirect()->route('admin.master', ['tab' => 'petugas'])
                ->with('error', 'Tidak dapat menghapus petugas terakhir. Sistem membutuhkan minimal 1 petugas lapangan aktif.');
        }

        $nama = $petugas->name;
        $email = $petugas->email;
        $petugas->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Menghapus petugas lapangan: {$nama} ({$email})",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.master', ['tab' => 'petugas'])
            ->with('success', "Petugas lapangan '{$nama}' berhasil dihapus dari sistem.");
    }

    /**
     * Tambah Blok Hunian Baru pada Data Master
     */
    public function storeBlok(Request $request)
    {
        $request->validate([
            'nama_blok' => 'required|string|max:100|unique:blok_hunians,nama_blok',
            'keterangan_wilayah' => 'nullable|string|max:255',
        ]);

        $blok = BlokHunian::create([
            'nama_blok' => trim($request->input('nama_blok')),
            'keterangan_wilayah' => $request->input('keterangan_wilayah'),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => "Menambahkan blok hunian baru: {$blok->nama_blok}",
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now(),
        ]);

        return redirect()->route('admin.master', ['tab' => 'blok'])
            ->with('success', "Blok hunian '{$blok->nama_blok}' berhasil ditambahkan ke Data Master.");
    }

    /**
     * 8. Audit Log Aktivitas
     * Sesuai SRS Page 15 & Lampiran B (h)
     */
    public function audit(Request $request)
    {
        $now = Carbon::now();
        $periodeStr = $now->translatedFormat('F Y');

        $tanggalAwal = trim((string) $request->query('tanggal_awal', ''));
        $tanggalAkhir = trim((string) $request->query('tanggal_akhir', ''));
        $selectedUserId = $request->query('user_id');
        $selectedJenisAktivitas = trim((string) $request->query('jenis_aktivitas', ''));
        $keyword = trim((string) $request->query('aktivitas', ''));
        $dateError = null;

        // Validasi rentang tanggal terbalik (TC_055)
        if ($tanggalAwal !== '' && $tanggalAkhir !== '') {
            try {
                $startDate = Carbon::parse($tanggalAwal)->startOfDay();
                $endDate = Carbon::parse($tanggalAkhir)->endOfDay();

                if ($endDate->lt($startDate)) {
                    $dateError = 'Rentang tanggal tidak valid (Tanggal akhir harus setelah tanggal awal)';
                }
            } catch (\Throwable) {
                $dateError = 'Format rentang tanggal tidak valid.';
            }
        }

        if ($dateError !== null) {
            $logs = AuditLog::with('user.roles')->whereRaw('1 = 0')->paginate(15)->withQueryString();
        } else {
            $query = AuditLog::with('user.roles')->latest('timestamp')->latest('id');

            if ($tanggalAwal !== '') {
                $query->where('timestamp', '>=', Carbon::parse($tanggalAwal)->startOfDay());
            }

            if ($tanggalAkhir !== '') {
                $query->where('timestamp', '<=', Carbon::parse($tanggalAkhir)->endOfDay());
            }

            if ($selectedUserId !== null && $selectedUserId !== '') {
                $query->where('user_id', (int) $selectedUserId);
            }

            if ($selectedJenisAktivitas !== '') {
                $query->where('aktivitas', 'like', '%'.$selectedJenisAktivitas.'%');
            }

            if ($keyword !== '') {
                $query->where('aktivitas', 'like', '%'.$keyword.'%');
            }

            $logs = $query->paginate(15)->withQueryString();
        }

        $users = User::with('roles')->orderBy('name')->get();
        $activityOptions = [
            'Mengirim pencatatan meter',
            'Memproses tagihan batch',
            'Memvalidasi data ter-flag',
            'Mencatat pembayaran',
            'Mencetak Surat Penyampaian',
            'Mencetak nota',
            'Login ke sistem',
        ];
        $totalTerflagCount = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();

        return view('admin.audit', compact(
            'logs',
            'periodeStr',
            'totalTerflagCount',
            'users',
            'activityOptions',
            'tanggalAwal',
            'tanggalAkhir',
            'selectedUserId',
            'selectedJenisAktivitas',
            'keyword',
            'dateError'
        ));
    }

    /**
     * Ambil notifikasi terkini untuk polling background sistem (khusus Admin)
     */
    public function getUnreadNotifikasi()
    {
        $adminId = Auth::id();
        $hasUserIdCol = Schema::hasColumn('notifikasis', 'user_id');

        $list = Notifikasi::query()
            ->where('is_read', false)
            ->when($hasUserIdCol, function ($query) use ($adminId) {
                $query->where(function ($q) use ($adminId) {
                    $q->where('user_id', $adminId)
                        ->orWhereNull('user_id');
                });
            })
            ->orderByDesc('tgl_kirim')
            ->orderByDesc('id')
            ->take(15)
            ->get();

        $data = $list->map(function ($n) {
            return [
                'id' => $n->id,
                'title' => $n->judul ?? 'Notifikasi Sistem',
                'desc' => $n->pesan,
                'time' => $n->tgl_kirim ? $n->tgl_kirim->diffForHumans() : 'Baru saja',
                'unread' => ! (bool) $n->is_read,
                'type' => $n->tipe ?? 'info',
                'link' => $n->link ?? route('admin.dashboard'),
            ];
        })->values()->all();

        $unreadCount = $list->where('is_read', false)->count();

        return response()->json([
            'notifications' => $data,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Tandai semua notifikasi sistem Admin sebagai dibaca
     */
    public function readAllNotifikasi()
    {
        $adminId = Auth::id();
        $hasUserIdCol = Schema::hasColumn('notifikasis', 'user_id');

        Notifikasi::query()
            ->when($hasUserIdCol, function ($query) use ($adminId) {
                $query->where(function ($q) use ($adminId) {
                    $q->where('user_id', $adminId)
                        ->orWhereNull('user_id');
                });
            })
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Tandai satu notifikasi sistem Admin sebagai dibaca
     */
    public function readNotifikasi(int $id)
    {
        $notif = Notifikasi::findOrFail($id);
        $adminId = Auth::id();
        $hasUserIdCol = Schema::hasColumn('notifikasis', 'user_id');

        if ($hasUserIdCol && $notif->user_id !== null && (int) $notif->user_id !== (int) $adminId) {
            abort(403, 'Anda tidak memiliki akses ke notifikasi pengguna lain.');
        }

        $notif->is_read = true;
        $notif->save();

        return response()->json(['success' => true]);
    }
}
