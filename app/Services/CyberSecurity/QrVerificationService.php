<?php

namespace App\Services\CyberSecurity;

use App\Models\DigitalSignature;
use App\Models\Pembayaran;

/**
 * Konsentrasi Cyber Security (Akbar Hidayat)
 * Fokus: Pemindaian & Verifikasi Keaslian Nota Publik (RSA-2048 Public Key + SHA-256 Integrity Check).
 */
class QrVerificationService
{
    public function __construct(
        private RsaSignatureService $rsaService,
    ) {}

    /**
     * Memverifikasi keaslian nota berdasarkan parameter hasil scan QR Code:
     * - Menghitung ulang Hash A (SHA-256) dari data transaksi aktual di database.
     * - Memverifikasi digital signature menggunakan Public Key RSA-2048.
     * - Mencocokkan integritas Hash & Signature terhadap manipulasi nominal/periode/pemalsuan.
     *
     * @return array{
     *     is_authentic: bool,
     *     status_label: string,
     *     reason: string,
     *     checks: array{terdaftar_di_sistem: bool, tanda_tangan_cocok: bool, integritas_hash_cocok: bool},
     *     data_nota: ?array{
     *         nomor_nota: string,
     *         unit_penghuni: string,
     *         periode: string,
     *         volume_m3: int,
     *         total_bayar: float,
     *         algoritma: string,
     *         fingerprint_sha256: string
     *     }
     * }
     */
    public function verifyScannedReceipt(
        int $pembayaranId,
        ?string $scannedHash = null,
        ?string $scannedSignaturePrefix = null
    ): array {
        $pembayaran = Pembayaran::with(['tagihan.unit'])->find($pembayaranId);

        if (! $pembayaran || ! $pembayaran->tagihan || $pembayaran->tagihan->status_bayar !== 'lunas') {
            return [
                'is_authentic' => false,
                'status_label' => 'NOTA TIDAK VALID / PALSU',
                'reason' => 'Nomor nota tidak ditemukan atau tagihan belum berstatus Lunas di sistem pengelola.',
                'checks' => [
                    'terdaftar_di_sistem' => false,
                    'tanda_tangan_cocok' => false,
                    'integritas_hash_cocok' => false,
                ],
                'data_nota' => null,
            ];
        }

        $signatureRecord = DigitalSignature::where('tagihan_id', $pembayaran->tagihan_id)->first();
        if (! $signatureRecord) {
            return [
                'is_authentic' => false,
                'status_label' => 'NOTA TIDAK VALID / PALSU',
                'reason' => 'Nota ini belum pernah disahkan dengan tanda tangan digital RSA-2048 oleh server.',
                'checks' => [
                    'terdaftar_di_sistem' => true,
                    'tanda_tangan_cocok' => false,
                    'integritas_hash_cocok' => false,
                ],
                'data_nota' => null,
            ];
        }

        $canonicalData = $this->rsaService->buildCanonicalData($pembayaran);
        $recalculatedHashA = $this->rsaService->generateHashSha256($canonicalData);
        $storedHashB = $signatureRecord->hash_sha256;

        $hashMatchesDatabase = hash_equals($storedHashB, $recalculatedHashA);
        $hashMatchesQr = ($scannedHash === null || $scannedHash === '')
            ? true
            : hash_equals($recalculatedHashA, $scannedHash);

        $integritasHashCocok = $hashMatchesDatabase && $hashMatchesQr;

        $tandaTanganCocok = $this->verifyRsaSignature(
            $canonicalData,
            $signatureRecord->digital_signature,
            $scannedSignaturePrefix
        );

        if (! $integritasHashCocok || ! $tandaTanganCocok) {
            return [
                'is_authentic' => false,
                'status_label' => 'NOTA TIDAK VALID / PALSU / DIMANIPULASI',
                'reason' => 'Tanda tangan digital RSA-2048 atau digest SHA-256 tidak cocok. Data nota terindikasi telah diubah.',
                'checks' => [
                    'terdaftar_di_sistem' => true,
                    'tanda_tangan_cocok' => $tandaTanganCocok,
                    'integritas_hash_cocok' => $integritasHashCocok,
                ],
                'data_nota' => null,
            ];
        }

        $nomorRumah = $pembayaran->tagihan->unit->nomor_rumah ?? '—';
        $namaPemilik = $this->maskOwnerName((string) ($pembayaran->tagihan->unit->nama_pemilik ?? 'Warga'));

        return [
            'is_authentic' => true,
            'status_label' => 'NOTA ASLI & SAH',
            'reason' => 'Tanda tangan digital RSA-2048 dan integritas hash SHA-256 terverifikasi asli.',
            'checks' => [
                'terdaftar_di_sistem' => true,
                'tanda_tangan_cocok' => true,
                'integritas_hash_cocok' => true,
            ],
            'data_nota' => [
                'nomor_nota' => 'NT-2610-'.str_pad((string) $pembayaran->id, 4, '0', STR_PAD_LEFT),
                'unit_penghuni' => "{$nomorRumah} - {$namaPemilik}",
                'periode' => (string) $pembayaran->tagihan->priode,
                'volume_m3' => (int) $pembayaran->tagihan->volume_pemakaian,
                'total_bayar' => (float) $pembayaran->jumlah_bayar,
                'algoritma' => 'RSA-2048 + SHA-256',
                'fingerprint_sha256' => substr($recalculatedHashA, 0, 8).'...'.substr($recalculatedHashA, -6),
            ],
        ];
    }

    /**
     * Memverifikasi ciphertext signature terhadap data kanonik menggunakan Public Key RSA-2048.
     */
    public function verifyRsaSignature(
        string $canonicalData,
        string $storedSignatureBase64,
        ?string $scannedSignaturePrefix = null
    ): bool {
        if ($scannedSignaturePrefix !== null && $scannedSignaturePrefix !== '') {
            if (! str_starts_with($storedSignatureBase64, $scannedSignaturePrefix)) {
                return false;
            }
        }

        $keys = $this->rsaService->ensureKeyPair();
        $publicKey = $keys['public_key'];

        if (str_starts_with($publicKey, '-----BEGIN')) {
            $decoded = base64_decode($storedSignatureBase64, true);
            if ($decoded === false) {
                return false;
            }

            return @openssl_verify($canonicalData, $decoded, $publicKey, OPENSSL_ALGO_SHA256) === 1;
        }

        $expectedSignature = $this->rsaService->signCanonicalData($canonicalData);

        return hash_equals($expectedSignature, $storedSignatureBase64);
    }

    /**
     * Menyamarkan sebagian nama pemilik unit untuk menjaga privasi warga pada halaman publik (SRS FR-007 & OR-004).
     * Contoh: "Budi Santoso" -> "Budi S."
     */
    public function maskOwnerName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        if (count($parts) <= 1) {
            return $fullName;
        }

        $firstName = $parts[0];
        $secondInitial = mb_substr($parts[1], 0, 1);

        return "{$firstName} {$secondInitial}.";
    }
}
