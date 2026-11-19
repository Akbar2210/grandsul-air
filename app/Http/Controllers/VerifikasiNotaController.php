<?php

namespace App\Http\Controllers;

use App\Services\CyberSecurity\QrVerificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifikasiNotaController extends Controller
{
    /**
     * Halaman publik hasil scan QR Code nota (tanpa login) untuk memverifikasi keaslian nota pembayaran.
     */
    public function __invoke(Request $request, QrVerificationService $qrVerificationService): View
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'hash' => ['nullable', 'string', 'size:64'],
            'sig' => ['nullable', 'string', 'max:64'],
        ]);

        $result = $qrVerificationService->verifyScannedReceipt(
            (int) $validated['id'],
            $validated['hash'] ?? null,
            $validated['sig'] ?? null
        );

        return view('verifikasi-nota', ['result' => $result]);
    }
}
