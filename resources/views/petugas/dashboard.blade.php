<<<<<<< HEAD
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
=======
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Petugas Lapangan — Grand Sulawesi Air</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#EEF2F6] font-sans text-[#1E293B] antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile Overlay Drawer --}}
    <div
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-[#0F172A]/50 backdrop-blur-xs z-40 lg:hidden"
        style="display: none;"
    ></div>

    <div class="min-h-screen flex">
        {{-- ==================== SIDEBAR KIRI (NAVY DEEP BLUE - PERSIS DASHBOARD ADMIN) ==================== --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 w-[250px] bg-[#0F172A] flex flex-col justify-between transition-transform duration-300 ease-in-out lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div>
                {{-- Brand Header --}}
                <div class="h-20 px-6 flex items-center justify-between border-b border-[#1E293B]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#1E293B] flex items-center justify-center p-2 shadow-xs">
                            <x-brand-logo class="w-full h-full object-contain" />
                        </div>
                        <div>
                            <span class="text-sm font-bold text-white tracking-wide block leading-none">GRAND SULAWESI</span>
                            <span class="text-[11px] text-[#64748B] font-medium tracking-tight mt-1 block">Petugas Lapangan</span>
                        </div>
                    </div>
                    {{-- Mobile close --}}
                    <button @click="sidebarOpen = false" class="lg:hidden text-[#94A3B8] hover:text-white p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Section Label --}}
                <div class="px-6 pt-6 pb-2 text-[11px] font-bold text-[#64748B] uppercase tracking-wider">
                    MAIN MENU
                </div>

                {{-- Nav Items --}}
                <nav class="px-3 space-y-1">
                    <a href="{{ route('petugas.dashboard') }}" class="menu-item-modern active">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                        <span>Daftar Unit</span>
                    </a>

                    <a href="{{ route('petugas.pindai') }}" class="menu-item-modern">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                        </svg>
                        <span>Pindai Meter</span>
                    </a>

                    <a href="{{ route('petugas.riwayat') }}" class="menu-item-modern">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Riwayat Data</span>
                    </a>
                </nav>
            </div>

            {{-- Logout Button --}}
            <div class="p-4 border-t border-[#1E293B]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[#94A3B8] hover:text-[#EF4444] hover:bg-[#1E293B] text-sm font-medium transition-colors">
                        <svg class="w-5 h-5 text-[#EF4444]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- ==================== MAIN CONTENT CANVAS ==================== --}}
        <div class="flex-1 lg:pl-[250px] flex flex-col min-w-0">

            {{-- TOPBAR BERSIH (RESPONSIF MOBILE & DESKTOP) --}}
            <header class="px-4 sm:px-6 lg:px-10 pt-5 sm:pt-7 pb-4 sm:pb-5 border-b border-[#E2E8F0]/80 bg-white/50 backdrop-blur-xs">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3.5 sm:gap-4">
                    
                    {{-- Baris 1: Judul di Kiri & Profil Avatar Sejajar di Kanan (Pada Mobile) --}}
                    <div class="flex items-center justify-between w-full md:w-auto gap-3">
                        <div class="flex items-center gap-2.5 sm:gap-3">
                            {{-- Hamburger Mobile --}}
                            <button @click="sidebarOpen = true" class="lg:hidden text-[#1E293B] p-2 rounded-lg hover:bg-[#F1F5F9] transition-colors border border-[#E2E8F0] shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                            </button>

                            {{-- Judul Halaman --}}
                            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-[#0F172A] tracking-tight leading-tight">
                                Daftar Unit & Pencatatan Meter Air
                            </h1>
                        </div>

                        {{-- Profil Avatar Sejajar Judul Pada Mobile (Polos Tanpa Kotak Putih di Belakangnya) --}}
                        <div class="md:hidden shrink-0">
                            <div class="w-9 h-9 rounded-lg bg-[#0F172A] text-white font-bold text-xs flex items-center justify-center shadow-xs">
                                {{ strtoupper(substr(Auth::user()->name ?? 'HE', 0, 2)) }}
                            </div>
                        </div>
                    </div>

                    {{-- Baris 2 Pada Mobile: Tombol Mulai Pindai OCR di Tengah --}}
                    {{-- Pada Desktop: Berada di Sisi Kanan Sejajar Judul Bersama Profil Lengkap --}}
                    <div class="flex items-center justify-center md:justify-end gap-3 w-full md:w-auto pt-1 md:pt-0">
                        {{-- Tombol Mulai Pindai OCR --}}
                        <a href="{{ route('petugas.pindai') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 sm:py-2 rounded-lg bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-[0.98] text-white text-xs font-semibold shadow-xs hover:shadow-md transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                            </svg>
                            <span>Mulai Pindai Kamera OCR</span>
                        </a>

                        {{-- Profil Lengkap (Hanya Tampil di Layar MD ke Atas - Tanpa Kotak di Belakangnya) --}}
                        <div class="hidden md:flex items-center gap-3 shrink-0">
                            <div class="w-9 h-9 rounded-lg bg-[#0F172A] text-white font-bold text-xs flex items-center justify-center shadow-xs">
                                {{ strtoupper(substr(Auth::user()->name ?? 'HE', 0, 2)) }}
                            </div>
                            <div class="text-left">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-bold text-[#0F172A] block leading-tight">{{ Auth::user()->name ?? 'Hendra (Petugas Lapangan)' }}</span>
                                    <span class="w-2 h-2 rounded-full bg-[#10B981]" title="Online"></span>
                                </div>
                                <span class="text-[10px] text-[#64748B] font-medium block">Petugas Lapangan • #PTG-04</span>
                            </div>
                        </div>
                    </div>

                </div>
            </header>

            {{-- KONTEN UTAMA --}}
            <main class="flex-1 px-6 lg:px-10 py-6 space-y-6">

                {{-- ==================== BARIS 1: 4 KARTU STATISTIK (RAPI, MINIMALIS, STRUKTUR SERAGAM) ==================== --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

                    {{-- 1. Total Unit Rumah --}}
                    <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 sm:p-5.5 shadow-xs hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-[#475569] uppercase tracking-wider">TOTAL UNIT HUNIAN</span>
                                <svg class="w-6 h-6 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                                </svg>
                            </div>
                            <div class="mt-3.5 flex items-baseline gap-2">
                                <h3 class="text-3xl font-extrabold text-[#0F172A] tracking-tight leading-none">{{ number_format($totalUnit) }}</h3>
                                <span class="text-sm font-semibold text-[#475569]">Unit Rumah</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                            <span class="font-bold text-[#0F172A]">100% Aktif</span>
                            <span class="font-semibold text-[#475569]">Wilayah Sektor 04</span>
                        </div>
                    </div>

                    {{-- 2. Selesai Dicatat --}}
                    <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 sm:p-5.5 shadow-xs hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-[#475569] uppercase tracking-wider">SELESAI TERCATAT</span>
                                <svg class="w-6 h-6 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="mt-3.5 flex items-baseline gap-2">
                                <h3 class="text-3xl font-extrabold text-[#0F172A] tracking-tight leading-none">{{ $totalTercatat }}</h3>
                                <span class="text-sm font-semibold text-[#475569]">/ {{ $totalUnit }} Unit</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                            <span class="font-bold text-[#0F172A]">{{ $progresPersen }}% Selesai</span>
                            <span class="font-semibold text-[#475569]">Tercatat di sistem</span>
                        </div>
                    </div>

                    {{-- 3. Sisa Belum Dicatat --}}
                    <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 sm:p-5.5 shadow-xs hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-[#475569] uppercase tracking-wider">SISA PERLU PINDAI</span>
                                <svg class="w-6 h-6 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                            </div>
                            <div class="mt-3.5 flex items-baseline gap-2">
                                <h3 class="text-3xl font-extrabold text-[#0F172A] tracking-tight leading-none">{{ $totalSisa }}</h3>
                                <span class="text-sm font-semibold text-[#475569]">Sisa Unit</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                            <span class="font-bold text-[#0F172A]">Prioritas Rute</span>
                            <span class="font-semibold text-[#475569]">Perlu dikunjungi</span>
                        </div>
                    </div>

                    {{-- 4. Target Harian --}}
                    <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 sm:p-5.5 shadow-xs hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-[#475569] uppercase tracking-wider">TARGET HARI INI</span>
                                <svg class="w-6 h-6 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                            </div>
                            <div class="mt-3.5 flex items-baseline gap-2">
                                <h3 class="text-3xl font-extrabold text-[#0F172A] tracking-tight leading-none">100%</h3>
                                <span class="text-sm font-semibold text-[#475569]">Target Sektor</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                            <span class="font-bold text-[#0F172A]">Tenggat Tgl 20</span>
                            <span class="font-semibold text-[#475569]">Bulan ini</span>
                        </div>
                    </div>

                </div>

                {{-- ==================== BARIS 2 (FOTO KEDUA): PROGRES SEKTOR 04 DI TENGAH ==================== --}}
                <div class="bg-white p-5 sm:p-6 rounded-xl border border-[#E2E8F0] shadow-xs card-lift">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-base font-bold text-[#0F172A]">Progres Sektor 04</h2>
                        <span class="text-xs font-bold text-[#2563EB] bg-[#EFF6FF] px-2.5 py-0.5 rounded-md border border-[#DBEAFE]">91%</span>
                    </div>

                    {{-- Progress Bar Mulus & Tebal --}}
                    <div class="w-full bg-[#E2E8F0] rounded-full h-3 overflow-hidden mb-3.5">
                        <div class="bg-[#2563EB] h-3 rounded-full transition-all duration-500" style="width: 91%"></div>
                    </div>

                    <div class="flex items-center justify-between text-xs font-semibold text-[#475569]">
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#2563EB]"></span> 128 Tercatat</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#CBD5E1]"></span> 12 Sisa</span>
                        <span class="text-[#0F172A] font-bold">Total 140 Unit</span>
                    </div>
                </div>



                {{-- ==================== BARIS 4 (FOTO KELIMA): TABEL DATA PEMBACAAN & VERIFIKASI LAPANGAN ==================== --}}
                <div class="bg-white border border-[#E2E8F0] rounded-xl p-6 shadow-xs card-lift">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                        <div>
                            <h2 class="text-sm font-bold text-[#0F172A]">Data Pembacaan & Verifikasi Lapangan</h2>
                            <p class="text-xs text-[#475569] mt-0.5">Rekapitulasi pencatatan meteran air warga wilayah Sektor 04</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#CBD5E1] text-xs font-semibold text-[#1E293B] hover:bg-[#F8FAFC] transition-colors shadow-xs">
                                <svg class="w-3.5 h-3.5 text-[#475569]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
                                <span>Filter</span>
                            </button>
                            <a href="{{ route('petugas.riwayat') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0F172A] hover:bg-[#1E293B] text-white text-xs font-semibold transition-colors shadow-xs">
                                <span>Lihat Semua</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Table Container --}}
                    <div class="overflow-x-auto -mx-6">
                        <table class="table-modern min-w-full">
                            <thead>
                                <tr>
                                    <th class="pl-6 w-12 text-[#334155]">No</th>
                                    <th class="text-[#334155]">ID Unit</th>
                                    <th class="text-[#334155]">Nama Pelanggan</th>
                                    <th class="text-[#334155]">Nomor Meteran</th>
                                    <th class="text-[#334155]">Tanggal Rekam</th>
                                    <th class="text-[#334155]">Status</th>
                                    <th class="pr-6 text-right text-[#334155]">Stand Meter</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Baris 1: Budi Santoso (Status: Butuh Persetujuan Admin) --}}
                                <tr>
                                    <td class="pl-6 text-[#475569] font-mono text-xs font-semibold">1</td>
                                    <td class="font-semibold text-[#0F172A] font-mono text-xs">#A-12</td>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-[#EFF6FF] text-[#2563EB] font-bold text-xs flex items-center justify-center">
                                                BS
                                            </div>
                                            <span class="font-semibold text-xs text-[#0F172A]">Budi Santoso</span>
                                        </div>
                                    </td>
                                    <td class="text-xs text-[#334155] font-mono font-medium">MTR-0012-A</td>
                                    <td class="text-xs text-[#334155] font-medium">14/10/2026 08:21</td>
                                    <td>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#FFFBEB] text-[#D97706] border border-[#FDE68A]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#D97706]"></span>
                                            Butuh Persetujuan Admin
                                        </span>
                                    </td>
                                    <td class="pr-6 text-right font-semibold text-xs text-[#0F172A] font-mono">
                                        1.700 m³
                                    </td>
                                </tr>

                                {{-- Baris 2: Siti Aminah (Status: Selesai) --}}
                                <tr>
                                    <td class="pl-6 text-[#475569] font-mono text-xs font-semibold">2</td>
                                    <td class="font-semibold text-[#0F172A] font-mono text-xs">#B-07</td>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-[#F5F3FF] text-[#7C3AED] font-bold text-xs flex items-center justify-center">
                                                SA
                                            </div>
                                            <span class="font-semibold text-xs text-[#0F172A]">Siti Aminah</span>
                                        </div>
                                    </td>
                                    <td class="text-xs text-[#334155] font-mono font-medium">MTR-0045-B</td>
                                    <td class="text-xs text-[#334155] font-medium">14/10/2026 08:21</td>
                                    <td>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#ECFDF5] text-[#059669] border border-[#A7F3D0]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span>
                                            Selesai
                                        </span>
                                    </td>
                                    <td class="pr-6 text-right font-semibold text-xs text-[#0F172A] font-mono">
                                        1.728 m³
                                    </td>
                                </tr>

                                {{-- Baris 3: Andi Rahman (Status: Butuh Persetujuan Admin) --}}
                                <tr>
                                    <td class="pl-6 text-[#475569] font-mono text-xs font-semibold">3</td>
                                    <td class="font-semibold text-[#0F172A] font-mono text-xs">#C-03</td>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-[#ECFDF5] text-[#059669] font-bold text-xs flex items-center justify-center">
                                                AR
                                            </div>
                                            <span class="font-semibold text-xs text-[#0F172A]">Andi Rahman</span>
                                        </div>
                                    </td>
                                    <td class="text-xs text-[#334155] font-mono font-medium">MTR-0089-C</td>
                                    <td class="text-xs text-[#334155] font-medium">13/10/2026 09:14</td>
                                    <td>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#FFFBEB] text-[#D97706] border border-[#FDE68A]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#D97706]"></span>
                                            Butuh Persetujuan Admin
                                        </span>
                                    </td>
                                    <td class="pr-6 text-right font-semibold text-xs text-[#0F172A] font-mono">
                                        1.650 m³
                                    </td>
                                </tr>

                                {{-- Baris 4: Nur Hidayah (Status: Selesai) --}}
                                <tr>
                                    <td class="pl-6 text-[#475569] font-mono text-xs font-semibold">4</td>
                                    <td class="font-semibold text-[#0F172A] font-mono text-xs">#A-05</td>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-[#FFFBEB] text-[#D97706] font-bold text-xs flex items-center justify-center">
                                                NH
                                            </div>
                                            <span class="font-semibold text-xs text-[#0F172A]">Nur Hidayah</span>
                                        </div>
                                    </td>
                                    <td class="text-xs text-[#334155] font-mono font-medium">MTR-0005-A</td>
                                    <td class="text-xs text-[#334155] font-medium">13/10/2026 09:14</td>
                                    <td>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#ECFDF5] text-[#059669] border border-[#A7F3D0]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span>
                                            Selesai
                                        </span>
                                    </td>
                                    <td class="pr-6 text-right font-semibold text-xs text-[#0F172A] font-mono">
                                        1.520 m³
                                    </td>
                                </tr>

                                {{-- Baris 5: Ibu Siti Nurhaliza (Status: Belum Dicatat) --}}
                                <tr>
                                    <td class="pl-6 text-[#475569] font-mono text-xs font-semibold">5</td>
                                    <td class="font-semibold text-[#0F172A] font-mono text-xs">#A-03</td>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-[#F1F5F9] text-[#475569] font-bold text-xs flex items-center justify-center">
                                                SN
                                            </div>
                                            <span class="font-semibold text-xs text-[#0F172A]">Ibu Siti Nurhaliza</span>
                                        </div>
                                    </td>
                                    <td class="text-xs text-[#334155] font-mono font-medium">MTR-0003-A</td>
                                    <td class="text-xs text-[#475569] font-medium italic">Belum direkam</td>
                                    <td>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#F1F5F9] text-[#475569] border border-[#CBD5E1]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#94A3B8]"></span>
                                            Belum Dicatat
                                        </span>
                                    </td>
                                    <td class="pr-6 text-right font-semibold text-xs text-[#475569] font-mono">
                                        18.4 m³ (Lalu)
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

</body>
</html>
>>>>>>> dc048bd ( fitur lengkap)
