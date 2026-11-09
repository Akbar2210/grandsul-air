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
        /* Modern table styling & typography per desain.md */
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }

        /* Primary Cyan CTA Button */
        .btn-cyan-cta {
            background-color: #00F2FE;
            color: #0B132B;
            font-weight: 700;
            border-radius: 12px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-cyan-cta:hover:not(:disabled) {
            background-color: #00C4CE;
            box-shadow: 0 0 16px rgba(0, 242, 254, 0.35);
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
        {{-- ==================== SIDEBAR KIRI (NAVY SLEEK #0F172A) ==================== --}}
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
                    {{-- 1. Dashboard --}}
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'menu-item-modern active' : 'menu-item-modern' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    {{-- 2. Data Ter-flag --}}
                    <a href="{{ route('admin.terflag') }}" class="{{ request()->routeIs('admin.terflag*') ? 'menu-item-modern active justify-between' : 'menu-item-modern justify-between' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                            <span>Data Ter-flag</span>
                        </div>
                        <span class="w-5 h-5 rounded-full {{ request()->routeIs('admin.terflag*') ? 'bg-white text-[#2563EB]' : 'bg-[#1E293B] text-[#38BDF8] border border-[#334155]' }} text-[10px] font-bold flex items-center justify-center">
                            {{ $totalTerflagCount ?? 4 }}
                        </span>
                    </a>

                    {{-- 3. Tagihan --}}
                    <a href="{{ route('admin.tagihan') }}" class="{{ request()->routeIs('admin.tagihan*') ? 'menu-item-modern active' : 'menu-item-modern' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <span>Tagihan</span>
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
            <header class="px-6 lg:px-10 pt-7 pb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-[#E2E8F0]/80 bg-white/50 backdrop-blur-xs">
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

                    {{-- Profil Avatar Admin --}}
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-[#0F172A] text-white font-bold text-xs flex items-center justify-center shadow-xs">
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
                <div class="mb-6 px-4 py-3 bg-[#ECFDF5] border border-[#A7F3D0] rounded-xl text-sm flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5 text-[#065F46] font-medium">
                        <svg class="w-5 h-5 text-[#059669] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-[#BBF7D0] text-[#166534] font-bold">Berhasil</span>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 px-4 py-3 bg-[#FEF2F2] border border-[#FECACA] rounded-xl text-sm flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5 text-[#991B1B] font-medium">
                        <svg class="w-5 h-5 text-[#DC2626] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-[#FECACA] text-[#991B1B] font-bold">Gagal</span>
                </div>
                @endif

                @yield('content')

            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

