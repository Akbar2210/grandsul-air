<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
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