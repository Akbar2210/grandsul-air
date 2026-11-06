<?php

use App\Http\Controllers\ProfileController;
// 1. Import Controller Dashboard Petugas
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// ==============================================================
// 2. BLOK RUTE BARU UNTUK PETUGAS & NOTIFIKASI
// ==============================================================

// Rute khusus Petugas
Route::middleware(['auth'])->prefix('petugas')->name('petugas.')->group(function () {
    // Rute utama Dashboard Mobile Petugas (menjalankan DashboardController)
    Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');
    
    // Rute sementara (dummy) agar tombol di halaman dashboard.blade.php tidak error 404
    Route::get('/catat-meter/create', function() { return "Halaman Kamera OCR (Segera Hadir)"; })->name('catat-meter.create');
    Route::get('/riwayat', function() { return "Halaman Semua Riwayat (Segera Hadir)"; })->name('riwayat');
});

// Rute umum untuk Notifikasi (bisa diakses Admin & Petugas)
Route::middleware(['auth'])->group(function () {
    Route::get('/notifikasi', function() { return "Halaman Feed Notifikasi (Segera Hadir)"; })->name('notifikasi.index');
});
// ==============================================================


require __DIR__.'/auth.php';