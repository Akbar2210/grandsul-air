{{--
    Top bar + navigasi atas untuk aplikasi mobile Petugas.
    Parameter: $title (string), $active ('dashboard' | 'pindai' | 'riwayat')
--}}
@php
    $menus = [
        'dashboard' => ['label' => 'Daftar Unit', 'route' => 'petugas.dashboard'],
        'pindai' => ['label' => 'Pindai Meter', 'route' => 'petugas.pindai'],
        'riwayat' => ['label' => 'Riwayat', 'route' => 'petugas.riwayat'],
    ];
    $namaPetugas = Auth::user()->name ?? 'Petugas';
@endphp

<script>
    if (typeof window.petugasNotificationDropdown !== 'function') {
        window.petugasNotificationDropdown = function(initial) {
            return {
                open: false,
                notifications: initial || [],
                get unreadCount() {
                    return this.notifications.filter(function(n) { return !!n.unread; }).length;
                },
                get hasUnread() {
                    return this.unreadCount > 0;
                },
                init() {
                    setInterval(() => {
                        this.refreshNotifications();
                    }, 15000);
                },
                async refreshNotifications() {
                    try {
                        const res = await fetch('{{ route('petugas.notifikasi.unread-list') }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) return;
                        const data = await res.json();
                        if (data && Array.isArray(data.notifications)) {
                            this.notifications = data.notifications;
                        }
                    } catch (e) {}
                },
                toggleDropdown() {
                    if (this.open) {
                        this.closeDropdown();
                    } else {
                        this.open = true;
                        this.refreshNotifications();
                    }
                },
                closeDropdown() {
                    this.open = false;
                    this.notifications = this.notifications.filter(function(n) { return n.unread; });
                },
                async markAllAsRead() {
                    this.notifications.forEach(function(n) { n.unread = false; });
                    try {
                        await fetch('{{ route('petugas.notifikasi.read-all') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });
                    } catch (e) {}
                },
                async readNotif(item) {
                    item.unread = false;
                    try {
                        await fetch('/petugas/notifikasi/' + item.id + '/read', {
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
        };
    }
</script>

<div class="sticky top-0 z-40">
    <header class="bg-[#0B1E3D] text-white h-14 px-4 flex items-center justify-between gap-2 border-b border-[#18325B]">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-9 h-9 flex items-center justify-center shrink-0">
                <x-brand-logo class="w-full h-full object-contain" />
            </div>
            <div class="min-w-0">
                <p class="text-[13px] font-semibold text-white leading-tight truncate">{{ $title }}</p>
                <p class="text-[10px] text-[#93C5FD] leading-tight truncate">{{ $periodeStr ?? now()->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-1.5 shrink-0">
            <div class="text-right max-w-[96px] mr-0.5">
                <p class="text-[11px] font-medium text-white leading-tight truncate">{{ $namaPetugas }}</p>
                <p class="text-[10px] text-[#94A3B8] leading-tight">Petugas</p>
            </div>

            {{-- Ikon Lonceng Notifikasi Petugas (Pojok Kanan Atas) --}}
            <div class="relative" x-data="petugasNotificationDropdown({{ Js::from($systemNotifications ?? []) }})">
                <button
                    type="button"
                    @click="toggleDropdown()"
                    title="Notifikasi"
                    aria-label="Notifikasi"
                    class="relative w-8 h-8 flex items-center justify-center border border-[#18325B] rounded-lg text-[#CBD5E1] hover:text-white hover:bg-[#142A50] transition-colors cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                    <template x-if="hasUnread">
                        <span
                            class="absolute -top-1 -right-1 min-w-[16px] h-[16px] px-1 rounded-full bg-[#EF4444] text-white text-[9px] font-mono font-bold flex items-center justify-center shadow-xs"
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
                    class="absolute right-0 mt-2 w-80 bg-white text-[#0F172A] rounded-xl border border-[#CBD5E1] shadow-xl z-50 overflow-hidden"
                    style="display: none;"
                >
                    <div class="px-3.5 py-2.5 border-b border-[#CBD5E1] flex items-center justify-between bg-[#F8FAFC]">
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-bold text-[#0F172A]">Notifikasi</h4>
                            <template x-if="hasUnread">
                                <span class="px-1.5 py-0.5 rounded-md bg-[#EF4444] text-white text-[10px] font-mono font-bold" x-text="unreadCount + ' Baru'"></span>
                            </template>
                        </div>
                        <button
                            type="button"
                            @click="markAllAsRead()"
                            x-show="hasUnread"
                            class="text-[11px] font-semibold text-[#2563EB] hover:underline cursor-pointer"
                        >
                            Tandai dibaca
                        </button>
                    </div>

                    <div class="max-h-80 overflow-y-auto divide-y divide-[#E2E8F0]">
                        <div x-show="notifications.length === 0" class="py-8 px-4 text-center">
                            <div class="w-10 h-10 mx-auto text-[#94A3B8] flex items-center justify-center mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                                </svg>
                            </div>
                            <p class="text-xs font-semibold text-[#475569]">Belum ada notifikasi saat ini</p>
                            <p class="text-[11px] text-[#94A3B8] mt-0.5">Pemberitahuan penolakan atau tugas baru akan muncul di sini.</p>
                        </div>

                        <template x-for="item in notifications" :key="item.id">
                            <div
                                @click="readNotif(item)"
                                class="p-3.5 hover:bg-[#F8FAFC] transition-colors cursor-pointer flex items-start gap-2.5"
                                :class="item.unread ? 'bg-[#EFF6FF]/60' : ''"
                            >
                                <div class="shrink-0 mt-0.5">
                                    <template x-if="item.type === 'flag'">
                                        <svg class="w-4 h-4 text-[#DC2626]" viewBox="0 0 24 24" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                                        </svg>
                                    </template>
                                    <template x-if="item.type !== 'flag'">
                                        <svg class="w-4 h-4 text-[#2563EB]" viewBox="0 0 24 24" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.25 9a6.75 6.75 0 0 1 13.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 0 1-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 1 1-7.48 0 24.585 24.585 0 0 1-4.831-1.244.75.75 0 0 1-.298-1.205A8.217 8.217 0 0 0 5.25 9.75V9Zm4.502 8.9a2.25 2.25 0 0 0 4.496 0 25.057 25.057 0 0 1-4.496 0Z" clip-rule="evenodd" />
                                        </svg>
                                    </template>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <h5 class="text-xs font-bold text-[#0F172A] truncate" x-text="item.title"></h5>
                                        <span class="text-[10px] font-mono text-[#64748B] whitespace-nowrap" x-text="item.time"></span>
                                    </div>
                                    <p class="text-[11px] text-[#475569] mt-0.5 leading-snug" x-text="item.desc"></p>
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

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Keluar" aria-label="Keluar"
                        class="w-8 h-8 flex items-center justify-center border border-[#18325B] rounded-lg text-[#94A3B8] hover:text-white hover:bg-[#142A50] transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                </button>
            </form>
        </div>
    </header>

    <nav class="bg-white border-b border-[#CBD5E1] grid grid-cols-3">
        @foreach($menus as $key => $menu)
            <a href="{{ route($menu['route']) }}"
               class="h-10 flex items-center justify-center text-xs border-b-2 transition-colors {{ $active === $key ? 'border-[#2563EB] text-[#0F172A] font-semibold' : 'border-transparent text-[#64748B] font-medium hover:text-[#0F172A]' }}">
                {{ $menu['label'] }}
            </a>
        @endforeach
    </nav>
</div>
