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

<div class="sticky top-0 z-40">
    <header class="bg-[#0B1E3D] text-white h-14 px-4 flex items-center justify-between gap-3 border-b border-[#18325B]">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-9 h-9 flex items-center justify-center shrink-0">
                <x-brand-logo class="w-full h-full object-contain" />
            </div>
            <div class="min-w-0">
                <p class="text-[13px] font-semibold text-white leading-tight truncate">{{ $title }}</p>
                <p class="text-[10px] text-[#93C5FD] leading-tight truncate">{{ $periodeStr ?? now()->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div class="text-right max-w-[110px]">
                <p class="text-[11px] font-medium text-white leading-tight truncate">{{ $namaPetugas }}</p>
                <p class="text-[10px] text-[#94A3B8] leading-tight">Petugas</p>
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
