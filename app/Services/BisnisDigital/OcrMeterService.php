<?php

namespace App\Services\BisnisDigital;

use Illuminate\Support\Facades\Http;

/**
 * Konsentrasi Bisnis Digital (Asdianto)
 * Fokus: Akuisisi Data Lapangan via OCR Google Cloud Vision API, Validasi GPS,
 * dan Evaluasi Anomali (Exception-Based Review).
 */
class OcrMeterService
{
    /**
     * Koordinat default pusat Perumahan Grand Sulawesi (-4.007468, 119.661131).
     */
    public const DEFAULT_LATITUDE = -4.007468;

    public const DEFAULT_LONGITUDE = 119.661131;

    /**
     * Radius toleransi maksimum lokasi petugas dari titik pusat kawasan (meter).
     */
    public const MAX_GPS_RADIUS_METERS = 700.0;

    /**
     * Ambang batas minimum confidence score OCR (%) agar tidak di-flag.
     */
    private const MIN_OCR_CONFIDENCE = 70.0;

    /**
     * Mengambil konfigurasi domain lokasi (Geofence) area Perumahan Grand Sulawesi.
     *
     * @return array{center_lat: float, center_lng: float, radius_meters: float}
     */
    public function getGeofenceConfig(): array
    {
        return [
            'center_lat' => (float) config('services.geofence.center_lat', self::DEFAULT_LATITUDE),
            'center_lng' => (float) config('services.geofence.center_lng', self::DEFAULT_LONGITUDE),
            'radius_meters' => (float) config('services.geofence.radius_meters', self::MAX_GPS_RADIUS_METERS),
        ];
    }

