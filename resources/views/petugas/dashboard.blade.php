<x-app-layout>
    <div class="min-h-screen bg-ice-white pb-12 font-sans">
        <!-- Topbar Mobile (Navy Deep Blue) -->
        <div class="bg-navy border-b border-navy-600 sticky top-0 z-10 px-5 py-4 flex justify-between items-center shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-cyan text-navy font-bold flex items-center justify-center text-sm shadow-sm">
                    {{ substr(Auth::user()->name ?? 'P', 0, 1) }}
                </div>
                <div>
                    <h1 class="font-bold text-sm text-white leading-tight">{{ Auth::user()->name ?? 'Petugas Lapangan' }}</h1>
                    <p class="text-[11px] text-cyan font-medium flex items-center gap-1.5 mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-cyan animate-pulse"></span> GPS Aktif
                    </p>
                </div>
            </div>

            <!-- Badge Notifikasi (FP-6) -->
            <a href="{{ route('notifikasi.index') }}" class="relative p-2 text-slate-300 hover:text-cyan transition focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                @if(isset($unreadNotifications) && $unreadNotifications > 0)
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-cyan rounded-full"></span>
                @endif
            </a>
        </div>

        <div class="max-w-md mx-auto px-5 pt-6 space-y-5">

            <!-- PRIMARY CTA BUTTON (Neon Cyan 10% Aksen + Teks Navy) -->
            <a href="{{ route('petugas.catat-meter.create') }}" 
               class="block bg-cyan hover:bg-cyan-600 text-navy p-6 rounded-2xl shadow-card text-center transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-4 focus:ring-cyan-glow">
                <div class="w-14 h-14 bg-navy text-cyan rounded-xl flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h2 class="font-extrabold text-lg tracking-wide uppercase">AMBIL FOTO METERAN</h2>
                <p class="text-xs text-navy/80 mt-1 font-medium">Pindai meteran via OCR & simpan koordinat GPS (FI-1)</p>
            </a>

            <!-- RINGKASAN PROGRES LAPANGAN -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-navy-200 shadow-card text-center">
                    <p class="text-xs font-semibold text-navy-400 uppercase tracking-wider">Sudah Dicatat</p>
                    <p class="text-2xl font-bold text-navy mt-1">{{ $totalDicatat ?? 0 }} <span class="text-xs font-normal text-navy-400">Unit</span></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-navy-200 shadow-card text-center">
                    <p class="text-xs font-semibold text-navy-400 uppercase tracking-wider">Sisa Target</p>
                    <p class="text-2xl font-bold text-navy mt-1">{{ $sisaTarget ?? 0 }} <span class="text-xs font-normal text-navy-400">Unit</span></p>
                </div>
            </div>

            <!-- RIWAYAT PENCATATAN TERAKHIR (FP-5) -->
            <div class="bg-white rounded-2xl border border-navy-200 shadow-card overflow-hidden">
                <div class="p-5 border-b border-navy-200 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-sm text-navy">Riwayat Pencatatan (FP-5)</h3>
                        <p class="text-[11px] text-navy-400">Input terbaru oleh Anda</p>
                    </div>
                    <a href="{{ route('petugas.riwayat') }}" class="text-xs font-semibold text-navy hover:text-cyan-600 transition">Lihat Semua →</a>
                </div>

                <div class="divide-y divide-navy-200">
                    @forelse($riwayatPencatatan ?? [] as $bacaan)
                        <div class="p-4 flex items-center justify-between hover:bg-ice-white transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-ice-white border border-navy-200 overflow-hidden flex-shrink-0 flex items-center justify-center text-navy-400">
                                    @if($bacaan->foto_meter)
                                        <img src="{{ asset('storage/' . $bacaan->foto_meter) }}" class="w-full h-full object-cover" alt="Foto Meter">
                                    @else
                                        <svg class="w-5 h-5 text-navy-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-bold text-xs text-navy">Unit {{ $bacaan->unit->nomor_rumah ?? 'A-01' }}</p>
                                    <p class="text-[11px] text-navy-400 font-mono">Angka: <strong class="text-navy">{{ $bacaan->angka_meter }} m³</strong></p>
                                </div>
                            </div>
                            <div>
                                @if($bacaan->status_verifikasi == 'ter_flag')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-medium bg-amber-100 text-amber-800">
                                        Ter-flag ⚠️
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-800">
                                        Valid ✓
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-navy-400 text-xs">
                            Belum ada riwayat pencatatan bulan ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>