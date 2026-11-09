<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Models\BacaanMeter;
use App\Models\UnitHunian;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $petugasId = $user->id;
        $bulanIni = (int) date('m');
        $tahunIni = (int) date('Y');

        $totalUnitGlobal = UnitHunian::count();

        // UBAH: id_petugas -> user_id
        $totalDicatat = BacaanMeter::where('user_id', $petugasId)
            ->where('bulan', $bulanIni)
            ->where('tahun', $tahunIni)
            ->count();

        $sisaTarget = max(0, $totalUnitGlobal - $totalDicatat);

        // UBAH: id_petugas -> user_id DAN with('unitHunian') -> with('unit')
        $riwayatPencatatan = BacaanMeter::with('unit')
            ->where('user_id', $petugasId)
            ->latest()
            ->take(5)
            ->get();

        $unreadNotifications = Notifikasi::where('status_terkirim', 'pending')
            ->count();

        return view('petugas.dashboard', compact(
            'totalDicatat',
            'sisaTarget',
            'riwayatPencatatan',
            'unreadNotifications'
        ));
    }
}
=======
use App\Models\UnitHunian;
use App\Models\BacaanMeter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $periodeStr = $now->translatedFormat('F Y');

        // Status filter dari request (default: all atau belum_dicatat)
        $filterStatus = $request->query('status', 'belum_dicatat');

        // Total unit seluruhnya
        $totalUnit = 140; // Default sektor 04 / Blok A
        $totalTercatat = 128;
        $totalSisa = 12;
        $progresPersen = 91;

        // Ambil unit hunian dengan bacaan bulan ini jika ada
        $unitsQuery = UnitHunian::with(['blok', 'bacaanMeters' => function ($q) use ($currentMonth, $currentYear) {
            $q->where('bulan', $currentMonth)->where('tahun', $currentYear);
        }]);

        if ($request->filled('search')) {
            $s = $request->query('search');
            $unitsQuery->where(function ($q) use ($s) {
                $q->where('nomor_rumah', 'like', "%{$s}%")
                  ->orWhere('nama_pemilik', 'like', "%{$s}%");
            });
        }

        $units = $unitsQuery->take(20)->get();

        return view('petugas.dashboard', compact(
            'user',
            'periodeStr',
            'totalUnit',
            'totalTercatat',
            'totalSisa',
            'progresPersen',
            'units',
            'filterStatus'
        ));
    }

    public function pindai(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $periodeStr = $now->translatedFormat('F Y');

        // Ambil unit yang dipilih (dari query string ?unit_id=xxx) atau default ke unit pertama yang belum dicatat
        $selectedUnit = null;
        if ($request->filled('unit_id')) {
            $selectedUnit = UnitHunian::with(['blok', 'bacaanMeters' => function ($q) use ($currentMonth, $currentYear) {
                $q->where('bulan', $currentMonth)->where('tahun', $currentYear);
            }])->find($request->query('unit_id'));
        }

        if (!$selectedUnit) {
            // Default pilih unit pertama yang belum dicatat bulan ini (prioritas unit A-03 jika ada)
            $selectedUnit = UnitHunian::with(['blok', 'bacaanMeters' => function ($q) use ($currentMonth, $currentYear) {
                $q->where('bulan', $currentMonth)->where('tahun', $currentYear);
            }])->whereDoesntHave('bacaanMeters', function ($q) use ($currentMonth, $currentYear) {
                $q->where('bulan', $currentMonth)->where('tahun', $currentYear);
            })->orderBy('nomor_rumah')->first() ?? UnitHunian::with('blok')->first();
        }

        $bacaanBulanLalu = null;
        $angkaLalu = 4198; // Default stand meter historis
        $estimasiMin = 20;
        $estimasiMax = 32;

        if ($selectedUnit) {
            $lastMonth = $now->copy()->subMonth();
            $bacaanBulanLalu = BacaanMeter::where('unit_id', $selectedUnit->id)
                ->where('bulan', $lastMonth->month)
                ->where('tahun', $lastMonth->year)
                ->first();

            if (!$bacaanBulanLalu) {
                $bacaanBulanLalu = BacaanMeter::where('unit_id', $selectedUnit->id)
                    ->orderBy('tahun', 'desc')
                    ->orderBy('bulan', 'desc')
                    ->first();
            }

            if ($bacaanBulanLalu && $bacaanBulanLalu->angka_meter !== null) {
                $angkaLalu = (float) $bacaanBulanLalu->angka_meter;
            } elseif ($selectedUnit->nomor_rumah === 'A-03') {
                $angkaLalu = 4198;
            }
        }

        // List unit untuk dropdown pencarian (dengan status bulan ini)
        $units = UnitHunian::with(['blok', 'bacaanMeters' => function ($q) use ($currentMonth, $currentYear) {
                $q->where('bulan', $currentMonth)->where('tahun', $currentYear);
            }])
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->query('search');
                $q->where('nomor_rumah', 'like', "%{$s}%")
                  ->orWhere('nama_pemilik', 'like', "%{$s}%");
            })
            ->orderBy('nomor_rumah')
            ->take(60)
            ->get();

        // Map unit ke array sederhana yang aman untuk @json di Blade
        $unitsMapped = $units->map(function ($u) use ($selectedUnit, $angkaLalu, $estimasiMin, $estimasiMax) {
            $sudahDicatat = $u->bacaanMeters->isNotEmpty();
            $isThisSelected = ($selectedUnit && $u->id === $selectedUnit->id);

            return [
                'id'            => $u->id,
                'nomor_rumah'   => $u->nomor_rumah   ?? '—',
                'nama_pemilik'  => $u->nama_pemilik  ?? '—',
                'blok_nama'     => optional($u->blok)->nama_blok ?? 'Blok ?',
                'angka_lalu'    => $isThisSelected ? $angkaLalu : 4198,
                'sudah_dicatat' => $sudahDicatat,
                'estimasi_range'=> ($estimasiMin && $estimasiMax) ? ($estimasiMin . '–' . $estimasiMax . ' m³') : '20–32 m³',
            ];
        })->values()->all();

        // Flash message dari session
        $successMessage = session('success');
        $errorMessage   = session('error');

        return view('petugas.pindai', compact(
            'user',
            'periodeStr',
            'units',
            'unitsMapped',
            'selectedUnit',
            'bacaanBulanLalu',
            'angkaLalu',
            'estimasiMin',
            'estimasiMax',
            'currentMonth',
            'currentYear',
            'successMessage',
            'errorMessage'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_id'      => 'required|exists:unit_hunians,id',
            'angka_meter'  => 'required|numeric|min:0',
            'foto_meter'   => 'nullable|image|max:5120',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
            'catatan'      => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear  = $now->year;

        // Cek apakah sudah ada bacaan bulan ini
        $existing = BacaanMeter::where('unit_id', $request->unit_id)
            ->where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->first();

        if ($existing) {
            return back()->withInput()
                ->with('error', 'Bacaan meter untuk unit ini di bulan ' . $now->translatedFormat('F Y') . ' sudah ada. Hubungi Admin untuk koreksi.');
        }

        // Angka meter bulan lalu
        $lastMonth = $now->copy()->subMonth();
        $bacaanLalu = BacaanMeter::where('unit_id', $request->unit_id)
            ->where('bulan', $lastMonth->month)
            ->where('tahun', $lastMonth->year)
            ->first();

        $angkaBulanLalu = $bacaanLalu ? $bacaanLalu->angka_meter : 0;
        $angkaSekarang  = (float) $request->angka_meter;
        $pemakaian      = $angkaSekarang - $angkaBulanLalu;

        // Deteksi anomali
        $statusVerifikasi = 'valid';
        $catatanAnomali   = null;

        if ($pemakaian < 0) {
            $statusVerifikasi = 'ter_flag';
            $catatanAnomali   = 'Angka meter lebih kecil dari bulan lalu (kemungkinan kesalahan baca atau penggantian meter).';
        } elseif ($angkaBulanLalu > 0 && $pemakaian > ($angkaBulanLalu * 2)) {
            $statusVerifikasi = 'ter_flag';
            $catatanAnomali   = 'Pemakaian melebihi 2x rata-rata historis — perlu verifikasi admin.';
        }

        // Upload foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto_meter')) {
            $fotoPath = $request->file('foto_meter')->store('bacaan_meters', 'public');
        }

        // Simpan bacaan
        BacaanMeter::create([
            'unit_id'               => $request->unit_id,
            'user_id'               => $user->id,
            'foto_meter'            => $fotoPath,
            'bulan'                 => $currentMonth,
            'tahun'                 => $currentYear,
            'angka_meter_bulanlalu' => $angkaBulanLalu,
            'angka_meter'           => $angkaSekarang,
            'latitude'              => $request->latitude,
            'longitude'             => $request->longitude,
            'status_verifikasi'     => $statusVerifikasi,
            'confidence_score'      => $request->confidence_score ?? null,
            'is_ocr_corrected'      => $request->boolean('is_ocr_corrected'),
            'catatan_anomali'       => $catatanAnomali ?? $request->catatan,
        ]);

        $unitLabel = optional(UnitHunian::find($request->unit_id))->nomor_rumah ?? '#';
        $msg = "Bacaan meter Unit {$unitLabel} berhasil disimpan.";
        if ($statusVerifikasi === 'ter_flag') {
            $msg .= ' Terdeteksi anomali — butuh persetujuan Admin.';
        }

        return redirect()->route('petugas.pindai')->with('success', $msg);
    }

    public function riwayat(Request $request)
    {
        $user = Auth::user();
        $now  = Carbon::now();
        $periodeStr = $now->translatedFormat('F Y');

        $filterBulan = $request->query('bulan', $now->month);
        $filterTahun = $request->query('tahun', $now->year);
        $filterStatus = $request->query('status', 'semua');

        $riwayatQuery = BacaanMeter::with(['unit.blok'])
            ->where('user_id', $user->id)
            ->where('bulan', $filterBulan)
            ->where('tahun', $filterTahun);

        if ($filterStatus !== 'semua') {
            $riwayatQuery->where('status_verifikasi', $filterStatus);
        }

        $riwayat = $riwayatQuery->orderBy('created_at', 'desc')->paginate(20);

        return view('petugas.riwayat', compact(
            'user',
            'periodeStr',
            'riwayat',
            'filterBulan',
            'filterTahun',
            'filterStatus'
        ));
    }
}
>>>>>>> dc048bd ( fitur lengkap)
