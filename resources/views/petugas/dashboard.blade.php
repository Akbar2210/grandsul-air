<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Petugas Lapangan (Mobile) — Grand Sulawesi Air</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Poppins', sans-serif !important;
        }
    </style>
</head>
<body class="bg-[#0B132B] text-[#0F172A] antialiased min-h-screen flex justify-center">

    {{-- ==================== MOBILE WEB APP CONTAINER (MAX 430PX) ==================== --}}
    <div class="w-full max-w-[430px] min-h-screen bg-[#F1F5F9] flex flex-col justify-between relative shadow-2xl border-x border-[#1E293B]/40 pb-20">

        <div>
            {{-- MOBILE TOP APP BAR (NAVY DEEP BLUE) --}}
            <header class="bg-[#0F172A] text-white px-4 pt-4 pb-5 rounded-b-3xl shadow-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#1E293B] border border-[#334155] flex items-center justify-center p-1.5">
                            <x-brand-logo class="w-full h-full object-contain" />
                        </div>
                        <div>
                            <span class="text-xs font-extrabold tracking-wide text-white block leading-none">GRAND SULAWESI</span>
                            <span class="text-[10px] text-[#00F2FE] font-semibold mt-0.5 block">Aplikasi Mobile Petugas</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="text-right">
                            <span class="text-[11px] font-bold text-white block leading-none">{{ Auth::user()->name ?? 'Hendra' }}</span>
                            <span class="text-[9px] text-[#94A3B8] block mt-0.5">{{ $periodeStr }}</span>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-[#00F2FE] text-[#0B132B] font-extrabold text-xs flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr(Auth::user()->name ?? 'HE', 0, 2)) }}
                        </div>
                    </div>
                </div>

                {{-- PROGRES CARD DI DALAM HEADER --}}
                <div class="mt-4 bg-[#1E293B] border border-[#334155] rounded-2xl p-3.5">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-semibold text-[#CBD5E1]">Progres Pencatatan Sektor 04</span>
                        <span class="text-xs font-extrabold text-[#00F2FE]">{{ $progresPersen }}%</span>
                    </div>
                    <div class="w-full bg-[#0F172A] rounded-full h-2.5 overflow-hidden mb-2">
                        <div class="bg-[#00F2FE] h-2.5 rounded-full transition-all duration-500" style="width: {{ $progresPersen }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-[#94A3B8] font-medium">
                        <span><strong class="text-white">{{ $totalTercatat }}</strong> Tercatat</span>
                        <span><strong class="text-[#FBBF24]">{{ $totalSisa }}</strong> Sisa Perlu Pindai</span>
                        <span>Total <strong class="text-white">{{ $totalUnit }}</strong> Unit</span>
                    </div>
                </div>
            </header>

            {{-- KONTEN UTAMA MOBILE --}}
            <main class="px-3.5 pt-4 space-y-3.5">

                {{-- TOMBOL UTAMA PINDAI KAMERA OCR --}}
                <a
                    href="{{ route('petugas.pindai') }}"
                    class="w-full py-3 px-4 rounded-2xl bg-[#00F2FE] hover:bg-[#00C4CE] active:scale-[0.99] text-[#0B132B] font-extrabold text-xs flex items-center justify-between shadow-md transition-all"
                >
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#0B132B] text-[#00F2FE] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-xs font-extrabold leading-tight">Mulai Pindai Kamera OCR</span>
                            <span class="block text-[10px] font-medium text-[#1E293B]">Foto meteran air dalam radius 700m</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-[#0B132B]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>
                </a>

                {{-- 4 KARTU RINGKASAN (GRID 2x2 MOBILE) --}}
                <div class="grid grid-cols-2 gap-2.5">
                    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-3 shadow-xs">
                        <span class="text-[10px] font-bold text-[#64748B] uppercase block">Total Unit</span>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-xl font-extrabold text-[#0F172A]">{{ $totalUnit }}</span>
                            <span class="text-[10px] font-semibold text-[#64748B]">Rumah</span>
                        </div>
                    </div>

                    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-3 shadow-xs">
                        <span class="text-[10px] font-bold text-[#16A34A] uppercase block">Selesai</span>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-xl font-extrabold text-[#16A34A]">{{ $totalTercatat }}</span>
                            <span class="text-[10px] font-semibold text-[#64748B]">Tercatat</span>
                        </div>
                    </div>

                    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-3 shadow-xs">
                        <span class="text-[10px] font-bold text-[#D97706] uppercase block">Belum Pindai</span>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-xl font-extrabold text-[#D97706]">{{ $totalSisa }}</span>
                            <span class="text-[10px] font-semibold text-[#64748B]">Antrean</span>
                        </div>
                    </div>

                    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-3 shadow-xs">
                        <span class="text-[10px] font-bold text-[#2563EB] uppercase block">Batas Radius</span>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-xl font-extrabold text-[#0F172A]">700</span>
                            <span class="text-[10px] font-semibold text-[#64748B]">Meter (GPS)</span>
                        </div>
                    </div>
                </div>

                {{-- PENCARIAN UNIT --}}
                <form method="GET" action="{{ route('petugas.dashboard') }}" class="flex gap-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nomor rumah (misal A-03) atau nama..."
                        class="flex-1 px-3.5 py-2 rounded-xl bg-white border border-[#CBD5E1] text-xs text-[#0F172A] focus:outline-none focus:border-[#00F2FE]"
                    >
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#0F172A] text-white text-xs font-bold shrink-0">
                        Cari
                    </button>
                </form>

                {{-- DAFTAR KARTU UNIT HUNIAN (MOBILE CARDS) --}}
                <div class="space-y-2.5 pb-4">
                    <div class="flex items-center justify-between px-1">
                        <h2 class="text-xs font-extrabold text-[#0F172A] uppercase tracking-wider">Daftar Unit Lapangan</h2>
                        <a href="{{ route('petugas.riwayat') }}" class="text-[11px] font-bold text-[#2563EB]">Lihat Riwayat →</a>
                    </div>

                    @forelse($units as $u)
                        @php
                            $bacaan = $u->bacaanMeters->first();
                            $status = $bacaan ? $bacaan->status_verifikasi : 'belum_dicatat';
                        @endphp
                        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-3.5 shadow-xs flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-xl bg-[#0F172A] text-white flex flex-col items-center justify-center shrink-0">
                                    <span class="text-[8px] text-[#94A3B8] font-semibold leading-none">UNIT</span>
                                    <span class="text-xs font-extrabold text-[#00F2FE] mt-0.5">{{ $u->nomor_rumah }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-[#0F172A] truncate">{{ $u->nama_pemilik }}</p>
                                    <p class="text-[10px] text-[#64748B] mt-0.5">
                                        {{ optional($u->blok)->nama_blok ?? 'Blok A' }} •
                                        @if($bacaan)
                                            Stand: <strong class="text-[#0F172A]">{{ $bacaan->angka_meter }} m³</strong>
                                        @else
                                            Belum direkam bulan ini
                                        @endif
                                    </p>
                                    <div class="mt-1">
                                        @if($status === 'valid')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#DCFCE7] text-[#166534]">
                                                ● Valid / Selesai
                                            </span>
                                        @elseif($status === 'ter_flag')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#FEF3C7] text-[#92400E]">
                                                ▲ Butuh Verifikasi Admin
                                            </span>
                                        @elseif($status === 'ditolak')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#FEE2E2] text-[#991B1B]">
                                                ✕ Ditolak (Foto Ulang)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#F1F5F9] text-[#475569]">
                                                ○ Belum Dicatat
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <a
                                href="{{ route('petugas.pindai', ['unit_id' => $u->id]) }}"
                                class="px-3 py-2 rounded-xl text-[11px] font-bold shrink-0 transition-all {{ $status === 'belum_dicatat' || $status === 'ditolak' ? 'bg-[#0F172A] text-[#00F2FE] hover:bg-[#1E293B]' : 'bg-[#F1F5F9] text-[#475569]' }}"
                            >
                                {{ $status === 'belum_dicatat' || $status === 'ditolak' ? 'Pindai' : 'Detail' }}
                            </a>
                        </div>
                    @empty
                        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 text-center text-xs text-[#64748B]">
                            Tidak ada unit ditemukan.
                        </div>
                    @endforelse
                </div>

            </main>
        </div>

        {{-- ==================== BOTTOM NAVIGATION BAR (KHAS MOBILE APP) ==================== --}}
        <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[430px] bg-[#0F172A] border-t border-[#1E293B] px-4 py-2 flex items-center justify-around z-50 rounded-t-2xl shadow-lg">
            <a href="{{ route('petugas.dashboard') }}" class="flex flex-col items-center gap-0.5 text-[#00F2FE] font-bold text-[10px]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                </svg>
                <span>Daftar Unit</span>
            </a>

            <a href="{{ route('petugas.pindai') }}" class="flex flex-col items-center -mt-5">
                <div class="w-12 h-12 rounded-full bg-[#00F2FE] text-[#0B132B] border-4 border-[#0F172A] flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-white mt-0.5">Pindai OCR</span>
            </a>

            <a href="{{ route('petugas.riwayat') }}" class="flex flex-col items-center gap-0.5 text-[#94A3B8] hover:text-white font-medium text-[10px]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Riwayat</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex flex-col items-center gap-0.5 text-[#EF4444] font-medium text-[10px] cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </nav>

    </div>

</body>
</html>
