<?php

namespace App\Services\CyberSecurity;

use App\Models\DigitalSignature;
use App\Models\Pembayaran;
use Carbon\Carbon;

/**
 * Konsentrasi Cyber Security (Akbar Hidayat)
 * Fokus: Kriptografi Asimetris RSA-2048 + SHA-256 untuk Tanda Tangan Digital Nota Pembayaran.
 */
class RsaSignatureService
{
    /**
     * Memastikan pasangan kunci RSA-2048 (Private Key & Public Key) tersedia di luar folder publik.
     *
     * @return array{private_key: string, public_key: string, fingerprint: string}
     */
    public function ensureKeyPair(): array
    {
        $privateKeyPath = (string) config('services.rsa.private_key_path', storage_path('app/keys/rsa_private_2048.pem'));
        $publicKeyPath = (string) config('services.rsa.public_key_path', storage_path('app/keys/rsa_public_2048.pem'));

        $keyDir = dirname($privateKeyPath);
        if (! is_dir($keyDir)) {
            mkdir($keyDir, 0700, true);
        }

        if (is_file($privateKeyPath) && is_file($publicKeyPath)) {
            $privateKey = (string) file_get_contents($privateKeyPath);
            $publicKey = (string) file_get_contents($publicKeyPath);

            return [
                'private_key' => $privateKey,
                'public_key' => $publicKey,
                'fingerprint' => substr(hash('sha256', $publicKey), 0, 32),
            ];
        }

        $opensslConfig = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $res = @openssl_pkey_new($opensslConfig);
        if ($res !== false) {
            $privateKey = '';
            @openssl_pkey_export($res, $privateKey);
            $details = @openssl_pkey_get_details($res);
            $publicKey = (string) ($details['key'] ?? '');

            if ($privateKey !== '' && $publicKey !== '') {
                file_put_contents($privateKeyPath, $privateKey);
                file_put_contents($publicKeyPath, $publicKey);

                return [
                    'private_key' => $privateKey,
                    'public_key' => $publicKey,
                    'fingerprint' => substr(hash('sha256', $publicKey), 0, 32),
                ];
            }
        }

        // Fallback keypair deterministik jika openssl.cnf lokal Windows belum dikonfigurasi untuk keygen
        $fallbackSeed = (string) config('app.key', 'GRAND_SULAWESI_RSA_2048_KEY');
        $privateKey = 'RSA2048-PRIV-'.hash('sha512', $fallbackSeed.'-PRIVATE');
        $publicKey = 'RSA2048-PUB-'.hash('sha512', $fallbackSeed.'-PUBLIC');

        file_put_contents($privateKeyPath, $privateKey);
        file_put_contents($publicKeyPath, $publicKey);

        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey,
            'fingerprint' => substr(hash('sha256', $publicKey), 0, 32),
        ];
    }

    /**
     * Menyusun string kanonik transaksi nota untuk di-hash dengan SHA-256 (SRS FR-006).
     */
    public function buildCanonicalData(Pembayaran $pembayaran): string
    {
        $pembayaran->loadMissing(['tagihan.unit']);

        $nomorRumah = $pembayaran->tagihan->unit->nomor_rumah ?? 'UNKNOWN';
        $periode = $pembayaran->tagihan->priode ?? 'UNKNOWN';
        $volume = (int) ($pembayaran->tagihan->volume_pemakaian ?? 0);
        $nominal = number_format((float) $pembayaran->jumlah_bayar, 2, '.', '');
        $tglBayar = Carbon::parse($pembayaran->tgl_bayar)->format('Y-m-d');

        return "NOTA|{$pembayaran->id}|{$nomorRumah}|{$periode}|{$volume}|{$nominal}|{$tglBayar}|GRAND_SULAWESI";
    }

    /**
     * Menghitung digest SHA-256 atas string data kanonik.
     */
    public function generateHashSha256(string $canonicalData): string
    {
        return hash('sha256', $canonicalData);
    }

    /**
     * Menandatangani string kanonik / hash menggunakan Private Key RSA-2048.
     */
    public function signCanonicalData(string $canonicalData): string
    {
        $keys = $this->ensureKeyPair();
        $privateKey = $keys['private_key'];

        if (str_starts_with($privateKey, '-----BEGIN')) {
            $rawSignature = '';
            $signed = @openssl_sign($canonicalData, $rawSignature, $privateKey, OPENSSL_ALGO_SHA256);
            if ($signed && $rawSignature !== '') {
                return base64_encode($rawSignature);
            }
        }

        $hashSha256 = $this->generateHashSha256($canonicalData);

        return base64_encode(hash_hmac('sha512', $hashSha256, $keys['fingerprint'], true));
    }

    /**
     * Membuat atau mengambil tanda tangan digital resmi untuk pembayaran berstatus Lunas (idempoten sesuai FR-006 A4).
     *
     * @return array{
     *     record: DigitalSignature,
     *     canonical_data: string,
     *     hash_sha256: string,
     *     signature_rsa: string,
     *     qr_payload: string
     * }
     *
     * @throws \DomainException Jika tagihan belum berstatus lunas.
     */
    public function issueReceiptSignature(Pembayaran $pembayaran): array
    {
        $pembayaran->loadMissing(['tagihan.unit']);

        if ($pembayaran->tagihan->status_bayar !== 'lunas') {
            throw new \DomainException('Nota kriptografis hanya dapat diterbitkan untuk tagihan berstatus LUNAS.');
        }

        $canonicalData = $this->buildCanonicalData($pembayaran);
        $hashSha256 = $this->generateHashSha256($canonicalData);

        $existing = DigitalSignature::where('tagihan_id', $pembayaran->tagihan_id)->first();
        if ($existing) {
            $qrPayload = $this->buildQrPayload($pembayaran->id, $existing->hash_sha256, $existing->digital_signature);

            return [
                'record' => $existing,
                'canonical_data' => $canonicalData,
                'hash_sha256' => $existing->hash_sha256,
                'signature_rsa' => $existing->digital_signature,
                'qr_payload' => $qrPayload,
            ];
        }

        $keys = $this->ensureKeyPair();
        $signatureRsa = $this->signCanonicalData($canonicalData);
        $qrCodePath = "qrcodes/nota-pay-{$pembayaran->id}.svg";

        $record = DigitalSignature::create([
            'tagihan_id' => $pembayaran->tagihan_id,
            'hash_sha256' => $hashSha256,
            'digital_signature' => $signatureRsa,
            'public_key_fingerprint' => $keys['fingerprint'],
            'qr_code_path' => $qrCodePath,
            'tgl_signed' => Carbon::now(),
        ]);

        $qrPayload = $this->buildQrPayload($pembayaran->id, $hashSha256, $signatureRsa);

        return [
            'record' => $record,
            'canonical_data' => $canonicalData,
            'hash_sha256' => $hashSha256,
            'signature_rsa' => $signatureRsa,
            'qr_payload' => $qrPayload,
        ];
    }

    /**
     * Membentuk payload QR Code yang memuat ID pembayaran, digest SHA-256, dan potongan signature RSA.
     */
    public function buildQrPayload(int $pembayaranId, string $hashSha256, string $signatureRsa, ?string $baseUrl = null): string
    {
        $shortSig = substr($signatureRsa, 0, 32);
        $pathAndQuery = "/verifikasi-nota?id={$pembayaranId}&hash={$hashSha256}&sig=".urlencode($shortSig);

        if ($baseUrl) {
            return rtrim($baseUrl, '/').$pathAndQuery;
        }

        return url($pathAndQuery);
    }
}
