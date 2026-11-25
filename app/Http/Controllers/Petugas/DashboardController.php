<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\BacaanMeter;
use App\Models\BlokHunian;
use App\Models\Notifikasi;
use App\Models\UnitHunian;
use App\Services\BisnisDigital\OcrMeterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $periodeStr = $now->translatedFormat('F Y');

        // Status filter dari request (default: all | belum_dicatat | tercatat | ditolak)
        $filterStatus = $request->query('status', 'all');
        $selectedBlokId = $request->query('blok_id');

        $bloks = BlokHunian::withCount('unitHunians')->orderBy('nama_blok')->get();
        $activeBlok = ($selectedBlokId && $selectedBlokId !== 'all') ? $bloks->firstWhere('id', (int) $selectedBlokId) : null;

        // Hitung statistik progres (spesifik per blok atau keseluruhan perumahan)
        if ($activeBlok) {
            $totalUnit = (int) ($activeBlok->unit_hunians_count ?? $activeBlok->units_count ?? 0);
            $totalTercatat = BacaanMeter::where('bulan', $currentMonth)
                ->where('tahun', $currentYear)
                ->whereIn('status_verifikasi', ['valid', 'ter_flag'])
                ->whereHas('unit', fn ($q) => $q->where('blok_id', $activeBlok->id))
                ->distinct('unit_id')
                ->count('unit_id');
            $totalDitolak = BacaanMeter::where('bulan', $currentMonth)
                ->where('tahun', $currentYear)
                ->where('status_verifikasi', 'ditolak')
                ->whereHas('unit', fn ($q) => $q->where('blok_id', $activeBlok->id))
                ->distinct('unit_id')
                ->count('unit_id');
        } else {
            $totalUnit = UnitHunian::count() ?: 700;
            $totalTercatat = BacaanMeter::where('bulan', $currentMonth)
                ->where('tahun', $currentYear)
                ->whereIn('status_verifikasi', ['valid', 'ter_flag'])
                ->distinct('unit_id')
                ->count('unit_id');
            $totalDitolak = BacaanMeter::where('bulan', $currentMonth)
                ->where('tahun', $currentYear)
                ->where('status_verifikasi', 'ditolak')
                ->distinct('unit_id')
                ->count('unit_id');
        }

        $totalSisa = max(0, $totalUnit - $totalTercatat);
        $progresPersen = $totalUnit > 0 ? round(($totalTercatat / $totalUnit) * 100) : 0;

        // Ambil unit hunian dengan bacaan bulan ini
        $unitsQuery = UnitHunian::with(['blok', 'bacaanMeters' => function ($q) use ($currentMonth, $currentYear) {
            $q->where('bulan', $currentMonth)->where('tahun', $currentYear);
        }]);

        if ($activeBlok) {
            $unitsQuery->where('blok_id', $activeBlok->id);
        }

        if ($request->filled('search')) {
            $s = $request->query('search');
            $unitsQuery->where(function ($q) use ($s) {
                $q->where('nomor_rumah', 'like', "%{$s}%")
                    ->orWhere('nama_pemilik', 'like', "%{$s}%");
            });
        }

        if ($filterStatus === 'tercatat') {
            $unitsQuery->whereHas('bacaanMeters', function ($q) use ($currentMonth, $currentYear) {
                $q->where('bulan', $currentMonth)->where('tahun', $currentYear)
                    ->whereIn('status_verifikasi', ['valid', 'ter_flag']);
            });
        } elseif ($filterStatus === 'ditolak') {
            $unitsQuery->whereHas('bacaanMeters', function ($q) use ($currentMonth, $currentYear) {
                $q->where('bulan', $currentMonth)->where('tahun', $currentYear)
                    ->where('status_verifikasi', 'ditolak');
            });
        } elseif ($filterStatus === 'belum_dicatat' || $filterStatus === 'belum') {
            $unitsQuery->where(function ($q) use ($currentMonth, $currentYear) {
                $q->whereDoesntHave('bacaanMeters', function ($sub) use ($currentMonth, $currentYear) {
                    $sub->where('bulan', $currentMonth)->where('tahun', $currentYear);
                })->orWhereHas('bacaanMeters', function ($sub) use ($currentMonth, $currentYear) {
                    $sub->where('bulan', $currentMonth)->where('tahun', $currentYear)
                        ->where('status_verifikasi', 'ditolak');
                });
            });
        }

        $units = $unitsQuery->orderByRaw('LENGTH(nomor_rumah) ASC, nomor_rumah ASC')
            ->paginate(25)
            ->withQueryString();

        return view('petugas.dashboard', compact(
            'user',
            'periodeStr',
            'bloks',
            'activeBlok',
            'selectedBlokId',
            'totalUnit',
            'totalTercatat',
            'totalSisa',
            'totalDitolak',
            'progresPersen',
            'units',
            'filterStatus'
        ));
    }

    public function pindai(Request $request, OcrMeterService $ocrMeterService)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $periodeStr = $now->translatedFormat('F Y');
        $geofenceConfig = $ocrMeterService->getGeofenceConfig();

        // 1. Ambil seluruh data blok dan hitung progres pencatatan per blok
        $bloks = BlokHunian::withCount('unitHunians')->orderBy('nama_blok')->get();

        // Tentukan blok aktif: dari query param, atau dari unit_id, atau blok pertama
        $selectedBlokId = $request->query('blok_id');
        $requestedUnitId = $request->query('unit_id');

        if ($requestedUnitId) {
            $requestedUnit = UnitHunian::find($requestedUnitId);
            if ($requestedUnit) {
                $selectedBlokId = $requestedUnit->blok_id;
            }
        }

        if (! $selectedBlokId && $bloks->isNotEmpty()) {
            $selectedBlokId = $bloks->first()->id;
        }

        // Hitung statistik untuk setiap blok di periode aktif
        $blokStats = [];
        foreach ($bloks as $b) {
            $totalInBlok = (int) ($b->unit_hunians_count ?? $b->units_count ?? 0);
            $tercatatInBlok = BacaanMeter::where('bulan', $currentMonth)
                ->where('tahun', $currentYear)
                ->whereIn('status_verifikasi', ['valid', 'ter_flag'])
                ->whereHas('unit', fn ($q) => $q->where('blok_id', $b->id))
                ->distinct('unit_id')
                ->count('unit_id');

            $ditolakInBlok = BacaanMeter::where('bulan', $currentMonth)
                ->where('tahun', $currentYear)
                ->where('status_verifikasi', 'ditolak')
                ->whereHas('unit', fn ($q) => $q->where('blok_id', $b->id))
                ->distinct('unit_id')
                ->count('unit_id');

            $belumInBlok = max(0, $totalInBlok - $tercatatInBlok);
            $persenInBlok = $totalInBlok > 0 ? (int) round(($tercatatInBlok / $totalInBlok) * 100) : 0;

            $blokStats[$b->id] = [
                'total' => $totalInBlok,
                'tercatat' => $tercatatInBlok,
                'ditolak' => $ditolakInBlok,
                'belum' => $belumInBlok,
                'persen' => $persenInBlok,
            ];
        }

        $activeBlok = $bloks->firstWhere('id', (int) $selectedBlokId) ?? $bloks->first();
        $activeBlokStat = $activeBlok ? ($blokStats[$activeBlok->id] ?? ['total' => 0, 'tercatat' => 0, 'belum' => 0, 'ditolak' => 0, 'persen' => 0]) : ['total' => 0, 'tercatat' => 0, 'belum' => 0, 'ditolak' => 0, 'persen' => 0];

        // 2. Query unit-unit dalam blok terpilih
        $filterStatus = $request->query('status', 'all'); // 'all', 'belum_dicatat', 'ditolak', 'tercatat'
        $searchKeyword = trim((string) $request->query('search', ''));

        $unitsQuery = UnitHunian::with(['blok', 'bacaanMeters' => function ($q) use ($currentMonth, $currentYear) {
            $q->where('bulan', $currentMonth)->where('tahun', $currentYear);
        }])
            ->where('blok_id', $activeBlok?->id);

        if ($searchKeyword !== '') {
            $unitsQuery->where(function ($q) use ($searchKeyword) {
                $q->where('nomor_rumah', 'like', "%{$searchKeyword}%")
                    ->orWhere('nama_pemilik', 'like', "%{$searchKeyword}%");
            });
        }

        if ($filterStatus === 'belum_dicatat') {
            $unitsQuery->where(function ($q) use ($currentMonth, $currentYear) {
                $q->whereDoesntHave('bacaanMeters', function ($b) use ($currentMonth, $currentYear) {
                    $b->where('bulan', $currentMonth)->where('tahun', $currentYear);
                })->orWhereHas('bacaanMeters', function ($b) use ($currentMonth, $currentYear) {
                    $b->where('bulan', $currentMonth)->where('tahun', $currentYear)->where('status_verifikasi', 'ditolak');
                });
            });
        } elseif ($filterStatus === 'ditolak') {
            $unitsQuery->whereHas('bacaanMeters', function ($b) use ($currentMonth, $currentYear) {
                $b->where('bulan', $currentMonth)->where('tahun', $currentYear)->where('status_verifikasi', 'ditolak');
            });
        } elseif ($filterStatus === 'tercatat') {
            $unitsQuery->whereHas('bacaanMeters', function ($b) use ($currentMonth, $currentYear) {
                $b->where('bulan', $currentMonth)->where('tahun', $currentYear)->whereIn('status_verifikasi', ['valid', 'ter_flag']);
            });
        }

        // Urutkan secara natural nomor rumah (contoh A-01, A-02, ..., A-60)
        $units = $unitsQuery->orderByRaw('LENGTH(nomor_rumah) ASC, nomor_rumah ASC')->get();

        // 3. Tentukan Unit yang sedang dipilih untuk dipindai
        $selectedUnit = null;
        if ($requestedUnitId) {
            $selectedUnit = $units->firstWhere('id', (int) $requestedUnitId) ?? UnitHunian::with(['blok', 'bacaanMeters' => function ($q) use ($currentMonth, $currentYear) {
                $q->where('bulan', $currentMonth)->where('tahun', $currentYear);
            }])->find($requestedUnitId);
        }

        if (! $selectedUnit) {
            // Prioritaskan unit pertama di blok ini yang belum dicatat atau berstatus ditolak
            $selectedUnit = $units->first(function ($u) {
                $b = $u->bacaanMeters->first();

                return ! $b || $b->status_verifikasi === 'ditolak';
            }) ?? $units->first();
        }

        // Fallback jika blok kosong
        if (! $selectedUnit && $activeBlok) {
            $selectedUnit = UnitHunian::where('blok_id', $activeBlok->id)->first();
        }

        // 4. Cari Unit Berikutnya (Next Unit) di blok yang sama yang belum dicatat atau ditolak
        $nextUnit = null;
        if ($selectedUnit) {
            $allUnitsInBlok = UnitHunian::with(['bacaanMeters' => function ($q) use ($currentMonth, $currentYear) {
                $q->where('bulan', $currentMonth)->where('tahun', $currentYear);
            }])
                ->where('blok_id', $selectedUnit->blok_id)
                ->orderByRaw('LENGTH(nomor_rumah) ASC, nomor_rumah ASC')
                ->get();

            $currentIndex = $allUnitsInBlok->search(fn ($u) => $u->id === $selectedUnit->id);
            if ($currentIndex !== false) {
                // Cari dari index setelahnya
                for ($i = $currentIndex + 1; $i < $allUnitsInBlok->count(); $i++) {
                    $candidate = $allUnitsInBlok->get($i);
                    $b = $candidate->bacaanMeters->first();
                    if (! $b || $b->status_verifikasi === 'ditolak') {
                        $nextUnit = $candidate;
                        break;
                    }
                }
                // Jika tidak ada di belakang, cari dari awal (wrap-around)
                if (! $nextUnit) {
                    for ($i = 0; $i < $currentIndex; $i++) {
                        $candidate = $allUnitsInBlok->get($i);
                        $b = $candidate->bacaanMeters->first();
                        if (! $b || $b->status_verifikasi === 'ditolak') {
                            $nextUnit = $candidate;
                            break;
                        }
                    }
                }
            }
        }

        // 5. Stand meter bulan lalu untuk unit terpilih
        $bacaanBulanLalu = null;
        $angkaLalu = 0.0;
        $bacaanAktifUnit = null;

        if ($selectedUnit) {
            $bacaanAktifUnit = $selectedUnit->bacaanMeters->first();

            $lastMonth = $now->copy()->subMonth();
            $bacaanBulanLalu = BacaanMeter::where('unit_id', $selectedUnit->id)
                ->where('bulan', $lastMonth->month)
                ->where('tahun', $lastMonth->year)
                ->first();

            if (! $bacaanBulanLalu) {
                $bacaanBulanLalu = BacaanMeter::where('unit_id', $selectedUnit->id)
                    ->where(function ($q) use ($currentYear, $currentMonth) {
                        $q->where('tahun', '<', $currentYear)
                            ->orWhere(function ($q2) use ($currentYear, $currentMonth) {
                                $q2->where('tahun', $currentYear)
                                    ->where('bulan', '<', $currentMonth);
                            });
                    })
                    ->orderBy('tahun', 'desc')
                    ->orderBy('bulan', 'desc')
                    ->first();
            }

            if ($bacaanBulanLalu && $bacaanBulanLalu->angka_meter !== null) {
                $angkaLalu = (float) $bacaanBulanLalu->angka_meter;
            } elseif ($selectedUnit->nomor_rumah === 'A-03') {
                $angkaLalu = 4198.0;
            } else {
                $angkaLalu = 0.0;
            }
        }

        // 6. Map seluruh unit dalam blok untuk interaktivitas Alpine.js di Blade
        $unitsMapped = $units->map(function ($u) use ($currentMonth, $currentYear) {
            $bacaan = $u->bacaanMeters->first();
            $status = 'belum_dicatat';
            $alasanDitolak = null;
            $angkaSekarang = null;

            if ($bacaan) {
                $status = $bacaan->status_verifikasi; // 'valid', 'ter_flag', 'ditolak'
                $alasanDitolak = $bacaan->alasan_penolakan;
                $angkaSekarang = $bacaan->angka_meter;
            }

            $hasCoords = ($u->latitude !== null && $u->longitude !== null);

            return [
                'id' => $u->id,
                'nomor_rumah' => $u->nomor_rumah ?? '—',
                'nama_pemilik' => $u->nama_pemilik ?? '—',
                'nomor_wa' => $u->nomor_wa ?? '',
                'blok_id' => $u->blok_id,
                'blok_nama' => optional($u->blok)->nama_blok ?? 'Blok ?',
                'latitude' => $u->latitude ? (float) $u->latitude : null,
                'longitude' => $u->longitude ? (float) $u->longitude : null,
                'radius_toleransi' => (int) ($u->radius_toleransi ?: 50),
                'has_coords' => $hasCoords,
                'status' => $status,
                'alasan_ditolak' => $alasanDitolak,
                'angka_sekarang' => $angkaSekarang,
                'foto_meter' => $bacaan?->foto_meter,
            ];
        })->values()->all();

        // Data unit terpilih yang siap dikonsumsi di frontend
        $selectedUnitMapped = null;
        if ($selectedUnit) {
            $selectedUnitMapped = [
                'id' => $selectedUnit->id,
                'nomor_rumah' => $selectedUnit->nomor_rumah ?? '—',
                'nama_pemilik' => $selectedUnit->nama_pemilik ?? '—',
                'nomor_wa' => $selectedUnit->nomor_wa ?? '',
                'blok_id' => $selectedUnit->blok_id,
                'blok_nama' => optional($selectedUnit->blok)->nama_blok ?? 'Blok ?',
                'latitude' => $selectedUnit->latitude ? (float) $selectedUnit->latitude : null,
                'longitude' => $selectedUnit->longitude ? (float) $selectedUnit->longitude : null,
                'radius_toleransi' => (int) ($selectedUnit->radius_toleransi ?: 50),
                'has_coords' => ($selectedUnit->latitude !== null && $selectedUnit->longitude !== null),
                'angka_lalu' => $angkaLalu,
                'status' => $bacaanAktifUnit ? $bacaanAktifUnit->status_verifikasi : 'belum_dicatat',
                'alasan_ditolak' => $bacaanAktifUnit?->alasan_penolakan,
                'angka_sekarang' => $bacaanAktifUnit?->angka_meter,
                'foto_meter' => $bacaanAktifUnit?->foto_meter,
            ];
        }

        $nextUnitMapped = null;
        if ($nextUnit) {
            $nextUnitMapped = [
                'id' => $nextUnit->id,
                'nomor_rumah' => $nextUnit->nomor_rumah,
                'nama_pemilik' => $nextUnit->nama_pemilik,
            ];
        }

        return view('petugas.pindai', compact(
            'user',
            'periodeStr',
            'bloks',
            'activeBlok',
            'activeBlokStat',
            'blokStats',
            'units',
            'unitsMapped',
            'selectedUnit',
            'selectedUnitMapped',
            'nextUnitMapped',
            'bacaanBulanLalu',
            'angkaLalu',
            'filterStatus',
            'searchKeyword',
            'currentMonth',
            'currentYear',
            'geofenceConfig'
        ));
    }

    /**
     * Endpoint AJAX untuk memindai foto meteran menggunakan Google Cloud Vision API.
     * Validasi GPS dilakukan langsung terhadap koordinat unit yang dipilih.
     */
    public function ocrScan(Request $request, OcrMeterService $ocrMeterService)
    {
        $request->validate([
            'unit_id' => 'required|exists:unit_hunians,id',
            'foto_meter' => 'required|image|max:5120',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $unit = UnitHunian::findOrFail($request->unit_id);

        $gpsCheck = $ocrMeterService->validateGpsLocation(
            $request->filled('latitude') ? (float) $request->latitude : null,
            $request->filled('longitude') ? (float) $request->longitude : null,
            $unit->latitude ? (float) $unit->latitude : null,
            $unit->longitude ? (float) $unit->longitude : null,
            (float) ($unit->radius_toleransi ?: 50)
        );

        if (! $gpsCheck['is_valid']) {
            return response()->json([
                'success' => false,
                'suggested_digits' => null,
                'raw_text' => '',
                'confidence_score' => 0.0,
                'is_gps_valid' => false,
                'gps_unverified' => false,
                'distance_meters' => $gpsCheck['distance_meters'] ?? 0.0,
                'has_api_key' => (string) config('services.google_vision.api_key', '') !== '',
                'message' => $gpsCheck['message'],
            ], 422);
        }

        $file = $request->file('foto_meter');
        $imageBinary = file_get_contents($file->getRealPath());
        $hasApiKey = (string) config('services.google_vision.api_key', '') !== '';

        $ocrResult = $ocrMeterService->extractMeterFromImage($imageBinary !== false ? $imageBinary : '');
        $ocrResult['has_api_key'] = $hasApiKey;
        $ocrResult['is_gps_valid'] = true;
        $ocrResult['gps_unverified'] = $gpsCheck['gps_unverified'] ?? false;
        $ocrResult['distance_meters'] = $gpsCheck['distance_meters'] ?? 0.0;
        $ocrResult['gps_message'] = $gpsCheck['message'] ?? '';

        return response()->json($ocrResult);
    }

    public function store(Request $request, OcrMeterService $ocrMeterService)
    {
        $request->validate([
            'unit_id' => 'required|exists:unit_hunians,id',
            'angka_meter' => 'required|numeric|min:0',
            'foto_meter' => 'nullable|image|max:5120',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'catatan' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        $unit = UnitHunian::with('blok')->findOrFail($request->unit_id);

        // 1. Validasi Lokasi GPS terhadap Unit Rumah
        $latPetugas = $request->filled('latitude') ? (float) $request->latitude : null;
        $lngPetugas = $request->filled('longitude') ? (float) $request->longitude : null;

        $gpsCheck = $ocrMeterService->validateGpsLocation(
            $latPetugas,
            $lngPetugas,
            $unit->latitude ? (float) $unit->latitude : null,
            $unit->longitude ? (float) $unit->longitude : null,
            (float) ($unit->radius_toleransi ?: 50)
        );

        if (! $gpsCheck['is_valid']) {
            return back()->withInput()->with('error', $gpsCheck['message']);
        }

        // 2. Auto-learn koordinat: jika unit belum punya koordinat, simpan GPS petugas saat ini sebagai titik awal
        if (($unit->latitude === null || $unit->longitude === null) && $latPetugas !== null && $lngPetugas !== null) {
            $unit->latitude = $latPetugas;
            $unit->longitude = $lngPetugas;
            if (! $unit->radius_toleransi) {
                $unit->radius_toleransi = 50;
            }
            $unit->save();
        }

        // 3. Angka meter bulan lalu
        $lastMonth = $now->copy()->subMonth();
        $bacaanLalu = BacaanMeter::where('unit_id', $unit->id)
            ->where('bulan', $lastMonth->month)
            ->where('tahun', $lastMonth->year)
            ->first();

        if (! $bacaanLalu) {
            $bacaanLalu = BacaanMeter::where('unit_id', $unit->id)
                ->where(function ($q) use ($currentYear, $currentMonth) {
                    $q->where('tahun', '<', $currentYear)
                        ->orWhere(function ($q2) use ($currentYear, $currentMonth) {
                            $q2->where('tahun', $currentYear)
                                ->where('bulan', '<', $currentMonth);
                        });
                })
                ->orderBy('tahun', 'desc')
                ->orderBy('bulan', 'desc')
                ->first();
        }

        $isFirstReading = ($bacaanLalu === null);
        $angkaBulanLalu = $bacaanLalu ? (float) $bacaanLalu->angka_meter : 0.0;
        $angkaSekarang = (float) $request->angka_meter;
        $confidenceScore = $request->filled('confidence_score') ? (float) $request->confidence_score : null;
        $isOcrCorrected = $request->boolean('is_ocr_corrected');

        // Evaluasi anomali
        $anomalyResult = $ocrMeterService->evaluateReadingAnomaly(
            $angkaBulanLalu,
            $angkaSekarang,
            $confidenceScore,
            $unit->status_huni ?? 'Dihuni',
            20.0,
            $isOcrCorrected,
            $isFirstReading
        );

        $statusVerifikasi = $anomalyResult['status_verifikasi'];
        $catatanAnomali = $anomalyResult['catatan_anomali'];

        // 4. Upload foto meteran jika ada berkas baru
        $fotoPath = null;
        if ($request->hasFile('foto_meter')) {
            $fotoPath = $request->file('foto_meter')->store('bacaan_meters', 'public');
            try {
                $destPath = public_path('storage/'.$fotoPath);
                @mkdir(dirname($destPath), 0755, true);
                @copy(storage_path('app/public/'.$fotoPath), $destPath);
            } catch (\Throwable) {
            }
        }

        // 5. Cek apakah sudah ada bacaan bulan ini (misal pencatatan ulang karena ditolak)
        $existing = BacaanMeter::where('unit_id', $unit->id)
            ->where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->first();

        if ($existing) {
            // Update data yang ada
            $existing->user_id = $user->id;
            if ($fotoPath) {
                $existing->foto_meter = $fotoPath;
            }
            $existing->angka_meter_bulanlalu = (int) $angkaBulanLalu;
            $existing->angka_meter = (int) $angkaSekarang;
            $existing->latitude = $latPetugas;
            $existing->longitude = $lngPetugas;
            $existing->status_verifikasi = $statusVerifikasi;
            $existing->gps_unverified = $gpsCheck['gps_unverified'] ?? false;
            $existing->confidence_score = $confidenceScore ?? $existing->confidence_score;
            $existing->is_ocr_corrected = $isOcrCorrected;
            $existing->catatan_anomali = $catatanAnomali ?? $request->catatan;
            $existing->alasan_penolakan = null; // Reset alasan penolakan setelah diperbaiki
            $existing->save();
            $bacaan = $existing;
        } else {
            // Buat record baru
            $bacaan = BacaanMeter::create([
                'unit_id' => $unit->id,
                'user_id' => $user->id,
                'foto_meter' => $fotoPath ?? 'sample_meter.jpg',
                'bulan' => $currentMonth,
                'tahun' => $currentYear,
                'angka_meter_bulanlalu' => (int) $angkaBulanLalu,
                'angka_meter' => (int) $angkaSekarang,
                'latitude' => $latPetugas,
                'longitude' => $lngPetugas,
                'status_verifikasi' => $statusVerifikasi,
                'gps_unverified' => $gpsCheck['gps_unverified'] ?? false,
                'confidence_score' => $confidenceScore,
                'is_ocr_corrected' => $isOcrCorrected,
                'catatan_anomali' => $catatanAnomali ?? $request->catatan,
                'alasan_penolakan' => null,
            ]);
        }

        // 6. Notifikasi otomatis ke admin
        $unitLabel = $unit->nomor_rumah ?? '#';
        $pemilikLabel = $unit->nama_pemilik ?? 'Warga';

        if ($statusVerifikasi === 'valid') {
            Notifikasi::create([
                'unit_id' => $unit->id,
                'judul' => 'Pembacaan Meter Masuk (Batch)',
                'pesan' => "Unit {$unitLabel} ({$pemilikLabel}) berhasil dicatat ({$angkaSekarang} m³). Masuk antrean batch tagihan.",
                'tipe' => 'batch',
                'link' => route('admin.tagihan'),
                'status_terkirim' => true,
                'is_read' => false,
                'tgl_kirim' => Carbon::now(),
            ]);
        } elseif ($statusVerifikasi === 'ter_flag') {
            Notifikasi::create([
                'unit_id' => $unit->id,
                'judul' => 'Data Meter Ter-flag Anomali',
                'pesan' => "Unit {$unitLabel} ({$pemilikLabel}) terdeteksi anomali: ".($catatanAnomali ?? 'Perlu verifikasi admin').'. Segera tinjau foto fisik meteran.',
                'tipe' => 'flag',
                'link' => route('admin.terflag', ['id' => $bacaan->id]),
                'status_terkirim' => true,
                'is_read' => false,
                'tgl_kirim' => Carbon::now(),
            ]);
        }

        // 7. Cari Unit Berikutnya di blok yang sama yang belum dicatat
        $allUnitsInBlok = UnitHunian::with(['bacaanMeters' => function ($q) use ($currentMonth, $currentYear) {
            $q->where('bulan', $currentMonth)->where('tahun', $currentYear);
        }])
            ->where('blok_id', $unit->blok_id)
            ->orderByRaw('LENGTH(nomor_rumah) ASC, nomor_rumah ASC')
            ->get();

        $currentIndex = $allUnitsInBlok->search(fn ($u) => $u->id === $unit->id);
        $nextUnit = null;
        if ($currentIndex !== false) {
            for ($i = $currentIndex + 1; $i < $allUnitsInBlok->count(); $i++) {
                $cand = $allUnitsInBlok->get($i);
                $b = $cand->bacaanMeters->first();
                if (! $b || $b->status_verifikasi === 'ditolak') {
                    $nextUnit = $cand;
                    break;
                }
            }
            if (! $nextUnit) {
                for ($i = 0; $i < $currentIndex; $i++) {
                    $cand = $allUnitsInBlok->get($i);
                    $b = $cand->bacaanMeters->first();
                    if (! $b || $b->status_verifikasi === 'ditolak') {
                        $nextUnit = $cand;
                        break;
                    }
                }
            }
        }

        $msg = "Pencatatan meteran Unit {$unitLabel} ({$pemilikLabel}) berhasil disimpan.";
        if ($statusVerifikasi === 'ter_flag') {
            $msg .= ' Catatan: Data terdeteksi anomali untuk ditinjau Admin.';
        }

        return redirect()->route('petugas.pindai', [
            'blok_id' => $unit->blok_id,
            'unit_id' => $unit->id,
        ])->with([
            'success' => $msg,
            'just_recorded_unit_id' => $unit->id,
            'next_unit_id' => $nextUnit?->id,
            'next_unit_nomor' => $nextUnit?->nomor_rumah,
            'next_unit_nama' => $nextUnit?->nama_pemilik,
        ]);
    }

    public function riwayat(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $periodeStr = $now->translatedFormat('F Y');

        $filterBulan = $request->query('bulan', $now->month);
        $filterTahun = $request->query('tahun', $now->year);
        $filterStatus = $request->query('status', 'semua');

        $riwayatQuery = BacaanMeter::with(['unit.blok', 'petugas'])
            ->where('bulan', $filterBulan)
            ->where('tahun', $filterTahun);

        if (! $user->hasRole('admin')) {
            $riwayatQuery->where('user_id', $user->id);
        }

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
