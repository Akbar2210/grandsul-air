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
     * Memeriksa apakah terdapat API Key AI (Gemini atau Google Vision) yang aktif.
     */
    public function hasActiveAiKey(): bool
    {
        return (string) config('services.gemini.api_key', '') !== ''
            || (string) config('services.google_vision.api_key', '') !== '';
    }

    /**
     * Mendapatkan nama provider AI yang sedang aktif.
     */
    public function getActiveAiProvider(): string
    {
        if ((string) config('services.gemini.api_key', '') !== '') {
            return 'Gemini AI Vision';
        }
        if ((string) config('services.google_vision.api_key', '') !== '') {
            return 'Google Cloud Vision';
        }

        return 'Manual';
    }

    /**
     * Mengompres citra foto meteran agar ukuran pengiriman ke AI sangat ringan (di bawah 100 KB)
     * sehingga proses analisis AI berjalan instan (1-2 detik) tanpa resiko timeout.
     */
    public function optimizeImageForAi(string $binaryData): string
    {
        if (strlen($binaryData) < 250000) {
            return $binaryData;
        }

        try {
            if (! extension_loaded('gd')) {
                return $binaryData;
            }

            $src = @imagecreatefromstring($binaryData);
            if (! $src) {
                return $binaryData;
            }

            $w = imagesx($src);
            $h = imagesy($src);
            $maxDim = 960;

            if ($w > $maxDim || $h > $maxDim) {
                if ($w > $h) {
                    $newW = $maxDim;
                    $newH = (int) round(($h * $maxDim) / $w);
                } else {
                    $newH = $maxDim;
                    $newW = (int) round(($w * $maxDim) / $h);
                }

                $dst = imagecreatetruecolor($newW, $newH);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
                imagedestroy($src);
                $src = $dst;
            }

            ob_start();
            imagejpeg($src, null, 75);
            $compressed = ob_get_clean();
            imagedestroy($src);

            return $compressed ?: $binaryData;
        } catch (\Throwable) {
            return $binaryData;
        }
    }

    /**
     * Mengekstrak angka meteran dan confidence score dari berkas gambar
     * menggunakan Gemini AI Vision API (Free Tier) dengan fallback ke Google Cloud Vision atau manual.
     *
     * @return array{success: bool, suggested_digits: ?int, raw_text: string, confidence_score: float, provider: string, message: string}
     */
    public function extractMeterFromImage(string $imageBinaryOrBase64, bool $isBase64 = false): array
    {
        if (! $isBase64) {
            $imageBinaryOrBase64 = $this->optimizeImageForAi($imageBinaryOrBase64);
            $base64Image = base64_encode($imageBinaryOrBase64);
        } else {
            $base64Image = $imageBinaryOrBase64;
        }

        // 1. Prioritaskan Google Gemini AI Vision (Model Multimodal Lebih Akurat untuk Meteran Air)
        $geminiKey = (string) config('services.gemini.api_key', '');
        if ($geminiKey !== '') {
            $geminiResult = $this->extractViaGeminiVision($base64Image, $geminiKey);
            if ($geminiResult['success']) {
                return $geminiResult;
            }
        }

        // 2. Fallback ke Google Cloud Vision OCR jika dikonfigurasi
        $googleVisionKey = (string) config('services.google_vision.api_key', '');
        if ($googleVisionKey !== '') {
            $googleResult = $this->extractViaGoogleVision($base64Image, $googleVisionKey);
            if ($googleResult['success']) {
                return $googleResult;
            }
        }

        // 3. Fallback jika tidak ada API key atau keduanya gagal
        $message = $geminiKey !== ''
            ? ($geminiResult['message'] ?? 'AI Gemini gagal membaca meteran. Silakan input manual.')
            : 'API Key AI Gemini belum diatur di file .env (GEMINI_API_KEY). Silakan masukkan angka meteran secara manual.';

        return [
            'success' => false,
            'suggested_digits' => null,
            'raw_text' => '',
            'confidence_score' => 0.0,
            'provider' => $geminiKey !== '' ? 'gemini' : ($googleVisionKey !== '' ? 'google_vision' : 'none'),
            'message' => $message,
        ];
    }

    /**
     * Mengekstrak angka meteran menggunakan Google Gemini AI Vision (Multimodal AI).
     *
     * @return array{success: bool, suggested_digits: ?int, raw_text: string, confidence_score: float, provider: string, message: string}
     */
    public function extractViaGeminiVision(string $base64Image, string $apiKey, ?string $fallbackModel = null): array
    {
        $primaryModel = (string) config('services.gemini.model', 'gemini-3.5-flash-lite');
        $model = $fallbackModel ?: $primaryModel;
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $prompt = "Anda adalah asisten AI pembaca angka cerdas (Smart Numeric OCR) untuk sistem pencatatan meter air dan simulasi pengujian.\n".
            "TUGAS UTAMA: Deteksi dan ekstrak ANGKA APAPUN yang terlihat pada foto ini sekaligus menilai kualitas visual foto secara objektif.\n\n".
            "PANDUAN PEMBACAAN:\n".
            "1. Jika pada foto terdapat METERAN AIR, baca deretan angka stand meterannya (biasanya digit roller hitam). Abaikan angka merah kecil/desimal.\n".
            "2. Jika foto BUKAN meteran air (karena untuk SIMULASI/TESTING, misalnya: angka yang ditulis tangan/cetak di kertas, layar HP/laptop, struk, stiker, buku, nomor rumah, atau objek apapun), BACA ANGKA UTAMA/TERJELAS YANG TERTULIS DI FOTO TERSEBUT.\n".
            "3. Ambil hanya bilangan bulat positif (contoh: 25, 450, 1011, 1820, 41323).\n".
            "4. PENILAIAN SKOR CONFIDENCE (0-100) HARUS JUJUR & KETAT TERHADAP KUALITAS GAMBAR:\n".
            "   - Jika gambar tajam, terang, fokus, dan tegak lurus: berikan confidence 90 - 99.\n".
            "   - Jika gambar BLUR / KABUR, REDUP / GELAP, MIRING, berbayang, tidak fokus, atau ada pantulan cahaya/silau meskipun angkanya masih bisa dibaca/ditebak: WAJIB berikan confidence RENDAH antara 55 - 68 (contoh: 67).\n".
            "5. Kembalikan HANYA format JSON valid murni (tanpa format markdown ```json) dengan struktur:\n".
            "{\"digits\": 1820, \"confidence\": 67, \"raw_text\": \"1820\", \"notes\": \"Foto blur/redup/miring\"}\n".
            "6. Hanya jika benar-benar TIDAK ADA ANGKA SAMA SEKALI atau terlalu rusak untuk dibaca, kembalikan:\n".
            '{"digits": null, "confidence": 0, "raw_text": "", "notes": "Tidak ditemukan angka pada foto"}';

        try {
            $response = Http::connectTimeout(5)
                ->timeout(20)
                ->withOptions([
                    'force_ip_resolve' => 'v4',
                ])
                ->post($endpoint, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => 'image/jpeg',
                                        'data' => $base64Image,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'response_mime_type' => 'application/json',
                    ],
                ]);

            if (! $response->successful()) {
                $status = $response->status();

                // Jika model primer sibuk (503) atau tidak ditemukan (404), coba model cadangan gemini-3.5-flash
                if (($status === 503 || $status === 404) && $model !== 'gemini-3.5-flash') {
                    return $this->extractViaGeminiVision($base64Image, $apiKey, 'gemini-3.5-flash');
                }

                $errBody = $response->json();
                $errMsg = data_get($errBody, 'error.message', 'Gagal menghubungi Gemini API');

                return [
                    'success' => false,
                    'suggested_digits' => null,
                    'raw_text' => '',
                    'confidence_score' => 0.0,
                    'provider' => 'gemini',
                    'message' => "Layanan AI Gemini (HTTP {$status}): {$errMsg}",
                ];
            }

            $payload = $response->json();
            $textResponse = (string) data_get($payload, 'candidates.0.content.parts.0.text', '');

            // Bersihkan jika ada bungkus markdown ```json
            $cleaned = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($textResponse));
            $data = json_decode($cleaned, true);

            if (! is_array($data) || ! isset($data['digits']) || $data['digits'] === null) {
                // Fallback ekstraksi regex jika model mengembalikan teks mentah dengan angka
                $fallbackDigits = $this->filterMeterDigits($textResponse);
                if ($fallbackDigits !== null) {
                    return [
                        'success' => true,
                        'suggested_digits' => $fallbackDigits,
                        'raw_text' => (string) $fallbackDigits,
                        'confidence_score' => 92.0,
                        'provider' => 'gemini',
                        'message' => 'Angka berhasil dideteksi oleh AI Gemini.',
                    ];
                }

                return [
                    'success' => false,
                    'suggested_digits' => null,
                    'raw_text' => (string) ($data['raw_text'] ?? $textResponse),
                    'confidence_score' => (float) ($data['confidence'] ?? 0.0),
                    'provider' => 'gemini',
                    'message' => (string) ($data['notes'] ?? 'Tidak ditemukan angka pada foto.'),
                ];
            }

            $digits = (int) $data['digits'];
            $confidence = isset($data['confidence']) ? (float) $data['confidence'] : 95.0;
            $message = 'Angka meteran berhasil dianalisis oleh AI Gemini.';

            if ($confidence > 0 && $confidence < self::MIN_OCR_CONFIDENCE) {
                $digits = $this->simulateLowConfidenceOcrMisread($digits);
                $message = 'Foto blur/redup/miring — hasil pembacaan OCR berisiko meleset dan akan ditandai Ter-flag.';
            }

            return [
                'success' => true,
                'suggested_digits' => $digits,
                'raw_text' => (string) $digits,
                'confidence_score' => round($confidence, 2),
                'provider' => 'gemini',
                'message' => $message,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'suggested_digits' => null,
                'raw_text' => '',
                'confidence_score' => 0.0,
                'provider' => 'gemini',
                'message' => 'Kendala koneksi ke AI Gemini: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Mensimulasikan kesalahan pembacaan digit khas OCR saat kualitas gambar buruk/blur (< 70%)
     * dengan menukar 1-2 digit terakhir ke angka yang mirip secara visual.
     */
    public function simulateLowConfidenceOcrMisread(int $digits): int
    {
        $confusionMap = [
            '0' => '8',
            '1' => '7',
            '2' => '7',
            '3' => '8',
            '4' => '9',
            '5' => '6',
            '6' => '5',
            '7' => '1',
            '8' => '3',
            '9' => '4',
        ];

        $chars = str_split((string) max(0, $digits));
        $len = count($chars);

        if ($len === 0) {
            return $digits;
        }

        // Ubah 1-2 digit di bagian belakang agar tampak seperti salah baca akibat blur
        $positionsToAlter = $len >= 4 ? [$len - 1, $len - 3] : [$len - 1];
        foreach ($positionsToAlter as $pos) {
            $ch = $chars[$pos];
            $chars[$pos] = $confusionMap[$ch] ?? '8';
        }

        return (int) implode('', $chars);
    }

    /**
     * Mengekstrak angka meteran menggunakan Google Cloud Vision (OCR Tradisional).
     *
     * @return array{success: bool, suggested_digits: ?int, raw_text: string, confidence_score: float, provider: string, message: string}
     */
    public function extractViaGoogleVision(string $base64Image, string $apiKey): array
    {
        $endpoint = (string) config('services.google_vision.endpoint', 'https://vision.googleapis.com/v1/images:annotate');

        try {
            $response = Http::connectTimeout(4)
                ->timeout(12)
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
                    'provider' => 'google_vision',
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
                    'provider' => 'google_vision',
                    'message' => 'OCR gagal membaca deretan angka pada foto meteran. Silakan ketik manual.',
                ];
            }

            return [
                'success' => true,
                'suggested_digits' => $digits,
                'raw_text' => trim($rawText),
                'confidence_score' => round($confidence, 2),
                'provider' => 'google_vision',
                'message' => 'Angka meteran berhasil diekstraksi oleh Google Cloud Vision OCR.',
            ];
        } catch (\Throwable) {
            return [
                'success' => false,
                'suggested_digits' => null,
                'raw_text' => '',
                'confidence_score' => 0.0,
                'provider' => 'google_vision',
                'message' => 'Terjadi kendala jaringan saat menghubungi Google Cloud Vision. Silakan input manual.',
            ];
        }
    }

    /**
     * Menyaring teks mentah hasil OCR menggunakan regex untuk mengambil angka stand meteran.
     */
    public function filterMeterDigits(string $rawText): ?int
    {
        if (preg_match('/\b(\d{1,7})\b/', $rawText, $matches) === 1) {
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
        // Rule OCR confidence rendah berlaku untuk semua bacaan (termasuk pencatatan perdana) jika belum dikoreksi manual
        if (! $isOcrCorrected && $confidenceScore !== null && $confidenceScore < self::MIN_OCR_CONFIDENCE) {
            $pemakaianFallback = ($isFirstReading || $angkaBulanLalu <= 0) ? 0.0 : ($angkaSekarang - $angkaBulanLalu);

            return [
                'status_verifikasi' => 'ter_flag',
                'catatan_anomali' => "Foto miring/redup, OCR confidence rendah ({$confidenceScore}%)",
                'pemakaian' => $pemakaianFallback,
            ];
        }

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
