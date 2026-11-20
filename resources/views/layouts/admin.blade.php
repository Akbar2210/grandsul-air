<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin — Grand Sulawesi Air')</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }

        /* Primary CTA Button (Sharp Kotak Architecture) */
        .btn-cyan-cta {
            background-color: #2563EB;
            color: #FFFFFF;
            font-weight: 700;
            border-radius: 0px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-cyan-cta:hover:not(:disabled) {
            background-color: #1D4ED8;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            transform: translateY(-1px);
        }
        .btn-cyan-cta:active:not(:disabled) {
            transform: translateY(0);
        }
        .btn-cyan-cta:disabled {
            background-color: #CBD5E1;
            color: #64748B;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-[#F8FAFC] font-sans text-[#1E293B] antialiased" x-data="{ sidebarOpen: false }">

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
        class="fixed inset-0 bg-[#0B1120]/50 backdrop-blur-xs z-40 lg:hidden"
        style="display: none;"
    ></div>

    <div class="min-h-screen flex">
        {{-- ==================== SIDEBAR KIRI (NAVY SLEEK #0B1120) ==================== --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 w-[250px] bg-[#0B1120] flex flex-col justify-between transition-transform duration-300 ease-in-out lg:translate-x-0"
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
                    {{-- 1. Dashboard --}}
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'menu-item-modern active' : 'menu-item-modern' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    {{-- 2. Data Ter-flag --}}
                    <a href="{{ route('admin.terflag') }}" class="{{ request()->routeIs('admin.terflag*') ? 'menu-item-modern active justify-between' : 'menu-item-modern justify-between' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5"/></svg>
                            <span>Data Ter-flag</span>
                        </div>
                        <span class="w-5 h-5 rounded-none {{ request()->routeIs('admin.terflag*') ? 'bg-white text-[#2563EB]' : 'bg-[#1E293B] text-[#60A5FA] border border-[#334155]' }} text-[10px] font-mono font-bold flex items-center justify-center">
                            {{ $totalTerflagCount ?? 4 }}
                        </span>
                    </a>

                    {{-- 3. Tagihan --}}
                    <a href="{{ route('admin.tagihan') }}" class="{{ request()->routeIs('admin.tagihan*') ? 'menu-item-modern active' : 'menu-item-modern' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <span>Tagihan</span>
                    </a>

                    {{-- 3b. Surat Tunggakan (>= 4 Bulan) --}}
                    <a href="{{ route('admin.surat-tunggakan') }}" class="{{ request()->routeIs('admin.surat-tunggakan*') ? 'menu-item-modern active' : 'menu-item-modern' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                        <span>Surat Tunggakan</span>
                    </a>

                    {{-- 4. Pembayaran --}}
                    <a href="{{ route('admin.pembayaran') }}" class="{{ request()->routeIs('admin.pembayaran*') ? 'menu-item-modern active' : 'menu-item-modern' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 13.5v-3a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 10.5v3z"/></svg>
                        <span>Pembayaran</span>
                    </a>

                    {{-- 5. Riwayat Pembayaran --}}
                    <a href="{{ route('admin.riwayat-pembayaran') }}" class="{{ request()->routeIs('admin.riwayat-pembayaran*') ? 'menu-item-modern active' : 'menu-item-modern' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Riwayat Pembayaran</span>
                    </a>

                    {{-- 6. Laporan --}}
                    <a href="{{ route('admin.laporan') }}" class="{{ request()->routeIs('admin.laporan*') ? 'menu-item-modern active' : 'menu-item-modern' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                        <span>Laporan</span>
                    </a>

                    {{-- 7. Data Master --}}
                    <a href="{{ route('admin.master') }}" class="{{ request()->routeIs('admin.master*') ? 'menu-item-modern active' : 'menu-item-modern' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg>
                        <span>Data Master</span>
                    </a>

                    {{-- 8. Audit Log --}}
                    <a href="{{ route('admin.audit') }}" class="{{ request()->routeIs('admin.audit*') ? 'menu-item-modern active' : 'menu-item-modern' }}">
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

        {{-- ==================== MAIN CONTENT CANVAS ==================== --}}
        <div class="flex-1 lg:pl-[250px] flex flex-col min-w-0">

            {{-- TOPBAR BERSIH, RAPI & MINIMALIS --}}
            <header class="px-6 lg:px-10 pt-7 pb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-[#E2E8F0]/80 bg-white/80 backdrop-blur-xs">
                <div class="flex items-center gap-3">
                    {{-- Hamburger Mobile --}}
                    <button @click="sidebarOpen = true" class="lg:hidden text-[#1E293B] p-2 rounded-lg hover:bg-[#F1F5F9] transition-colors border border-[#E2E8F0]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>

                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold text-[#0F172A] tracking-tight">
                            @yield('page-title', 'Dashboard')
                        </h1>
                        <p class="text-xs text-[#64748B] mt-1 font-medium">@yield('page-subtitle', 'Periode Pencatatan & Penagihan: November 2026')</p>
                    </div>
                </div>

                {{-- Sisi Kanan: Action Buttons, Notifikasi, & Avatar Admin --}}
                <div class="flex items-center gap-3 sm:gap-4 self-end md:self-auto">
                    @yield('page-actions')

                    {{-- DROPDOWN NOTIFIKASI SISTEM --}}
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
                                title: 'Surat Penyampaian Otomatis Siap',
                                desc: 'Draf surat resmi PT. Arista Jaya diterbitkan otomatis untuk unit dengan tunggakan ≥ 4 bulan.',
                                time: '3 jam lalu',
                                unread: false,
                                type: 'wa',
                                link: '{{ route('admin.surat-tunggakan') }}'
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
                        <button
                            @click="open = !open"
                            type="button"
                            class="relative p-2 text-[#64748B] hover:text-[#0F172A] transition-colors cursor-pointer focus:outline-none"
                            title="Pemberitahuan Sistem"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            </svg>
                            <template x-if="unreadCount > 0">
                                <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#EF4444] opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-[#EF4444]"></span>
                                </span>
                            </template>
                        </button>

                        <div
                            x-show="open"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                            class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-lg border border-[#CBD5E1] shadow-lg z-50 overflow-hidden"
                            style="display: none;"
                        >
                            <div class="px-4 py-3 border-b border-[#E2E8F0] flex items-center justify-between bg-[#F8FAFC]">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-[#0F172A]">Notifikasi Sistem</h4>
                                    <template x-if="unreadCount > 0">
                                        <span class="px-1.5 py-0.5 rounded-md bg-[#0F172A] text-white text-[10px] font-mono font-bold" x-text="unreadCount + ' Baru'"></span>
                                    </template>
                                </div>
                                <button
                                    type="button"
                                    @click="markAllAsRead()"
                                    class="text-[11px] font-semibold text-[#0F172A] hover:underline cursor-pointer"
                                    x-show="unreadCount > 0"
                                >
                                    Tandai dibaca
                                </button>
                            </div>

                            <div class="max-h-80 overflow-y-auto divide-y divide-[#E2E8F0]">
                                <template x-for="item in notifications" :key="item.id">
                                    <div
                                        @click="readNotif(item)"
                                        class="p-3.5 hover:bg-[#F8FAFC] transition-colors cursor-pointer flex items-start gap-3 relative"
                                        :class="item.unread ? 'bg-[#F8FAFC]/80' : ''"
                                    >
                                        <div class="w-7 h-7 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] flex items-center justify-center shrink-0 mt-0.5">
                                            <template x-if="item.type === 'flag'">
                                                <svg class="w-3.5 h-3.5 text-[#0F172A]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5"/></svg>
                                            </template>
                                            <template x-if="item.type === 'batch'">
                                                <svg class="w-3.5 h-3.5 text-[#0F172A]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"/></svg>
                                            </template>
                                            <template x-if="item.type === 'wa'">
                                                <svg class="w-3.5 h-3.5 text-[#0F172A]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                                            </template>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1">
                                                <h5 class="text-xs font-bold text-[#0F172A] truncate" x-text="item.title"></h5>
                                                <span class="text-[10px] font-mono text-[#64748B] whitespace-nowrap" x-text="item.time"></span>
                                            </div>
                                            <p class="text-[11px] text-[#475569] mt-0.5 leading-snug line-clamp-2" x-text="item.desc"></p>
                                        </div>

                                        <template x-if="item.unread">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#0F172A] shrink-0 mt-1.5"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Profil Avatar Admin --}}
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-[#0F172A] text-white font-mono font-bold text-xs flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr(Auth::user()->name ?? 'AD', 0, 2)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-[#0F172A] block leading-tight">{{ Auth::user()->name ?? 'Admin Pengelola Air' }}</span>
                                <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                            </div>
                            <span class="text-[10px] text-[#64748B] font-medium block">Administrator Utama</span>
                        </div>
                    </div>
                </div>
            </header>

            {{-- KONTEN HALAMAN --}}
            <main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8">

                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="mb-6 px-4 py-3 bg-white border border-[#E2E8F0] border-l-4 border-l-[#10B981] rounded-lg text-sm flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5 text-[#0F172A] font-medium">
                        <svg class="w-5 h-5 text-[#10B981] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <span class="text-xs px-2.5 py-0.5 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] font-semibold">Berhasil</span>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 px-4 py-3 bg-white border border-[#E2E8F0] border-l-4 border-l-[#EF4444] rounded-lg text-sm flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5 text-[#0F172A] font-medium">
                        <svg class="w-5 h-5 text-[#EF4444] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <span class="text-xs px-2.5 py-0.5 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] font-semibold">Gagal</span>
                </div>
                @endif

                @yield('content')

            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
