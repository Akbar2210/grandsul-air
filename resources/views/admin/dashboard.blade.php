@extends('layouts.admin')

@section('title', 'Dashboard Admin — Grand Sulawesi Air')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Periode Pencatatan & Penagihan: ' . $periodeStr)

@section('content')
<div class="space-y-6">

    {{-- ==================== BARIS 1: 4 KARTU STATISTIK (BENTUK ASLI DIPERTAHANKAN) ==================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- 1. Total Unit --}}
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 sm:p-5.5 shadow-xs hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#475569] uppercase tracking-wider">TOTAL UNIT HUNIAN</span>
                    <svg class="w-6 h-6 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                    </svg>
                </div>
                <div class="mt-3.5 flex items-baseline gap-2">
                    <h3 class="text-3xl font-extrabold text-[#0F172A] font-mono tabular-nums tracking-tight leading-none">{{ number_format($totalUnit) }}</h3>
                    <span class="text-sm font-semibold text-[#475569]">Unit Rumah</span>
                </div>
            </div>
            <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                <span class="font-bold text-[#0F172A]">100% Aktif</span>
                <span class="font-semibold text-[#475569]">Semua terpasang</span>
            </div>
        </div>

        {{-- 2. Pembacaan Masuk --}}
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 sm:p-5.5 shadow-xs hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#475569] uppercase tracking-wider">PEMBACAAN MASUK</span>
                    <svg class="w-6 h-6 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <div class="mt-3.5 flex items-baseline gap-2">
                    <h3 class="text-3xl font-extrabold text-[#0F172A] font-mono tabular-nums tracking-tight leading-none">{{ $totalBacaanBulanIni }}</h3>
                    <span class="text-sm font-semibold text-[#475569] font-mono tabular-nums">/ {{ $totalUnit }} Unit</span>
                </div>
            </div>
            <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                <span class="font-bold text-[#0F172A] font-mono tabular-nums">{{ $progresPembacaan }}% Selesai</span>
                <span class="font-semibold text-[#475569] font-mono tabular-nums">{{ $totalUnit - $totalBacaanBulanIni }} Sisa Unit</span>
            </div>
        </div>

        {{-- 3. Data Ter-flag (Anomali) --}}
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 sm:p-5.5 shadow-xs hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#475569] uppercase tracking-wider">DATA TER-FLAG</span>
                    <svg class="w-5 h-5 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5"/>
                    </svg>
                </div>
                <div class="mt-3.5 flex items-baseline gap-2">
                    <h3 class="text-3xl font-extrabold text-[#0F172A] font-mono tabular-nums tracking-tight leading-none">{{ $totalTerflag }}</h3>
                    <span class="text-sm font-semibold text-[#475569]">Unit Anomali</span>
                </div>
            </div>
            <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                <span class="font-bold text-[#0F172A]">Perlu Ditinjau</span>
                <a href="{{ route('admin.terflag') }}" class="font-bold text-[#2563EB] hover:underline">Tinjau</a>
            </div>
        </div>

        {{-- 4. Tagihan Belum Lunas --}}
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 sm:p-5.5 shadow-xs hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#475569] uppercase tracking-wider">TAGIHAN TERTUNDA</span>
                    <svg class="w-6 h-6 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                    </svg>
                </div>
                <div class="mt-3.5 flex items-baseline gap-2">
                    <h3 class="text-3xl font-extrabold text-[#0F172A] font-mono tabular-nums tracking-tight leading-none">{{ $tagihanBelumLunasCount }}</h3>
                    <span class="text-sm font-semibold text-[#475569]">Tagihan Warga</span>
                </div>
            </div>
            <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                <span class="font-bold text-[#0F172A] font-mono tabular-nums">Rp {{ number_format($nominalBelumLunas, 0, ',', '.') }}</span>
                <a href="{{ route('admin.tagihan') }}" class="font-bold text-[#2563EB] hover:underline">Kelola</a>
            </div>
        </div>

    </div>

    {{-- ==================== BARIS 2: DONUT CHART & WATER USAGE SPLINE (BENTUK ASLI DIPERTAHANKAN) ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- KIRI (5 KOLOM): Statistik Tagihan Bulanan (Donut + Progress Detail) --}}
        <div class="lg:col-span-5 bg-white border border-[#E2E8F0] rounded-xl p-6 shadow-xs card-lift flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#F1F5F9]">
                    <h2 class="text-sm font-bold text-[#0F172A]">Statistik Tagihan Bulanan</h2>
                    <span class="text-xs font-semibold text-[#334155]">Bulan Ini</span>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-6 py-2">
                    <div class="relative w-44 h-44 flex items-center justify-center flex-shrink-0">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="38" fill="transparent" stroke="#F1F5F9" stroke-width="15"></circle>
                            <circle cx="50" cy="50" r="38" fill="transparent" stroke="#0F172A" stroke-width="15" class="animate-donut-navy" stroke-linecap="round"></circle>
                            <circle cx="50" cy="50" r="38" fill="transparent" stroke="#2563EB" stroke-width="15" class="animate-donut-blue" stroke-linecap="round"></circle>
                            <circle cx="50" cy="50" r="38" fill="transparent" stroke="#64748B" stroke-width="15" class="animate-donut-gray" stroke-linecap="round"></circle>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-2"
                             x-data="{ count: 0 }"
                             x-init="let start = 0; let end = 700; let duration = 1200; let stepTime = 16; let steps = duration / stepTime; let inc = end / steps; let timer = setInterval(() => { start += inc; if(start >= end) { count = end; clearInterval(timer); } else { count = Math.floor(start); } }, stepTime);">
                            <span class="text-2xl sm:text-[26px] font-extrabold text-[#0F172A] font-mono tabular-nums tracking-tight leading-none" x-text="count">700</span>
                            <span class="text-[10px] text-[#475569] font-bold uppercase tracking-wider mt-1 block leading-tight">Total Tagihan</span>
                        </div>
                    </div>

                    <div class="space-y-5 w-full sm:flex-1 pl-0 sm:pl-1">
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <div class="flex items-center gap-2 font-semibold text-[#0F172A]">
                                    <span class="w-3 h-3 rounded-none bg-[#0F172A]"></span>
                                    <span class="text-sm font-semibold">Lunas</span>
                                </div>
                                <span class="text-sm font-bold text-[#0F172A] font-mono tabular-nums">407 <span class="text-xs font-semibold text-[#475569]">(58%)</span></span>
                            </div>
                            <div class="w-full bg-[#E2E8F0] rounded-none h-2.5 overflow-hidden">
                                <div class="bg-[#0F172A] h-2.5 rounded-none" style="width: 58%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <div class="flex items-center gap-2 font-semibold text-[#0F172A]">
                                    <span class="w-3 h-3 rounded-none bg-[#2563EB]"></span>
                                    <span class="text-sm font-semibold">Belum Lunas</span>
                                </div>
                                <span class="text-sm font-bold text-[#0F172A] font-mono tabular-nums">248 <span class="text-xs font-semibold text-[#475569]">(35%)</span></span>
                            </div>
                            <div class="w-full bg-[#E2E8F0] rounded-none h-2.5 overflow-hidden">
                                <div class="bg-[#2563EB] h-2.5 rounded-none" style="width: 35%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <div class="flex items-center gap-2 font-semibold text-[#0F172A]">
                                    <span class="w-3 h-3 rounded-none bg-[#64748B]"></span>
                                    <span class="text-sm font-semibold">Perlu Review</span>
                                </div>
                                <span class="text-sm font-bold text-[#0F172A] font-mono tabular-nums">45 <span class="text-xs font-semibold text-[#475569]">(7%)</span></span>
                            </div>
                            <div class="w-full bg-[#E2E8F0] rounded-none h-2.5 overflow-hidden">
                                <div class="bg-[#64748B] h-2.5 rounded-none" style="width: 7%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-[#F1F5F9] grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-[11px] text-[#475569] block font-bold uppercase tracking-wider">Total Nilai Tagihan</span>
                    <span class="text-base font-bold text-[#0F172A] font-mono tabular-nums mt-0.5 block">Rp 34.692.000</span>
                </div>
                <div>
                    <span class="text-[11px] text-[#475569] block font-bold uppercase tracking-wider">Efisiensi Koleksi</span>
                    <span class="text-base font-bold text-[#0F172A] font-mono tabular-nums mt-0.5 block">93.9% <span class="text-xs font-sans font-semibold text-[#16A34A]">(Terverifikasi)</span></span>
                </div>
            </div>
        </div>

        {{-- KANAN (7 KOLOM): Water Analytics Line Chart --}}
        <div class="lg:col-span-7 bg-white border border-[#E2E8F0] rounded-xl p-6 shadow-xs card-lift flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-[#F1F5F9]">
                    <h2 class="text-sm font-bold text-[#0F172A]">Tren Pemakaian Air (m³)</h2>
                    <span class="text-xs font-semibold text-[#2563EB] font-mono tabular-nums">
                        Volume Terdistribusi (+8.4%)
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <span class="text-[11px] text-[#475569] block font-bold uppercase tracking-wider">Volume Bulan Ini</span>
                        <span class="text-lg font-bold text-[#0F172A] font-mono tabular-nums mt-0.5 block">9,350 <span class="text-xs font-medium text-[#475569]">m³</span></span>
                    </div>
                    <div>
                        <span class="text-[11px] text-[#475569] block font-bold uppercase tracking-wider">Rata-Rata Bulanan</span>
                        <span class="text-lg font-bold text-[#0F172A] font-mono tabular-nums mt-0.5 block">6,820 <span class="text-xs font-medium text-[#475569]">m³</span></span>
                    </div>
                    <div>
                        <span class="text-[11px] text-[#475569] block font-bold uppercase tracking-wider">Tekanan Jaringan</span>
                        <span class="text-lg font-bold text-[#0F172A] font-mono tabular-nums mt-0.5 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                            3.2 Bar (Stabil)
                        </span>
                    </div>
                </div>

                <div class="relative h-48 pt-1">
                    <div class="absolute inset-0 flex flex-col justify-between pointer-events-none text-[11px] text-[#334155] font-mono font-semibold tabular-nums">
                        <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-1"><span class="w-7 text-right">10k</span><div class="flex-1"></div></div>
                        <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-1"><span class="w-7 text-right">8k</span><div class="flex-1"></div></div>
                        <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-1"><span class="w-7 text-right">6k</span><div class="flex-1"></div></div>
                        <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-1"><span class="w-7 text-right">4k</span><div class="flex-1"></div></div>
                        <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-1"><span class="w-7 text-right">2k</span><div class="flex-1"></div></div>
                    </div>

                    <div class="absolute inset-0 pl-10 pr-2 pt-2 pb-6">
                        <svg class="w-full h-full overflow-visible" viewBox="0 0 600 160" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="curveGradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#2563EB" stop-opacity="0.22" />
                                    <stop offset="60%" stop-color="#3B82F6" stop-opacity="0.08" />
                                    <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>

                            <line x1="20" y1="20" x2="20" y2="160" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3,3" />
                            <line x1="140" y1="20" x2="140" y2="160" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3,3" />
                            <line x1="260" y1="20" x2="260" y2="160" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3,3" />
                            <line x1="380" y1="20" x2="380" y2="160" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3,3" />
                            <line x1="500" y1="20" x2="500" y2="160" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3,3" />
                            <line x1="580" y1="20" x2="580" y2="160" stroke="#2563EB" stroke-width="1.5" stroke-dasharray="3,3" opacity="0.6" />

                            <path
                                class="spline-area-fill"
                                d="M 20,115 C 70,105 90,75 140,75 C 190,75 200,55 260,55 C 310,55 330,95 380,95 C 430,95 450,40 500,40 C 540,40 560,20 580,20 L 580,160 L 20,160 Z"
                                fill="url(#curveGradient)"
                            />

                            <path
                                class="spline-line-draw"
                                d="M 20,115 C 70,105 90,75 140,75 C 190,75 200,55 260,55 C 310,55 330,95 380,95 C 430,95 450,40 500,40 C 540,40 560,20 580,20"
                                fill="none"
                                stroke="#2563EB"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <circle cx="20" cy="115" r="4.5" fill="#FFFFFF" stroke="#2563EB" stroke-width="2.5" class="spline-node" style="animation-delay: 0.1s;"></circle>
                            <circle cx="140" cy="75" r="4.5" fill="#FFFFFF" stroke="#2563EB" stroke-width="2.5" class="spline-node" style="animation-delay: 0.35s;"></circle>
                            <circle cx="260" cy="55" r="4.5" fill="#FFFFFF" stroke="#2563EB" stroke-width="2.5" class="spline-node" style="animation-delay: 0.65s;"></circle>
                            <circle cx="380" cy="95" r="4.5" fill="#FFFFFF" stroke="#2563EB" stroke-width="2.5" class="spline-node" style="animation-delay: 0.95s;"></circle>
                            <circle cx="500" cy="40" r="4.5" fill="#FFFFFF" stroke="#2563EB" stroke-width="2.5" class="spline-node" style="animation-delay: 1.2s;"></circle>
                            <circle cx="580" cy="20" r="6" fill="#FFFFFF" stroke="#2563EB" stroke-width="3" class="spline-node" style="animation-delay: 1.4s;"></circle>
                        </svg>

                        <div class="absolute right-4 top-1 -translate-y-full mb-1 spline-tooltip">
                            <div class="bg-[#0F172A] text-white px-2.5 py-1 rounded-lg text-xs font-mono font-bold shadow-md flex items-center gap-1.5 border border-[#334155] tabular-nums">
                                <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
                                <span>9,350 m³</span>
                            </div>
                        </div>
                    </div>

                    <div class="absolute bottom-0 inset-x-0 pl-10 pr-2 flex justify-between text-xs font-medium">
                        <div class="text-center"><span class="block text-xs font-bold text-[#0F172A]">Mei</span><span class="block text-[11px] font-mono font-semibold text-[#334155]">4.8k</span></div>
                        <div class="text-center"><span class="block text-xs font-bold text-[#0F172A]">Jun</span><span class="block text-[11px] font-mono font-semibold text-[#334155]">6.1k</span></div>
                        <div class="text-center"><span class="block text-xs font-bold text-[#0F172A]">Jul</span><span class="block text-[11px] font-mono font-semibold text-[#334155]">7.2k</span></div>
                        <div class="text-center"><span class="block text-xs font-bold text-[#0F172A]">Agu</span><span class="block text-[11px] font-mono font-semibold text-[#334155]">5.3k</span></div>
                        <div class="text-center"><span class="block text-xs font-bold text-[#0F172A]">Sep</span><span class="block text-[11px] font-mono font-semibold text-[#334155]">8.4k</span></div>
                        <div class="text-center"><span class="block text-xs font-bold text-[#2563EB] border-b-2 border-[#2563EB] pb-0.5">Okt</span><span class="block text-[11px] font-mono font-bold text-[#2563EB]">9.4k</span></div>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-[#F1F5F9] flex flex-wrap items-center justify-between text-xs text-[#334155]">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1.5 font-medium"><span class="w-2 h-2 rounded-full bg-[#2563EB]"></span> Volume Terpakai</span>
                    <span class="flex items-center gap-1.5 font-medium"><span class="w-2 h-2 rounded-full bg-[#16A34A]"></span> Batas Normal (&lt;10k m³)</span>
                </div>
                <span class="text-xs font-medium text-[#475569]">Sensor telemetry terhubung 100%</span>
            </div>
        </div>

    </div>

    {{-- ==================== BARIS 3: TABEL RECENT INVOICES / DATA TERBARU (BENTUK ASLI DIPERTAHANKAN) ==================== --}}
    <div class="bg-white border border-[#E2E8F0] rounded-xl p-6 shadow-xs card-lift">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="text-sm font-bold text-[#0F172A]">Data Pembacaan & Tagihan Terbaru</h2>
            </div>
            <div class="flex items-center gap-2">
                <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#CBD5E1] text-xs font-semibold text-[#1E293B] hover:bg-[#F8FAFC] transition-colors shadow-xs">
                    <svg class="w-3.5 h-3.5 text-[#475569]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
                    <span>Filter</span>
                </button>
                <a href="{{ route('admin.tagihan') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0F172A] hover:bg-[#1E293B] text-white text-xs font-semibold transition-colors shadow-xs">
                    <span>Lihat Semua</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>
        </div>

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
                        <th class="pr-6 text-right text-[#334155]">Total Tagihan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="pl-6 text-[#475569] font-mono text-xs font-semibold tabular-nums">1</td>
                        <td class="font-semibold text-[#0F172A] font-mono text-xs tabular-nums">#A-12</td>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] font-bold text-xs flex items-center justify-center">BS</div>
                                <span class="font-semibold text-xs text-[#0F172A]">Budi Santoso</span>
                            </div>
                        </td>
                        <td class="text-xs text-[#334155] font-mono font-medium tabular-nums">MTR-0012-A</td>
                        <td class="text-xs text-[#334155] font-mono font-medium tabular-nums">09/11/2026 08:21</td>
                        <td><span class="badge-modern badge-pending">Ter-flag</span></td>
                        <td class="pr-6 text-right font-semibold text-xs text-[#0F172A] font-mono tabular-nums">Rp 215.000</td>
                    </tr>

                    <tr>
                        <td class="pl-6 text-[#475569] font-mono text-xs font-semibold tabular-nums">2</td>
                        <td class="font-semibold text-[#0F172A] font-mono text-xs tabular-nums">#B-07</td>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] font-bold text-xs flex items-center justify-center">SA</div>
                                <span class="font-semibold text-xs text-[#0F172A]">Siti Aminah</span>
                            </div>
                        </td>
                        <td class="text-xs text-[#334155] font-mono font-medium tabular-nums">MTR-0045-B</td>
                        <td class="text-xs text-[#334155] font-mono font-medium tabular-nums">09/11/2026 08:21</td>
                        <td><span class="badge-modern badge-overdue">Belum Dibayar</span></td>
                        <td class="pr-6 text-right font-semibold text-xs text-[#0F172A] font-mono tabular-nums">Rp 78.500</td>
                    </tr>

                    <tr>
                        <td class="pl-6 text-[#475569] font-mono text-xs font-semibold tabular-nums">3</td>
                        <td class="font-semibold text-[#0F172A] font-mono text-xs tabular-nums">#C-03</td>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] font-bold text-xs flex items-center justify-center">AR</div>
                                <span class="font-semibold text-xs text-[#0F172A]">Andi Rahman</span>
                            </div>
                        </td>
                        <td class="text-xs text-[#334155] font-mono font-medium tabular-nums">MTR-0089-C</td>
                        <td class="text-xs text-[#334155] font-mono font-medium tabular-nums">08/11/2026 09:14</td>
                        <td><span class="badge-modern badge-overdue">Belum Dibayar</span></td>
                        <td class="pr-6 text-right font-semibold text-xs text-[#0F172A] font-mono tabular-nums">Rp 64.000</td>
                    </tr>

                    <tr>
                        <td class="pl-6 text-[#475569] font-mono text-xs font-semibold tabular-nums">4</td>
                        <td class="font-semibold text-[#0F172A] font-mono text-xs tabular-nums">#A-05</td>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] font-bold text-xs flex items-center justify-center">NH</div>
                                <span class="font-semibold text-xs text-[#0F172A]">Nur Hidayah</span>
                            </div>
                        </td>
                        <td class="text-xs text-[#334155] font-mono font-medium tabular-nums">MTR-0005-A</td>
                        <td class="text-xs text-[#334155] font-mono font-medium tabular-nums">08/11/2026 09:14</td>
                        <td><span class="badge-modern badge-overdue">Belum Dibayar</span></td>
                        <td class="pr-6 text-right font-semibold text-xs text-[#0F172A] font-mono tabular-nums">Rp 43.000</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