    /**
     * Mengekstrak angka meteran dan confidence score dari berkas gambar
     * menggunakan Google Cloud Vision API (Text Detection) dengan fallback aman.
     *
     * @return array{success: bool, suggested_digits: ?int, raw_text: string, confidence_score: float, message: string}
     */
    public function extractMeterFromImage(string $imageBinaryOrBase64, bool $isBase64 = false): array
    {
        $apiKey = (string) config('services.google_vision.api_key', '');
        $endpoint = (string) config('services.google_vision.endpoint', 'https://vision.googleapis.com/v1/images:annotate');

        $base64Image = $isBase64 ? $imageBinaryOrBase64 : base64_encode($imageBinaryOrBase64);

        if ($apiKey === '') {
            return [
                'success' => false,
                'suggested_digits' => null,
                'raw_text' => '',
                'confidence_score' => 0.0,
                'message' => 'OCR gagal atau kuota/API Key Google Cloud Vision belum dikonfigurasi. Silakan input manual.',
            ];
        }

        try {
            $response = Http::connectTimeout(3)
                ->timeout(10)
                ->post($endpoint.'?key='.$apiKey, [
                    'requests' => [
                        [
                            'image' => ['content' => $base64Image],
                            'features' => [
                                ['type' => 'TEXT_DETECTION', 'maxResults' => 5],
                            ],
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'suggested_digits' => null,
                    'raw_text' => '',
                    'confidence_score' => 0.0,
                    'message' => 'Layanan Google Cloud Vision tidak dapat dijangkau. Silakan input manual.',
                ];
            }

            $payload = $response->json();
            $rawText = (string) data_get($payload, 'responses.0.fullTextAnnotation.text', data_get($payload, 'responses.0.textAnnotations.0.description', ''));
            $confidence = (float) data_get($payload, 'responses.0.fullTextAnnotation.pages.0.confidence', 0.95) * 100;

            $digits = $this->filterMeterDigits($rawText);

            if ($digits === null) {
                return [
                    'success' => false,
                    'suggested_digits' => null,
                    'raw_text' => $rawText,
                    'confidence_score' => round($confidence, 2),
                    'message' => 'OCR gagal membaca deretan angka pada foto meteran. Silakan ketik manual.',
                ];
            }

            return [
                'success' => true,
                'suggested_digits' => $digits,
                'raw_text' => trim($rawText),
                'confidence_score' => round($confidence, 2),
                'message' => 'Angka meteran berhasil diekstraksi oleh OCR.',
            ];
        } catch (\Throwable) {
            return [
                'success' => false,
                'suggested_digits' => null,
                'raw_text' => '',
                'confidence_score' => 0.0,
                'message' => 'Terjadi kendala jaringan saat menghubungi Google Cloud Vision. Silakan input manual.',
            ];
        }
    }

    /**
     * Menyaring teks mentah hasil OCR menggunakan regex untuk mengambil angka stand meteran.
     */
    public function filterMeterDigits(string $rawText): ?int
    {
        if (preg_match('/\b(\d{3,7})\b/', $rawText, $matches) === 1) {
            return (int) $matches[1];
        }

        $digitsOnly = preg_replace('/\D+/', '', $rawText);
        if ($digitsOnly !== null && $digitsOnly !== '') {
            return (int) $digitsOnly;
        }

        return null;
    }

    /**
     * Menghitung jarak koordinat GPS petugas terhadap titik unit hunian (atau pusat kawasan) menggunakan rumus Haversine.
     *
     * @return array{is_valid: bool, is_unit_locked: bool, gps_unverified: bool, distance_meters: float, message: string}
     */
    public function validateGpsLocation(
        ?float $latPetugas,
        ?float $lngPetugas,
        ?float $latUnit = null,
        ?float $lngUnit = null,
        ?float $maxRadiusMeters = null
    ): array {
        if ($latPetugas === null || $lngPetugas === null) {
            return [
                'is_valid' => false,
                'is_unit_locked' => false,
                'gps_unverified' => false,
                'distance_meters' => 0.0,
                'message' => 'Peringatan Lokasi: GPS perangkat wajib diaktifkan untuk dapat memotret meteran.',
            ];
        }

        $earthRadius = 6371000.0;
        $hasUnitCoord = ($latUnit !== null && $lngUnit !== null);

        if ($hasUnitCoord) {
            // Validasi presisi unit hunian (default 50 meter)
            $targetLat = (float) $latUnit;
            $targetLng = (float) $lngUnit;
            $maxRadius = $maxRadiusMeters !== null && $maxRadiusMeters > 0 ? (float) $maxRadiusMeters : 50.0;

            $dLat = deg2rad($latPetugas - $targetLat);
            $dLng = deg2rad($lngPetugas - $targetLng);

            $a = sin($dLat / 2) * sin($dLat / 2)
                + cos(deg2rad($targetLat)) * cos(deg2rad($latPetugas))
                * sin($dLng / 2) * sin($dLng / 2);

            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = round($earthRadius * $c, 2);

            if ($distance > $maxRadius) {
                return [
                    'is_valid' => false,
                    'is_unit_locked' => true,
                    'gps_unverified' => false,
                    'distance_meters' => $distance,
                    'message' => "Lokasi tidak sesuai: Anda berjarak {$distance} m dari unit rumah ini (radius toleransi maksimal {$maxRadius} m). Kamera & OCR dinonaktifkan.",
                ];
            }

            return [
                'is_valid' => true,
                'is_unit_locked' => true,
                'gps_unverified' => false,
                'distance_meters' => $distance,
                'message' => "Lokasi GPS sesuai ({$distance} m dari unit, batas toleransi {$maxRadius} m).",
            ];
        }

        // Jika unit belum memiliki koordinat tersimpan: tetap izinkan dengan penanda gps_unverified
        $geofence = $this->getGeofenceConfig();
        $targetLat = $geofence['center_lat'];
        $targetLng = $geofence['center_lng'];

        $dLat = deg2rad($latPetugas - $targetLat);
        $dLng = deg2rad($lngPetugas - $targetLng);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($targetLat)) * cos(deg2rad($latPetugas))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = round($earthRadius * $c, 2);

        return [
            'is_valid' => true,
            'is_unit_locked' => false,
            'gps_unverified' => true,
            'distance_meters' => $distance,
            'message' => 'Unit belum memiliki koordinat tersimpan. Pencatatan diizinkan dengan status GPS Unverified.',
        ];
    }

    /**
     * Mengevaluasi aturan deteksi anomali otomatis (Exception-Based Review) sesuai SRS FR-004 & Lampiran C:
     * 1. Angka meter baru lebih kecil dari bulan lalu (meter mundur).
     * 2. Confidence score OCR < 70% (dilewati jika petugas sudah memverifikasi/mengoreksi angka fisik).
     * 3. Pemakaian bernilai 0 m³ pada unit hunian aktif (Dihuni) (dilewati jika pencatatan perdana).
     * 4. Lonjakan pemakaian drastis (> 200% rata-rata normal atau > 50 m³).
     *
     * @return array{status_verifikasi: string, catatan_anomali: ?string, pemakaian: float}
     */
    public function evaluateReadingAnomaly(
        float $angkaBulanLalu,
        float $angkaSekarang,
        ?float $confidenceScore = null,
        string $statusHuni = 'Dihuni',
        float $rataRataNormal = 20.0,
        bool $isOcrCorrected = false,
        bool $isFirstReading = false
    ): array {
        // Jika pencatatan perdana (stand awal / belum ada riwayat bulan lalu)
        if ($isFirstReading || $angkaBulanLalu <= 0) {
            return [
                'status_verifikasi' => 'valid',
                'catatan_anomali' => null,
                'pemakaian' => 0.0,
            ];
        }

        $pemakaian = $angkaSekarang - $angkaBulanLalu;

        if ($pemakaian < 0) {
            return [
                'status_verifikasi' => 'ter_flag',
                'catatan_anomali' => "Angka lebih kecil dari bulan lalu ({$pemakaian} m³)",
                'pemakaian' => $pemakaian,
            ];
        }

        // Rule OCR confidence rendah hanya berlaku jika petugas belum memverifikasi/mengoreksi angka
        if (! $isOcrCorrected && $confidenceScore !== null && $confidenceScore < self::MIN_OCR_CONFIDENCE) {
            return [
                'status_verifikasi' => 'ter_flag',
                'catatan_anomali' => "Foto miring/redup, OCR confidence rendah ({$confidenceScore}%)",
                'pemakaian' => $pemakaian,
            ];
        }

        if ($pemakaian == 0.0 && $statusHuni === 'Dihuni') {
            return [
                'status_verifikasi' => 'ter_flag',
                'catatan_anomali' => 'Pemakaian nol pada unit hunian aktif',
                'pemakaian' => 0.0,
            ];
        }

        $batasLonjakan = max($rataRataNormal * 2.0, 50.0);
        if ($pemakaian > $batasLonjakan) {
            return [
                'status_verifikasi' => 'ter_flag',
                'catatan_anomali' => 'Pemakaian melonjak (>200% rata-rata)',
                'pemakaian' => $pemakaian,
            ];
        }

        return [
            'status_verifikasi' => 'valid',
            'catatan_anomali' => null,
            'pemakaian' => $pemakaian,
        ];
    }
}
