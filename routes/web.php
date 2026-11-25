<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerifikasiNotaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

Route::get('/verifikasi-nota', VerifikasiNotaController::class)
    ->middleware('throttle:30,1')
    ->name('verifikasi-nota');

// Admin Routes (Protected with RBAC role:admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // 1. Dashboard Utama
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // 2. Tinjau Data Ter-flag
    Route::get('/terflag', [AdminDashboardController::class, 'terflag'])->name('terflag');
    Route::post('/terflag/{id}/resolve', [AdminDashboardController::class, 'resolveTerflag'])->name('terflag.resolve');

    // 3. Tagihan & Batch Processing + Cetak Nota Fisik Tagihan + Surat Tunggakan Otomatis (>= 4 Bulan)
    Route::get('/tagihan', [AdminDashboardController::class, 'tagihan'])->name('tagihan');
    Route::post('/tagihan/batch', [AdminDashboardController::class, 'batchTagihan'])->name('tagihan.batch');
    Route::get('/tagihan/{id}/nota', [AdminDashboardController::class, 'cetakNotaTagihan'])->name('tagihan.nota');
    Route::get('/surat-tunggakan', [AdminDashboardController::class, 'suratTunggakan'])->name('surat-tunggakan');
    Route::get('/surat-tunggakan/{unitId}/cetak', [AdminDashboardController::class, 'cetakSuratTunggakan'])->name('surat-tunggakan.cetak');

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
    Route::post('/master/unit/import', [AdminDashboardController::class, 'importUnits'])->name('master.unit.import');
    Route::get('/master/unit/template', [AdminDashboardController::class, 'downloadUnitTemplate'])->name('master.unit.template');
    Route::post('/master/unit/{id}', [AdminDashboardController::class, 'updateUnit'])->name('master.unit.update');
    Route::delete('/master/unit/{id}', [AdminDashboardController::class, 'destroyUnit'])->name('master.unit.destroy');
    Route::post('/master/tarif', [AdminDashboardController::class, 'storeTarif'])->name('master.tarif.store');
    Route::post('/master/tarif/{id}', [AdminDashboardController::class, 'updateTarif'])->name('master.tarif.update');
    Route::delete('/master/tarif/{id}', [AdminDashboardController::class, 'destroyTarif'])->name('master.tarif.destroy');
    Route::post('/master/petugas', [AdminDashboardController::class, 'storePetugas'])->name('master.petugas.store');
    Route::post('/master/petugas/{id}', [AdminDashboardController::class, 'updatePetugas'])->name('master.petugas.update');
    Route::post('/master/petugas/{id}/toggle-status', [AdminDashboardController::class, 'togglePetugasStatus'])->name('master.petugas.toggle-status');
    Route::delete('/master/petugas/{id}', [AdminDashboardController::class, 'destroyPetugas'])->name('master.petugas.destroy');
    Route::post('/master/blok', [AdminDashboardController::class, 'storeBlok'])->name('master.blok.store');

    // 8. Audit Log Aktivitas (Read-Only Immutable)
    Route::get('/audit', [AdminDashboardController::class, 'audit'])->name('audit');
    Route::get('/audit-log', [AdminDashboardController::class, 'audit'])->name('audit-log');
    Route::match(['put', 'patch', 'delete'], '/audit/{id}', fn () => abort(403, 'Audit log bersifat immutable (tidak dapat diubah atau dihapus).'))->name('audit.immutable');
    Route::match(['put', 'patch', 'delete'], '/audit-log/{id}', fn () => abort(403, 'Audit log bersifat immutable (tidak dapat diubah atau dihapus).'))->name('audit-log.immutable');

    // 9. Notifikasi Sistem Interaktif
    Route::get('/notifikasi/unread-list', [AdminDashboardController::class, 'getUnreadNotifikasi'])->name('notifikasi.unread-list');
    Route::post('/notifikasi/read-all', [AdminDashboardController::class, 'readAllNotifikasi'])->name('notifikasi.read-all');
    Route::post('/notifikasi/{id}/read', [AdminDashboardController::class, 'readNotifikasi'])->name('notifikasi.read');
});

// Petugas Routes (Protected with RBAC role:petugas|admin untuk mendukung simulasi multi-peran)
Route::middleware(['auth', 'role:petugas|admin'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');
    Route::get('/pindai', [PetugasDashboardController::class, 'pindai'])->name('pindai');
    Route::post('/pindai/ocr', [PetugasDashboardController::class, 'ocrScan'])->name('pindai.ocr');
    Route::post('/pindai', [PetugasDashboardController::class, 'store'])->name('pindai.store');
    Route::get('/riwayat', [PetugasDashboardController::class, 'riwayat'])->name('riwayat');
    Route::get('/riwayat/{id}', [PetugasDashboardController::class, 'showRiwayat'])->name('riwayat.show');

    // Notifikasi Petugas Lapangan
    Route::get('/notifikasi/unread-list', [PetugasDashboardController::class, 'getUnreadNotifikasi'])->name('notifikasi.unread-list');
    Route::post('/notifikasi/read-all', [PetugasDashboardController::class, 'readAllNotifikasi'])->name('notifikasi.read-all');
    Route::post('/notifikasi/{id}/read', [PetugasDashboardController::class, 'readNotifikasi'])->name('notifikasi.read');
});

Route::middleware('auth')->group(function () {
    Route::match(['get', 'post'], '/notifikasi/{id}/read', [PetugasDashboardController::class, 'readNotifikasiShared'])->name('notifikasi.read');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Fallback streaming foto & berkas storage publik (terutama untuk environment Windows/local)
Route::get('/storage/{path}', function (string $path) {
    if (Storage::disk('public')->exists($path)) {
        return Storage::disk('public')->response($path);
    }

    $publicFile = public_path('storage/'.$path);
    if (file_exists($publicFile)) {
        return response()->file($publicFile);
    }

    abort(404);
})->where('path', '.*');

require __DIR__.'/auth.php';
