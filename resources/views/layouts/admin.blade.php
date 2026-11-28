<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pengelolaan Air Terintegrasi Grand Sulawesi')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">

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

        /* Primary CTA Button */
        .btn-cyan-cta {
            background-color: #2563EB;
            color: #FFFFFF;
            font-weight: 400;
            border-radius: 10px;
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
    <script>
        function notificationDropdown(initial) {
            return {
                open: false,
                notifications: initial || [],
                pendingSound: false,
                get unreadCount() {
                    return this.notifications.filter(function(n) { return !!n.unread; }).length;
                },
                get hasUnread() {
                    return this.unreadCount > 0;
                },
                init() {
                    const initialIds = this.notifications.map(function(n) { return n.id; });
                    const lastKnownMaxId = parseInt(sessionStorage.getItem('last_notif_id') || '0', 10);
                    const currentMaxId = initialIds.length > 0 ? Math.max.apply(null, initialIds) : 0;

                    if (currentMaxId > lastKnownMaxId && this.unreadCount > 0) {
                        sessionStorage.setItem('last_notif_id', currentMaxId.toString());
                        setTimeout(() => {
                            this.playNotificationSound();
                        }, 400);
                    } else if (currentMaxId > 0 && !sessionStorage.getItem('last_notif_id')) {
                        sessionStorage.setItem('last_notif_id', currentMaxId.toString());
                    }

                    // Unlock audio jika browser membatasi autoplay sampai user berinteraksi
                    const unlockAudio = () => {
                        if (this.pendingSound) {
                            this.pendingSound = false;
                            this.playNotificationSound();
                        }
                    };
                    window.addEventListener('click', unlockAudio, { once: true, passive: true });
                    window.addEventListener('keydown', unlockAudio, { once: true, passive: true });

                    // Polling background setiap 15 detik untuk deteksi notifikasi baru secara real-time
                    setInterval(() => {
                        this.checkNewNotifications();
                    }, 15000);
                },
                playNotificationSound() {
                    try {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        if (!AudioContext) return;
                        if (!window._appAudioCtx) {
                            window._appAudioCtx = new AudioContext();
                        }
                        const ctx = window._appAudioCtx;
                        if (ctx.state === 'suspended') {
                            ctx.resume().catch(() => {
                                this.pendingSound = true;
                            });
                        }

                        const now = ctx.currentTime;
                        const playTone = (freq, start, duration, vol) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(freq, now + start);

                            gain.gain.setValueAtTime(0.0001, now + start);
                            gain.gain.exponentialRampToValueAtTime(vol, now + start + 0.02);
                            gain.gain.exponentialRampToValueAtTime(0.0001, now + start + duration);

                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start(now + start);
                            osc.stop(now + start + duration);
                        };

                        // Nada lonceng jernih & harmonis (G5 784Hz -> C6 1046.5Hz)
                        playTone(783.99, 0.0, 0.35, 0.15);
                        playTone(1046.50, 0.12, 0.45, 0.18);
                    } catch (e) {
                        this.pendingSound = true;
                    }
                },
                async checkNewNotifications() {
                    try {
                        const res = await fetch('{{ route('admin.notifikasi.unread-list') }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) return;
                        const data = await res.json();
                        if (data && Array.isArray(data.notifications)) {
                            const knownIds = this.notifications.map(function(n) { return n.id; });
                            const hasNew = data.notifications.some(function(n) { return !knownIds.includes(n.id); });
                            if (hasNew && data.notifications.length > 0) {
                                this.playNotificationSound();
                                const maxId = Math.max.apply(null, data.notifications.map(function(n) { return n.id; }));
                                sessionStorage.setItem('last_notif_id', maxId.toString());
                                if (this.open) {
                                    const newItems = data.notifications.filter(function(n) { return !knownIds.includes(n.id); });
                                    this.notifications = newItems.concat(this.notifications);
                                }
                            }
                            if (!this.open) {
                                this.notifications = data.notifications;
                            }
                        }
                    } catch (e) {}
                },
                toggleDropdown() {
                    if (this.open) {
                        this.closeDropdown();
                    } else {
                        this.open = true;
                    }
                },
                closeDropdown() {
                    this.open = false;
                    this.notifications = this.notifications.filter(function(n) { return n.unread; });
                },
                async markAllAsRead() {
                    this.notifications.forEach(function(n) { n.unread = false; });
                    try {
                        await fetch('{{ route('admin.notifikasi.read-all') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });
                    } catch (e) {
                        console.error(e);
                    }
                },
                async readNotif(item) {
                    item.unread = false;
                    try {
                        await fetch('/admin/notifikasi/' + item.id + '/read', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });
                    } catch (e) {}
                    if (item.link) {
                        window.location.href = item.link;
                    }
                }
            };
        }
    </script>
    @stack('styles')
</head>
<body class="bg-[#F1F5F9] font-sans text-[#1E293B] antialiased" x-data="{ sidebarOpen: false, profileModal: {{ old('_profile_modal') && $errors->any() ? 'true' : 'false' }} }">

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
        class="fixed inset-0 bg-[#0B1E3D]/50 z-40 lg:hidden"
        style="display: none;"
    ></div>

    <div class="min-h-screen flex">
        {{-- ==================== SIDEBAR KIRI (BIRU NAVY #0B1E3D) ==================== --}}
        <aside
            id="app-sidebar"
            class="fixed inset-y-0 left-0 z-50 w-[250px] bg-[#0B1E3D] border-r border-[#18325B] flex flex-col justify-between transition-transform duration-300 ease-in-out lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div>
                {{-- Brand Header --}}
                <div class="h-20 px-3 flex items-center justify-center relative border-b border-[#18325B]">
                    <div class="flex items-center gap-3">
                        <div class="w-[58px] h-[58px] flex items-center justify-center shrink-0">
                            <x-brand-logo class="w-full h-full object-contain scale-105" />
                        </div>
                        <div class="text-left">
                            <span class="text-[12.5px] font-extrabold text-white tracking-wider uppercase block leading-tight whitespace-nowrap">GRAND SULAWESI</span>
                            <span class="text-[10px] text-[#93C5FD] font-semibold tracking-wide block mt-1 whitespace-nowrap">Pengelolaan Air</span>
                        </div>
                    </div>
                    {{-- Mobile close --}}
                    <button @click="sidebarOpen = false" class="lg:hidden absolute right-3 text-[#94A3B8] hover:text-white p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Section Label --}}
                <div class="px-6 pt-6 pb-2 text-[11px] font-bold text-[#94A3B8] uppercase tracking-wider">
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
                        <span class="w-5 h-5 rounded-md {{ request()->routeIs('admin.terflag*') ? 'bg-white text-[#2563EB]' : 'bg-[#142A50] text-[#93C5FD] border border-[#1E3A8A]' }} text-[10px] font-mono font-bold flex items-center justify-center">
                            {{ $totalTerflagCount ?? 0 }}
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
            <div class="p-4 border-t border-[#18325B]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-[#94A3B8] hover:text-[#EF4444] hover:bg-[#142A50] text-sm font-medium transition-colors">
                        <svg class="w-5 h-5 text-[#EF4444]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- ==================== MAIN CONTENT CANVAS ==================== --}}
        <div class="flex-1 lg:pl-[250px] flex flex-col min-w-0 bg-[#F1F5F9]">

            {{-- TOPBAR BERSIH, RAPI & MINIMALIS --}}
            <header id="app-topbar" class="px-6 lg:px-10 py-3.5 sm:py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-[#CBD5E1] bg-white sticky top-0 z-30 shadow-2xs">
                <div class="flex items-center gap-3">
                    {{-- Hamburger Mobile --}}
                    <button @click="sidebarOpen = true" class="lg:hidden text-[#1E293B] p-2 rounded-lg hover:bg-[#F1F5F9] transition-colors border border-[#CBD5E1]">
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
                    <div class="relative" x-data="notificationDropdown({{ Js::from($systemNotifications ?? []) }})">
                        <button
                            @click="toggleDropdown()"
                            type="button"
                            class="relative p-2 text-[#64748B] hover:text-[#0F172A] transition-colors cursor-pointer focus:outline-none"
                            title="Pemberitahuan Sistem"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            </svg>
                            <template x-if="hasUnread">
                                <span
                                    class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-[#EF4444] text-white text-[10px] font-mono font-bold flex items-center justify-center shadow-xs"
                                    x-text="unreadCount > 9 ? '9+' : unreadCount"
                                ></span>
                            </template>
                        </button>

                        <div
                            x-show="open"
                            @click.away="closeDropdown()"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                            class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-lg border border-[#CBD5E1] shadow-lg z-50 overflow-hidden"
                            style="display: none;"
                        >
                            <div class="px-4 py-3 border-b border-[#CBD5E1] flex items-center justify-between bg-[#F8FAFC]">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-[#0F172A]">Notifikasi Sistem</h4>
                                    <template x-if="hasUnread">
                                        <span class="px-1.5 py-0.5 rounded-md bg-[#0F172A] text-white text-[10px] font-mono font-bold" x-text="unreadCount + ' Baru'"></span>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <button
                                        type="button"
                                        @click.stop="playNotificationSound()"
                                        class="text-[11px] font-semibold text-[#64748B] hover:text-[#2563EB] flex items-center gap-1 transition-colors cursor-pointer"
                                        title="Uji coba nada notifikasi"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.757 3.63 8.25 4.51 8.25H6.75z"/>
                                        </svg>
                                        <span>Tes Suara</span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="markAllAsRead()"
                                        class="text-[11px] font-semibold text-[#2563EB] hover:underline cursor-pointer"
                                        x-show="hasUnread"
                                    >
                                        Tandai dibaca
                                    </button>
                                </div>
                            </div>

                            <div class="max-h-80 overflow-y-auto divide-y divide-[#E2E8F0]">
                                <div x-show="notifications.length === 0" class="py-8 px-4 text-center">
                                    <div class="w-10 h-10 mx-auto text-[#94A3B8] flex items-center justify-center mb-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-[#64748B]">Tidak ada notifikasi baru</p>
                                    <p class="text-[11px] text-[#94A3B8] mt-0.5">Semua data pembacaan dan tagihan terkendali.</p>
                                </div>

                                <template x-for="item in notifications" :key="item.id">
                                    <div
                                        @click="readNotif(item)"
                                        class="p-3.5 hover:bg-[#F8FAFC] transition-colors cursor-pointer flex items-start gap-3 relative"
                                        :class="item.unread ? 'bg-[#F8FAFC]/80' : ''"
                                    >
                                        {{-- Icon Tanpa Kotak Belakang, Menggunakan Vektor Modern Profesional --}}
                                        <div class="shrink-0 mt-0.5 flex items-center justify-center">
                                            <template x-if="item.type === 'batch'">
                                                <svg class="w-5 h-5 text-[#2563EB]" viewBox="0 0 24 24" fill="currentColor" title="Batch Processing">
                                                    <path d="M11.644 1.59a.75.75 0 0 1 .712 0l9.75 5.25a.75.75 0 0 1 0 1.32l-9.75 5.25a.75.75 0 0 1-.712 0l-9.75-5.25a.75.75 0 0 1 0-1.32l9.75-5.25Z" />
                                                    <path d="M3.265 10.602l7.666 4.128a1.75 1.75 0 0 0 1.662 0l7.666-4.128 1.488.801a.75.75 0 0 1 0 1.32l-9.75 5.25a.75.75 0 0 1-.712 0l-9.75-5.25a.75.75 0 0 1 0-1.32l1.488-.801Z" />
                                                    <path d="M3.265 15.602l7.666 4.128a1.75 1.75 0 0 0 1.662 0l7.666-4.128 1.488.801a.75.75 0 0 1 0 1.32l-9.75 5.25a.75.75 0 0 1-.712 0l-9.75-5.25a.75.75 0 0 1 0-1.32l1.488-.801Z" />
                                                </svg>
                                            </template>
                                            <template x-if="item.type === 'valid'">
                                                <svg class="w-5 h-5 text-[#16A34A]" viewBox="0 0 24 24" fill="currentColor" title="Validasi Selesai">
                                                    <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                                </svg>
                                            </template>
                                            <template x-if="item.type === 'flag'">
                                                <svg class="w-5 h-5 text-[#EF4444]" viewBox="0 0 24 24" fill="currentColor" title="Anomali Flag">
                                                    <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                                                </svg>
                                            </template>
                                            <template x-if="item.type === 'wa'">
                                                <svg class="w-5 h-5 text-[#10B981]" viewBox="0 0 24 24" fill="currentColor" title="Notifikasi WhatsApp">
                                                    <path fill-rule="evenodd" d="M4.848 2.771A49.144 49.144 0 0 1 12 2.25c2.43 0 4.817.177 7.152.521 1.978.292 3.348 2.024 3.348 3.97v6.02c0 1.946-1.37 3.678-3.348 3.97a48.901 48.901 0 0 1-3.476.383.39.39 0 0 0-.297.17l-2.755 4.133a.75.75 0 0 1-1.248 0l-2.755-4.133a.39.39 0 0 0-.297-.17 48.9 48.9 0 0 1-3.476-.384c-1.978-.29-3.348-2.024-3.348-3.97V6.741c0-1.946 1.37-3.68 3.348-3.97ZM6.75 8.25a.75.75 0 0 1 .75-.75h9a.75.75 0 0 1 0 1.5h-9a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5H12a.75.75 0 0 0 0-1.5H7.5Z" clip-rule="evenodd" />
                                                </svg>
                                            </template>
                                            <template x-if="!['flag', 'batch', 'valid', 'wa'].includes(item.type)">
                                                <svg class="w-5 h-5 text-[#3B82F6]" viewBox="0 0 24 24" fill="currentColor" title="Info Sistem">
                                                    <path fill-rule="evenodd" d="M5.25 9a6.75 6.75 0 0 1 13.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 0 1-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 1 1-7.48 0 24.585 24.585 0 0 1-4.831-1.244.75.75 0 0 1-.298-1.205A8.217 8.217 0 0 0 5.25 9.75V9Zm4.502 8.9a2.25 2.25 0 0 0 4.496 0 25.057 25.057 0 0 1-4.496 0Z" clip-rule="evenodd" />
                                                </svg>
                                            </template>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1">
                                                <h5 class="text-xs font-bold text-[#0F172A] truncate" x-text="item.title"></h5>
                                                <span class="text-[10px] font-mono text-[#64748B] whitespace-nowrap" x-text="item.time"></span>
                                            </div>
                                            <p class="text-[11px] text-[#475569] mt-0.5 leading-snug line-clamp-2" x-text="item.desc"></p>
                                            <span
                                                class="inline-block mt-1 text-[10px] font-semibold"
                                                :class="item.unread ? 'text-[#2563EB]' : 'text-[#94A3B8]'"
                                                x-text="item.unread ? 'Belum Dibaca' : 'Sudah Dibaca'"
                                            ></span>
                                        </div>

                                        <template x-if="item.unread">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB] shrink-0 mt-1.5"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Profil Avatar Admin (Bundar Sesuai Permintaan) --}}
                    <button type="button" @click="profileModal = true" title="Edit Profil Admin" class="flex items-center gap-3 px-2 py-1.5 rounded-xl hover:bg-[#F8FAFC] transition-colors cursor-pointer">
                        <div class="w-9 h-9 rounded-full bg-[#0F172A] text-white font-mono font-bold text-xs flex items-center justify-center shadow-xs shrink-0 ring-2 ring-[#0F172A]/10">
                            {{ strtoupper(substr(Auth::user()->name ?? 'AD', 0, 2)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-[#0F172A] block leading-tight">{{ Auth::user()->name ?? 'Admin Pengelola Air' }}</span>
                                <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                            </div>
                            <span class="text-[10px] text-[#64748B] font-medium block">Administrator Utama</span>
                        </div>
                    </button>
                </div>
            </header>

            {{-- KONTEN HALAMAN --}}
            <main class="flex-1 px-4 sm:px-6 lg:px-10 pt-4 pb-6 sm:pt-5 sm:pb-8 bg-[#F1F5F9]">

                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="mb-3 px-4 py-3 bg-white border border-[#E2E8F0] rounded-xl text-sm flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5 text-[#0F172A] font-medium">
                        <svg class="w-5 h-5 text-[#10B981] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <span class="text-xs px-2.5 py-0.5 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] font-semibold">Berhasil</span>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-3 px-4 py-3 bg-white border border-[#E2E8F0] rounded-xl text-sm flex items-center justify-between shadow-xs">
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

    {{-- Modal Edit Profil Admin --}}
    <template x-teleport="body">
        <div x-show="profileModal"
             class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 bg-[#0F172A]/60"
             style="display: none;"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-2xl border border-[#CBD5E1] shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8" @click.away="profileModal = false">
                <div class="flex items-center justify-between pb-4 border-b border-[#F1F5F9]">
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A]">Profil Administrator</h3>
                        <p class="text-xs text-[#64748B]">Perbarui username, email login, atau password akun Admin</p>
                    </div>
                    <button type="button" @click="profileModal = false" class="text-[#94A3B8] hover:text-[#0F172A] p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @if(old('_profile_modal') && $errors->any())
                    <div class="mt-4 p-3 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-xs text-[#DC2626] space-y-1">
                        @foreach($errors->all() as $err)
                            <p>• {{ $err }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.profile.update') }}" class="mt-4 space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="_profile_modal" value="1">

                    <div>
                        <label class="block font-bold text-[#0F172A] mb-1">Username / Nama Tampilan <span class="text-[#DC2626]">*</span></label>
                        <input type="text" name="name" required value="{{ old('_profile_modal') ? old('name') : (Auth::user()->name ?? '') }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    </div>

                    <div>
                        <label class="block font-bold text-[#0F172A] mb-1">Email / Username Login <span class="text-[#DC2626]">*</span></label>
                        <input type="text" name="email" required value="{{ old('_profile_modal') ? old('email') : (Auth::user()->email ?? '') }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    </div>

                    <div>
                        <label class="block font-bold text-[#0F172A] mb-1">Password Baru <span class="text-[#64748B] font-normal">(Opsional)</span></label>
                        <input type="password" name="password" minlength="6" placeholder="Kosongkan jika tidak ingin mengubah password"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    </div>

                    <div>
                        <label class="block font-bold text-[#0F172A] mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" minlength="6" placeholder="Ulangi password baru"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#F1F5F9]">
                        <button type="button" @click="profileModal = false" class="px-4 py-2.5 rounded-xl border border-[#CBD5E1] text-[#64748B] hover:text-[#0F172A] font-bold text-xs cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs cursor-pointer">
                            Simpan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    @stack('scripts')
</body>
</html>
