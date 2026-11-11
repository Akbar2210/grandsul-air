<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole('petugas')) {
            return redirect()->route('petugas.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->hasRole('petugas')) {
        return redirect()->route('petugas.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes (Protected with RBAC role:admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // 1. Dashboard Utama
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // 2. Tinjau Data Ter-flag
    Route::get('/terflag', [AdminDashboardController::class, 'terflag'])->name('terflag');
    Route::post('/terflag/{id}/resolve', [AdminDashboardController::class, 'resolveTerflag'])->name('terflag.resolve');

    // 3. Tagihan & Batch Processing
    Route::get('/tagihan', [AdminDashboardController::class, 'tagihan'])->name('tagihan');
    Route::post('/tagihan/batch', [AdminDashboardController::class, 'batchTagihan'])->name('tagihan.batch');

    // 4. Pembayaran (Catat Transaksi)
    Route::get('/pembayaran', [AdminDashboardController::class, 'pembayaran'])->name('pembayaran');
    Route::post('/pembayaran', [AdminDashboardController::class, 'storePembayaran'])->name('pembayaran.store');

    // 5. Riwayat Pembayaran & Cetak Nota Kriptografis RSA
    Route::get('/riwayat-pembayaran', [AdminDashboardController::class, 'riwayatPembayaran'])->name('riwayat-pembayaran');
    Route::get('/nota/{id}/cetak', [AdminDashboardController::class, 'cetakNota'])->name('nota.cetak');

    // 6. Laporan Rekapitulasi Bulanan
    Route::get('/laporan', [AdminDashboardController::class, 'laporan'])->name('laporan');
    Route::get('/laporan/export-pdf', [AdminDashboardController::class, 'exportLaporanPdf'])->name('laporan.pdf');

    // 7. Data Master (Unit, Tarif Progresif, Petugas, Blok)
    Route::get('/master', [AdminDashboardController::class, 'master'])->name('master');
    Route::post('/master/unit', [AdminDashboardController::class, 'storeUnit'])->name('master.unit.store');
    Route::post('/master/tarif', [AdminDashboardController::class, 'storeTarif'])->name('master.tarif.store');

    // 8. Audit Log Aktivitas
    Route::get('/audit', [AdminDashboardController::class, 'audit'])->name('audit');
});

// Petugas Routes (Protected with RBAC role:petugas)
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');
    Route::get('/pindai', [PetugasDashboardController::class, 'pindai'])->name('pindai');
    Route::post('/pindai', [PetugasDashboardController::class, 'store'])->name('pindai.store');
    Route::get('/riwayat', [PetugasDashboardController::class, 'riwayat'])->name('riwayat');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';