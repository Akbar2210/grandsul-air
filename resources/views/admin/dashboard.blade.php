<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Admin — Grand Sulawesi Air</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ===== ANIMASI GRAFIK & STATISTIK ===== */
        @keyframes donutSweepNavy {
            0% { stroke-dashoffset: 238.76; }
            100% { stroke-dashoffset: 100; }
        }
        @keyframes donutSweepBlue {
            0% { stroke-dashoffset: 238.76; }
            100% { stroke-dashoffset: 170; }
        }
        @keyframes donutSweepGray {
            0% { stroke-dashoffset: 238.76; }
            100% { stroke-dashoffset: 215; }
        }

        .animate-donut-navy {
            stroke-dasharray: 238.76;
            stroke-dashoffset: 238.76;
            animation: donutSweepNavy 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .animate-donut-blue {
            stroke-dasharray: 238.76;
            stroke-dashoffset: 238.76;
            animation: donutSweepBlue 1.0s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards;
        }
        .animate-donut-gray {
            stroke-dasharray: 238.76;
            stroke-dashoffset: 238.76;
            animation: donutSweepGray 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards;
        }

        @keyframes drawSplinePath {
            0% { stroke-dashoffset: 1000; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes splineAreaFade {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        @keyframes nodePop {
            0% { opacity: 0; transform: scale(0); }
            70% { opacity: 1; transform: scale(1.4); }
            100% { opacity: 1; transform: scale(1); }
        }
        @keyframes tooltipPop {
            0% { opacity: 0; transform: translateY(8px) scale(0.85); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .spline-line-draw {
            stroke-dasharray: 1000;
            stroke-dashoffset: 1000;
            animation: drawSplinePath 1.4s cubic-bezier(0.2, 0.8, 0.25, 1) forwards;
        }
        .spline-area-fill {
            opacity: 0;
            animation: splineAreaFade 0.8s ease-out 0.5s forwards;
        }
        .spline-node {
            transform-box: fill-box;
            transform-origin: center;
            opacity: 0;
            animation: nodePop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        .spline-tooltip {
            opacity: 0;
            animation: tooltipPop 0.45s cubic-bezier(0.16, 1, 0.3, 1) 1.35s forwards;
        }
    </style>
</head>
<body class="bg-[#EEF2F6] font-sans text-[#1E293B] antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile Overlay --}}
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
        {{-- ==================== SIDEBAR KIRI (NAVY SLEEK) ==================== --}}
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
                            <span class="text-[11px] text-[#64748B] font-medium tracking-tight mt-1 block">Pengelolaan Air</span>
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
                    <a href="{{ route('admin.dashboard') }}" class="menu-item-modern active">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.terflag') }}" class="menu-item-modern justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                            <span>Data Ter-flag</span>
                        </div>
                        <span class="w-5 h-5 rounded-full bg-[#1E293B] text-[#38BDF8] text-[10px] font-bold flex items-center justify-center border border-[#334155]">
                            {{ $totalTerflag }}
                        </span>
                    </a>

                    <a href="{{ route('admin.tagihan') }}" class="menu-item-modern">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <span>Tagihan</span>
                    </a>

                    <a href="{{ route('admin.pembayaran') }}" class="menu-item-modern">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 13.5v-3a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 10.5v3z"/></svg>
                        <span>Pembayaran</span>
                    </a>

                    <a href="{{ route('admin.riwayat-pembayaran') }}" class="menu-item-modern">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Riwayat Pembayaran</span>
                    </a>

                    <a href="{{ route('admin.laporan') }}" class="menu-item-modern">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                        <span>Laporan</span>
                    </a>

                    <a href="{{ route('admin.master') }}" class="menu-item-modern">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg>
                        <span>Data Master</span>
                    </a>

                    <a href="{{ route('admin.audit') }}" class="menu-item-modern">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                        <span>Audit Log</span>
                    </a>
                </nav>
            </div>

            {{-- Logout Button --}}
            <div class="p-4 border-t border-[#1E293B]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-[#94A3B8] hover:text-[#EF4444] hover:bg-[#1E293B] text-sm font-medium transition-colors">
                        <svg class="w-5 h-5 text-[#EF4444]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- ==================== MAIN CONTENT CANVAS (SEPERTI IMAGE 2) ==================== --}}
        <div class="flex-1 lg:pl-[250px] flex flex-col min-w-0">

            {{-- TOPBAR BERSIH, RAPI & MINIMALIS --}}
            <header class="px-6 lg:px-10 pt-7 pb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-[#E2E8F0]/80 bg-white/50 backdrop-blur-xs">
                <div class="flex items-center gap-3">
                    {{-- Hamburger Mobile --}}
                    <button @click="sidebarOpen = true" class="lg:hidden text-[#1E293B] p-2 rounded-lg hover:bg-[#F1F5F9] transition-colors border border-[#E2E8F0]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>

                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold text-[#0F172A] tracking-tight">
                            Dashboard
                        </h1>
                        <p class="text-xs text-[#64748B] mt-1 font-medium">Periode Pencatatan & Penagihan: <span class="text-[#0F172A] font-semibold">{{ $periodeStr }}</span></p>
                    </div>
                </div>

                {{-- Sisi Kanan: Notifikasi Sistem & Profil Admin --}}
                <div class="flex items-center gap-3 self-end md:self-auto">

                    {{-- DROPDOWN NOTIFIKASI SISTEM (SESUAI PRD F-11 & US-11) --}}
                    <div class="relative" x-data="{
                        open: false,
                        notifications: [
                            {
                                id: 1,
                                title: 'Data Meter Ter-flag Anomali',
                                desc: 'Unit A-12 terdeteksi anomali pemakaian (OCR conf 71%). Perlu verifikasi admin.',
                                time: '15 mnt lalu',
                                unread: true,
                                type: 'flag',
                                link: '{{ route('admin.terflag') }}'
                            },
                            {
                                id: 2,
                                title: 'Batch Tagihan Massal Selesai',
                                desc: 'Kalkulasi tarif bertingkat progresif untuk 140 unit selesai diproses di latar belakang.',
                                time: '1 jam lalu',
                                unread: true,
                                type: 'batch',
                                link: '{{ route('admin.tagihan') }}'
                            },
                            {
                                id: 3,
                                title: 'WhatsApp Pengingat Terkirim',
                                desc: 'Pemberitahuan tunggakan otomatis berhasil dikirimkan ke 41 unit warga.',
                                time: '3 jam lalu',
                                unread: false,
                                type: 'wa',
                                link: '{{ route('admin.dashboard') }}'
                            }
                        ],
                        get unreadCount() {
                            return this.notifications.filter(n => n.unread).length;
                        },
                        markAllAsRead() {
                            this.notifications.forEach(n => n.unread = false);
                        },
                        readNotif(item) {
                            item.unread = false;
                            window.location.href = item.link;
                        }
                    }">
                        {{-- Bell Icon Button (Polos Minimalis Tanpa Kotak Background) --}}
                        <button
                            @click="open = !open"
                            type="button"
                            class="relative p-2 text-[#64748B] hover:text-[#0F172A] transition-colors cursor-pointer focus:outline-none"
                            title="Pemberitahuan Sistem"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            </svg>
                            {{-- Red Indicator Dot --}}
                            <template x-if="unreadCount > 0">
                                <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#EF4444] opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-[#EF4444]"></span>
                                </span>
                            </template>
                        </button>

                        {{-- Dropdown Panel Notifikasi --}}
                        <div
                            x-show="open"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                            class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl border border-[#E2E8F0] shadow-xl z-50 overflow-hidden"
                            style="display: none;"
                        >
                            {{-- Header Notifikasi --}}
                            <div class="px-4 py-3 border-b border-[#F1F5F9] flex items-center justify-between bg-[#F8FAFC]">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-[#0F172A]">Notifikasi Sistem</h4>
                                    <template x-if="unreadCount > 0">
                                        <span class="px-1.5 py-0.5 rounded-full bg-[#EF4444] text-white text-[10px] font-bold" x-text="unreadCount + ' Baru'"></span>
                                    </template>
                                </div>
                                <button
                                    type="button"
                                    @click="markAllAsRead()"
                                    class="text-[11px] font-medium text-[#2563EB] hover:text-[#1D4ED8] hover:underline cursor-pointer"
                                    x-show="unreadCount > 0"
                                >
                                    Tandai dibaca
                                </button>
                            </div>

                            {{-- Daftar Notifikasi --}}
                            <div class="max-h-80 overflow-y-auto divide-y divide-[#F1F5F9]">
                                <template x-for="item in notifications" :key="item.id">
                                    <div
                                        @click="readNotif(item)"
                                        class="p-3.5 hover:bg-[#F8FAFC] transition-colors cursor-pointer flex items-start gap-3 relative"
                                        :class="item.unread ? 'bg-[#EFF6FF]/40' : ''"
                                    >
                                        {{-- Icon Berdasarkan Tipe Notifikasi PRD --}}
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                                             :class="{
                                                 'bg-[#FEF3C7] text-[#D97706]': item.type === 'flag',
                                                 'bg-[#EFF6FF] text-[#2563EB]': item.type === 'batch',
                                                 'bg-[#ECFDF5] text-[#059669]': item.type === 'wa'
                                             }">
                                            <template x-if="item.type === 'flag'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                            </template>
                                            <template x-if="item.type === 'batch'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </template>
                                            <template x-if="item.type === 'wa'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.84-.961l.534-1.599A8.04 8.04 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                                            </template>
                                        </div>

                                        {{-- Konten Notifikasi --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1">
                                                <h5 class="text-xs font-bold text-[#0F172A] truncate" x-text="item.title"></h5>
                                                <span class="text-[10px] text-[#94A3B8] whitespace-nowrap" x-text="item.time"></span>
                                            </div>
                                            <p class="text-[11px] text-[#64748B] mt-0.5 leading-snug line-clamp-2" x-text="item.desc"></p>
                                        </div>

                                        {{-- Unread Dot Indicator --}}
                                        <template x-if="item.unread">
                                            <span class="w-2 h-2 rounded-full bg-[#2563EB] shrink-0 mt-1.5"></span>
                                        </template>
                                    </div>
                                </template>

                                {{-- Pesan Jika Kosong (Sesuai US-11 PRD) --}}
                                <template x-if="notifications.length === 0">
                                    <div class="p-8 text-center text-[#64748B]">
                                        <svg class="w-8 h-8 mx-auto text-[#CBD5E1] mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                                        </svg>
                                        <p class="text-xs font-semibold text-[#0F172A]">Belum ada notifikasi</p>
                                        <p class="text-[11px] text-[#94A3B8] mt-0.5">Semua proses berjalan lancar</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Profil Avatar Admin Minimalis --}}
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-[#0F172A] text-white font-bold text-xs flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 2)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-[#0F172A] block leading-tight">{{ Auth::user()->name ?? 'Admin Pengelola Air' }}</span>
                                <span class="w-2 h-2 rounded-full bg-[#10B981]" title="Online"></span>
                            </div>
                            <span class="text-[10px] text-[#64748B] font-medium block">Administrator Utama</span>
                        </div>
                    </div>
                </div>
            </header>

            {{-- KONTEN UTAMA --}}
            <main class="flex-1 px-6 lg:px-10 py-6 space-y-6">

                {{-- ==================== BARIS 1: 4 KARTU STATISTIK (RAPI, MINIMALIS, STRUKTUR SERAGAM) ==================== --}}
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
                                <h3 class="text-3xl font-extrabold text-[#0F172A] tracking-tight leading-none">{{ number_format($totalUnit) }}</h3>
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
                                <h3 class="text-3xl font-extrabold text-[#0F172A] tracking-tight leading-none">{{ $totalBacaanBulanIni }}</h3>
                                <span class="text-sm font-semibold text-[#475569]">/ {{ $totalUnit }} Unit</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                            <span class="font-bold text-[#0F172A]">{{ $progresPembacaan }}% Selesai</span>
                            <span class="font-semibold text-[#475569]">{{ $totalUnit - $totalBacaanBulanIni }} Sisa Unit</span>
                        </div>
                    </div>

                    {{-- 3. Data Ter-flag (Anomali) --}}
                    <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 sm:p-5.5 shadow-xs hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-[#475569] uppercase tracking-wider">DATA TER-FLAG</span>
                                <svg class="w-6 h-6 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                            </div>
                            <div class="mt-3.5 flex items-baseline gap-2">
                                <h3 class="text-3xl font-extrabold text-[#0F172A] tracking-tight leading-none">{{ $totalTerflag }}</h3>
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
                                <h3 class="text-3xl font-extrabold text-[#0F172A] tracking-tight leading-none">{{ $tagihanBelumLunasCount }}</h3>
                                <span class="text-sm font-semibold text-[#475569]">Tagihan Warga</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                            <span class="font-bold text-[#0F172A]">Rp {{ number_format($nominalBelumLunas, 0, ',', '.') }}</span>
                            <a href="{{ route('admin.tagihan') }}" class="font-bold text-[#2563EB] hover:underline">Kelola</a>
                        </div>
                    </div>

                </div>

                {{-- ==================== BARIS 2: DONUT CHART & WATER USAGE SPLINE (PADAT, RAPI & TIDAK KOSONG) ==================== --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    {{-- KIRI (5 KOLOM): Statistik Tagihan Bulanan (Donut + Progress Detail) --}}
                    <div class="lg:col-span-5 bg-white border border-[#E2E8F0] rounded-xl p-6 shadow-xs card-lift flex flex-col justify-between">
                        <div>
                            {{-- Card Header --}}
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#F1F5F9]">
                                <h2 class="text-sm font-bold text-[#0F172A]">Statistik Tagihan Bulanan</h2>
                                <span class="text-xs font-semibold text-[#334155]">
                                    Bulan Ini
                                </span>
                            </div>

                            {{-- Donut & Structured Legends --}}
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-6 py-2">
                                {{-- Circular Donut SVG Chart dengan Animasi Putar & Draw (Tebal & Proporsional) --}}
                                <div class="relative w-44 h-44 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                        {{-- Background circle tebal --}}
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#F1F5F9" stroke-width="15"></circle>
                                        {{-- Segment 1: Lunas (Navy 58%) beranimasi tebal --}}
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#0F172A" stroke-width="15" class="animate-donut-navy" stroke-linecap="round"></circle>
                                        {{-- Segment 2: Belum Lunas (Blue 30%) beranimasi tebal --}}
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#2563EB" stroke-width="15" class="animate-donut-blue" stroke-linecap="round"></circle>
                                        {{-- Segment 3: Ter-flag (Soft Gray 12%) beranimasi tebal --}}
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#64748B" stroke-width="15" class="animate-donut-gray" stroke-linecap="round"></circle>
                                    </svg>
                                    {{-- Inside Donut Value dengan Animasi Count-Up (Pas di Tengah Tidak Menabrak Garis) --}}
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-2"
                                         x-data="{ count: 0 }"
                                         x-init="let start = 0; let end = 700; let duration = 1200; let stepTime = 16; let steps = duration / stepTime; let inc = end / steps; let timer = setInterval(() => { start += inc; if(start >= end) { count = end; clearInterval(timer); } else { count = Math.floor(start); } }, stepTime);">
                                        <span class="text-2xl sm:text-[26px] font-extrabold text-[#0F172A] tracking-tight leading-none" x-text="count">700</span>
                                        <span class="text-[10px] text-[#475569] font-bold uppercase tracking-wider mt-1 block leading-tight">Total Tagihan</span>
                                    </div>
                                </div>

                                {{-- Structured Progress Breakdown (Garis Tebal & Tidak Kosong) --}}
                                <div class="space-y-5 w-full sm:flex-1 pl-0 sm:pl-1">
                                    {{-- Row 1: Lunas --}}
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-1.5">
                                            <div class="flex items-center gap-2 font-semibold text-[#0F172A]">
                                                <span class="w-3 h-3 rounded-full bg-[#0F172A]"></span>
                                                <span class="text-sm font-semibold">Lunas</span>
                                            </div>
                                            <span class="text-sm font-bold text-[#0F172A]">407 <span class="text-xs font-semibold text-[#475569]">(58%)</span></span>
                                        </div>
                                        <div class="w-full bg-[#E2E8F0] rounded-full h-2.5 overflow-hidden">
                                            <div class="bg-[#0F172A] h-2.5 rounded-full" style="width: 58%"></div>
                                        </div>
                                    </div>

                                    {{-- Row 2: Belum Lunas --}}
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-1.5">
                                            <div class="flex items-center gap-2 font-semibold text-[#0F172A]">
                                                <span class="w-3 h-3 rounded-full bg-[#2563EB]"></span>
                                                <span class="text-sm font-semibold">Belum Lunas</span>
                                            </div>
                                            <span class="text-sm font-bold text-[#0F172A]">248 <span class="text-xs font-semibold text-[#475569]">(35%)</span></span>
                                        </div>
                                        <div class="w-full bg-[#E2E8F0] rounded-full h-2.5 overflow-hidden">
                                            <div class="bg-[#2563EB] h-2.5 rounded-full" style="width: 35%"></div>
                                        </div>
                                    </div>

                                    {{-- Row 3: Anomali --}}
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-1.5">
                                            <div class="flex items-center gap-2 font-semibold text-[#0F172A]">
                                                <span class="w-3 h-3 rounded-full bg-[#64748B]"></span>
                                                <span class="text-sm font-semibold">Perlu Review</span>
                                            </div>
                                            <span class="text-sm font-bold text-[#0F172A]">45 <span class="text-xs font-semibold text-[#475569]">(7%)</span></span>
                                        </div>
                                        <div class="w-full bg-[#E2E8F0] rounded-full h-2.5 overflow-hidden">
                                            <div class="bg-[#64748B] h-2.5 rounded-full" style="width: 7%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Footer KPI Metrics (Tanpa Box Dalam) --}}
                        <div class="mt-5 pt-4 border-t border-[#F1F5F9] grid grid-cols-2 gap-4 text-xs">
                            <div>
                                <span class="text-[11px] text-[#475569] block font-bold uppercase tracking-wider">Total Nilai Tagihan</span>
                                <span class="text-base font-bold text-[#0F172A] mt-0.5 block">Rp 34.692.000</span>
                            </div>
                            <div>
                                <span class="text-[11px] text-[#475569] block font-bold uppercase tracking-wider">Efisiensi Koleksi</span>
                                <span class="text-base font-bold text-[#0F172A] mt-0.5 block">93.9% <span class="text-xs font-semibold text-[#16A34A]">(Terverifikasi)</span></span>
                            </div>
                        </div>
                    </div>

                    {{-- KANAN (7 KOLOM): Water Analytics Line Chart (Padat, Terstruktur & Minimalis) --}}
                    <div class="lg:col-span-7 bg-white border border-[#E2E8F0] rounded-xl p-6 shadow-xs card-lift flex flex-col justify-between">
                        <div>
                            {{-- Card Header --}}
                            <div class="flex items-center justify-between mb-3 pb-3 border-b border-[#F1F5F9]">
                                <h2 class="text-sm font-bold text-[#0F172A]">Tren Pemakaian Air (m³)</h2>
                                <span class="text-xs font-semibold text-[#2563EB]">
                                    Volume Terdistribusi (+8.4%)
                                </span>
                            </div>

                            {{-- Mini KPI Strip (Bersih, Rapi Tanpa Kolom Kotak Bertingkat) --}}
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div>
                                    <span class="text-[11px] text-[#475569] block font-bold uppercase tracking-wider">Volume Bulan Ini</span>
                                    <span class="text-lg font-bold text-[#0F172A] mt-0.5 block">9,350 <span class="text-xs font-medium text-[#475569]">m³</span></span>
                                </div>
                                <div>
                                    <span class="text-[11px] text-[#475569] block font-bold uppercase tracking-wider">Rata-Rata Bulanan</span>
                                    <span class="text-lg font-bold text-[#0F172A] mt-0.5 block">6,820 <span class="text-xs font-medium text-[#475569]">m³</span></span>
                                </div>
                                <div>
                                    <span class="text-[11px] text-[#475569] block font-bold uppercase tracking-wider">Tekanan Jaringan</span>
                                    <span class="text-lg font-bold text-[#0F172A] mt-0.5 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                                        3.2 Bar (Stabil)
                                    </span>
                                </div>
                            </div>

                            {{-- Elegant Smooth Curved Line Chart (SVG Bezier Spline Beranimasi) --}}
                            <div class="relative h-48 pt-1">
                                {{-- Y-axis numbers & horizontal subtle grid lines --}}
                                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none text-[11px] text-[#334155] font-mono font-semibold">
                                    <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-1">
                                        <span class="w-7 text-right">10k</span>
                                        <div class="flex-1"></div>
                                    </div>
                                    <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-1">
                                        <span class="w-7 text-right">8k</span>
                                        <div class="flex-1"></div>
                                    </div>
                                    <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-1">
                                        <span class="w-7 text-right">6k</span>
                                        <div class="flex-1"></div>
                                    </div>
                                    <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-1">
                                        <span class="w-7 text-right">4k</span>
                                        <div class="flex-1"></div>
                                    </div>
                                    <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-1">
                                        <span class="w-7 text-right">2k</span>
                                        <div class="flex-1"></div>
                                    </div>
                                </div>

                                {{-- SVG Spline Curve Overlay dengan Animasi Meluncur --}}
                                <div class="absolute inset-0 pl-10 pr-2 pt-2 pb-6">
                                    <svg class="w-full h-full overflow-visible" viewBox="0 0 600 160" preserveAspectRatio="none">
                                        <defs>
                                            {{-- Soft blue gradient under the curve --}}
                                            <linearGradient id="curveGradient" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#2563EB" stop-opacity="0.22" />
                                                <stop offset="60%" stop-color="#3B82F6" stop-opacity="0.08" />
                                                <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0.0" />
                                            </linearGradient>
                                        </defs>

                                        {{-- Vertical Guide Dashed Lines --}}
                                        <line x1="20" y1="20" x2="20" y2="160" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3,3" />
                                        <line x1="140" y1="20" x2="140" y2="160" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3,3" />
                                        <line x1="260" y1="20" x2="260" y2="160" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3,3" />
                                        <line x1="380" y1="20" x2="380" y2="160" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3,3" />
                                        <line x1="500" y1="20" x2="500" y2="160" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3,3" />
                                        <line x1="580" y1="20" x2="580" y2="160" stroke="#2563EB" stroke-width="1.5" stroke-dasharray="3,3" opacity="0.6" />

                                        {{-- Filled Area Under Spline Beranimasi Fade In --}}
                                        <path
                                            class="spline-area-fill"
                                            d="M 20,115 C 70,105 90,75 140,75 C 190,75 200,55 260,55 C 310,55 330,95 380,95 C 430,95 450,40 500,40 C 540,40 560,20 580,20 L 580,160 L 20,160 Z"
                                            fill="url(#curveGradient)"
                                        />

                                        {{-- Main Stroke Spline Beranimasi Menggambar dari Kiri ke Kanan --}}
                                        <path
                                            class="spline-line-draw"
                                            d="M 20,115 C 70,105 90,75 140,75 C 190,75 200,55 260,55 C 310,55 330,95 380,95 C 430,95 450,40 500,40 C 540,40 560,20 580,20"
                                            fill="none"
                                            stroke="#2563EB"
                                            stroke-width="2.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />

                                        {{-- Data Nodes Beranimasi Pop-in Bergantian --}}
                                        <circle cx="20" cy="115" r="4.5" fill="#FFFFFF" stroke="#2563EB" stroke-width="2.5" class="spline-node" style="animation-delay: 0.1s;"></circle>
                                        <circle cx="140" cy="75" r="4.5" fill="#FFFFFF" stroke="#2563EB" stroke-width="2.5" class="spline-node" style="animation-delay: 0.35s;"></circle>
                                        <circle cx="260" cy="55" r="4.5" fill="#FFFFFF" stroke="#2563EB" stroke-width="2.5" class="spline-node" style="animation-delay: 0.65s;"></circle>
                                        <circle cx="380" cy="95" r="4.5" fill="#FFFFFF" stroke="#2563EB" stroke-width="2.5" class="spline-node" style="animation-delay: 0.95s;"></circle>
                                        <circle cx="500" cy="40" r="4.5" fill="#FFFFFF" stroke="#2563EB" stroke-width="2.5" class="spline-node" style="animation-delay: 1.2s;"></circle>
                                        <circle cx="580" cy="20" r="6" fill="#FFFFFF" stroke="#2563EB" stroke-width="3" class="spline-node" style="animation-delay: 1.4s;"></circle>
                                    </svg>

                                    {{-- Floating Interactive Tooltip Muncul Setelah Garis Sampai di Bulan Oktober --}}
                                    <div class="absolute right-4 top-1 -translate-y-full mb-1 spline-tooltip">
                                        <div class="bg-[#0F172A] text-white px-2.5 py-1 rounded-lg text-xs font-bold shadow-md flex items-center gap-1.5 border border-[#334155]">
                                            <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
                                            <span>9,350 m³</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- X-axis Month & Volume Labels --}}
                                <div class="absolute bottom-0 inset-x-0 pl-10 pr-2 flex justify-between text-xs font-medium">
                                    <div class="text-center">
                                        <span class="block text-xs font-bold text-[#0F172A]">Mei</span>
                                        <span class="block text-[11px] font-semibold text-[#334155]">4.8k</span>
                                    </div>
                                    <div class="text-center">
                                        <span class="block text-xs font-bold text-[#0F172A]">Jun</span>
                                        <span class="block text-[11px] font-semibold text-[#334155]">6.1k</span>
                                    </div>
                                    <div class="text-center">
                                        <span class="block text-xs font-bold text-[#0F172A]">Jul</span>
                                        <span class="block text-[11px] font-semibold text-[#334155]">7.2k</span>
                                    </div>
                                    <div class="text-center">
                                        <span class="block text-xs font-bold text-[#0F172A]">Agu</span>
                                        <span class="block text-[11px] font-semibold text-[#334155]">5.3k</span>
                                    </div>
                                    <div class="text-center">
                                        <span class="block text-xs font-bold text-[#0F172A]">Sep</span>
                                        <span class="block text-[11px] font-semibold text-[#334155]">8.4k</span>
                                    </div>
                                    <div class="text-center">
                                        <span class="block text-xs font-bold text-[#2563EB] border-b-2 border-[#2563EB] pb-0.5">Okt</span>
                                        <span class="block text-[11px] font-bold text-[#2563EB]">9.4k</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Footer Strip --}}
                        <div class="mt-5 pt-4 border-t border-[#F1F5F9] flex flex-wrap items-center justify-between text-xs text-[#334155]">
                            <div class="flex items-center gap-4">
                                <span class="flex items-center gap-1.5 font-medium"><span class="w-2 h-2 rounded-full bg-[#2563EB]"></span> Volume Terpakai</span>
                                <span class="flex items-center gap-1.5 font-medium"><span class="w-2 h-2 rounded-full bg-[#16A34A]"></span> Batas Normal (&lt;10k m³)</span>
                            </div>
                            <span class="text-xs font-medium text-[#475569]">Sensor telemetry terhubung 100%</span>
                        </div>
                    </div>

                </div>

                {{-- ==================== BARIS 3: TABEL RECENT INVOICES / DATA TERBARU (PERSIS IMAGE 2) ==================== --}}
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
                                    <th class="pr-6 text-right text-[#334155]">Total Tagihan</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Baris 1: Budi Santoso --}}
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
                                    <td class="text-xs text-[#334155] font-medium">09/11/2026 08:21</td>
                                    <td>
                                        <span class="badge-modern badge-pending">Ter-flag</span>
                                    </td>
                                    <td class="pr-6 text-right font-semibold text-xs text-[#0F172A] font-mono">
                                        Rp 215.000
                                    </td>
                                </tr>

                                {{-- Baris 2: Siti Aminah --}}
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
                                    <td class="text-xs text-[#334155] font-medium">09/11/2026 08:21</td>
                                    <td>
                                        <span class="badge-modern badge-overdue">Belum Dibayar</span>
                                    </td>
                                    <td class="pr-6 text-right font-semibold text-xs text-[#0F172A] font-mono">
                                        Rp 78.500
                                    </td>
                                </tr>

                                {{-- Baris 3: Andi Rahman --}}
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
                                    <td class="text-xs text-[#334155] font-medium">08/11/2026 09:14</td>
                                    <td>
                                        <span class="badge-modern badge-overdue">Belum Dibayar</span>
                                    </td>
                                    <td class="pr-6 text-right font-semibold text-xs text-[#0F172A] font-mono">
                                        Rp 64.000
                                    </td>
                                </tr>

                                {{-- Baris 4: Nur Hidayah --}}
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
                                    <td class="text-xs text-[#334155] font-medium">08/11/2026 09:14</td>
                                    <td>
                                        <span class="badge-modern badge-overdue">Belum Dibayar</span>
                                    </td>
                                    <td class="pr-6 text-right font-semibold text-xs text-[#0F172A] font-mono">
                                        Rp 43.000
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
