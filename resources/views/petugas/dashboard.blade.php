<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Petugas Lapangan — Pengelolaan Air Terintegrasi Grand Sulawesi</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Poppins', sans-serif !important;
        }
        /* Custom scrollbar for horizontal chips */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-[#E2E8F0] text-[#0F172A] antialiased min-h-screen flex justify-center petugas-app">

    <div class="w-full max-w-[430px] min-h-screen bg-[#F1F5F9] flex flex-col border-x border-[#CBD5E1]">

        @include('petugas.partials.topbar', ['title' => 'Daftar Unit', 'active' => 'dashboard'])

        <main class="px-4 py-4 space-y-3.5 flex-1">

            {{-- ==================== 1. KARTU PROGRES PENCATATAN (TAMPILAN ASLI RAPI) ==================== --}}
            <section class="bg-white border border-[#CBD5E1] rounded-2xl shadow-xs overflow-hidden">
                <div class="px-4 pt-3.5 pb-3">
                    <div class="flex items-baseline justify-between">
                        <span class="text-[11px] font-medium text-[#64748B]">
                            Progres pencatatan {{ $activeBlok ? $activeBlok->nama_blok : 'bulan ini' }}
                        </span>
                        <span class="text-sm font-semibold font-mono text-[#0F172A]">{{ $progresPersen }}%</span>
                    </div>
                    <div class="w-full bg-[#F1F5F9] h-2 mt-2 rounded-full overflow-hidden">
                        <div class="bg-[#2563EB] h-2 rounded-full transition-all duration-500" style="width: {{ $progresPersen }}%"></div>
                    </div>
                </div>

                {{-- Kolom Statistik Interaktif --}}
                <div class="grid grid-cols-3 border-t border-[#CBD5E1] text-center bg-[#FAFCFF]/50 divide-x divide-[#CBD5E1]">
                    <a href="{{ route('petugas.dashboard', ['blok_id' => $selectedBlokId, 'status' => 'all', 'search' => request('search')]) }}"
                       class="py-2.5 transition-colors {{ $filterStatus === 'all' ? 'bg-[#EFF6FF]' : 'hover:bg-[#F1F5F9]' }}">
                        <span class="text-[10px] text-[#64748B] block">Total unit</span>
                        <span class="text-[15px] font-semibold font-mono text-[#0F172A]">{{ $totalUnit }}</span>
                    </a>
                    <a href="{{ route('petugas.dashboard', ['blok_id' => $selectedBlokId, 'status' => 'tercatat', 'search' => request('search')]) }}"
                       class="py-2.5 transition-colors {{ $filterStatus === 'tercatat' ? 'bg-[#EFF6FF]' : 'hover:bg-[#F1F5F9]' }}">
                        <span class="text-[10px] text-[#64748B] block">Tercatat</span>
                        <span class="text-[15px] font-semibold font-mono text-[#16A34A]">{{ $totalTercatat }}</span>
                    </a>
                    <a href="{{ route('petugas.dashboard', ['blok_id' => $selectedBlokId, 'status' => 'belum_dicatat', 'search' => request('search')]) }}"
                       class="py-2.5 transition-colors {{ ($filterStatus === 'belum_dicatat' || $filterStatus === 'belum') ? 'bg-[#EFF6FF]' : 'hover:bg-[#F1F5F9]' }}">
                        <span class="text-[10px] text-[#64748B] block">Belum</span>
                        <span class="text-[15px] font-semibold font-mono text-[#B45309]">{{ $totalSisa }}</span>
                    </a>
                </div>

                @if(isset($totalDitolak) && $totalDitolak > 0)
                <div class="border-t border-[#FEE2E2] bg-[#FEF2F2] px-4 py-1.5 flex items-center justify-between text-xs">
                    <span class="text-[#DC2626] font-medium text-[11px] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#DC2626] animate-pulse"></span>
                        Ada {{ $totalDitolak }} unit ditolak admin (perlu foto ulang)
                    </span>
                    <a href="{{ route('petugas.dashboard', ['blok_id' => $selectedBlokId, 'status' => 'ditolak']) }}"
                       class="text-[11px] font-bold text-[#DC2626] underline hover:text-[#B91C1C]">
                        Tinjau
                    </a>
                </div>
                @endif
            </section>

            {{-- ==================== 2. TOMBOL MULAI PINDAI METER ==================== --}}
            <a href="{{ route('petugas.pindai', ['blok_id' => ($activeBlok ? $activeBlok->id : null)]) }}"
                class="h-12 px-4 bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-[0.99] text-white rounded-xl shadow-sm shadow-[#2563EB]/25 flex items-center justify-between transition-all">
                <span class="text-[13px] font-semibold">
                    {{ $activeBlok ? 'Mulai pindai meter ' . $activeBlok->nama_blok : 'Mulai pindai meter' }}
                </span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </a>

            {{-- ==================== 3. PILIHAN BLOK (CHIP SELEKTOR CEPAT) ==================== --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-[11px] px-0.5">
                    <span class="font-semibold text-[#64748B]">Pilih Blok:</span>
                    @if($activeBlok)
                    <a href="{{ route('petugas.dashboard', ['blok_id' => 'all', 'status' => $filterStatus]) }}" class="text-[#2563EB] font-medium hover:underline">
                        Lihat Semua Blok
                    </a>
                    @endif
                </div>
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                    <a href="{{ route('petugas.dashboard', ['blok_id' => 'all', 'status' => $filterStatus]) }}"
                       class="h-8 px-3 rounded-lg text-xs font-semibold whitespace-nowrap flex items-center transition-all shrink-0 {{ (!$selectedBlokId || $selectedBlokId === 'all') ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-white text-[#475569] border border-[#CBD5E1] hover:bg-[#F8FAFC]' }}">
                        Semua
                    </a>
                    @foreach($bloks as $b)
                        @php $isBActive = ($selectedBlokId && (string)$selectedBlokId === (string)$b->id); @endphp
                        <a href="{{ route('petugas.dashboard', ['blok_id' => $b->id, 'status' => $filterStatus]) }}"
                           class="h-8 px-3 rounded-lg text-xs font-semibold whitespace-nowrap flex items-center transition-all shrink-0 {{ $isBActive ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-white text-[#475569] border border-[#CBD5E1] hover:bg-[#F8FAFC]' }}">
                            {{ $b->nama_blok }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- ==================== 4. PENCARIAN ==================== --}}
            <form method="GET" action="{{ route('petugas.dashboard') }}" class="flex items-center gap-2">
                <input type="hidden" name="blok_id" value="{{ $selectedBlokId }}">
                <input type="hidden" name="status" value="{{ $filterStatus }}">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nomor unit atau nama warga..."
                        class="w-full h-11 pl-3.5 pr-3 bg-white border border-[#CBD5E1] rounded-xl text-xs text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition-all shadow-xs">
                </div>
                <button type="submit"
                    class="h-11 px-4 bg-[#0F172A] hover:bg-[#1E293B] active:scale-[0.98] text-white text-xs font-semibold rounded-xl shadow-xs transition-all shrink-0 cursor-pointer">
                    Cari
                </button>
            </form>

            {{-- ==================== 5. DAFTAR UNIT LAPANGAN ==================== --}}
            <section>
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xs font-semibold text-[#0F172A]">
                        Unit lapangan {{ $activeBlok ? '— ' . $activeBlok->nama_blok : '' }}
                    </h2>
                    <span class="text-[11px] text-[#64748B] font-mono">{{ $units->total() }} unit</span>
                </div>

                <div class="bg-white border border-[#CBD5E1] rounded-2xl shadow-xs divide-y divide-[#F1F5F9] overflow-hidden">
                    @forelse($units as $u)
                        @php
                            $bacaan = $u->bacaanMeters->first();
                            $status = $bacaan ? $bacaan->status_verifikasi : 'belum_dicatat';
                            $statusMeta = match ($status) {
                                'valid' => ['label' => 'Valid', 'dot' => 'bg-[#16A34A]'],
                                'ter_flag' => ['label' => 'Menunggu verifikasi', 'dot' => 'bg-[#D97706]'],
                                'ditolak' => ['label' => 'Ditolak, perlu foto ulang', 'dot' => 'bg-[#DC2626]'],
                                default => ['label' => 'Belum dicatat', 'dot' => 'bg-[#94A3B8]'],
                            };
                            $perluPindai = in_array($status, ['belum_dicatat', 'ditolak']);
                        @endphp
                        <div class="px-3.5 py-3 flex items-center justify-between gap-3 hover:bg-[#F8FAFC] transition-colors">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span
                                        class="font-mono text-[11px] font-semibold bg-[#F1F5F9] text-[#0F172A] border border-[#CBD5E1] rounded-md px-2 py-0.5 whitespace-nowrap shrink-0">{{ $u->nomor_rumah }}</span>
                                    <span
                                        class="text-[13px] font-medium text-[#0F172A] truncate">{{ $u->nama_pemilik }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 mt-1 text-[11px] text-[#64748B] whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusMeta['dot'] }} shrink-0"></span>
                                    <span>{{ $statusMeta['label'] }}</span>
                                    @if($bacaan)
                                        <span class="text-[#CBD5E1]">|</span>
                                        <span class="font-mono text-[#334155]">{{ $bacaan->angka_meter }} m³</span>
                                    @endif
                                </div>

                                {{-- Catatan Alasan Ditolak Jika Ada --}}
                                @if($bacaan && $bacaan->status_verifikasi === 'ditolak' && $bacaan->alasan_penolakan)
                                    <div class="mt-1.5 text-[10px] text-[#DC2626] font-medium bg-[#FEF2F2] px-2 py-1 rounded-lg border border-[#FECACA]">
                                        Alasan admin: {{ $bacaan->alasan_penolakan }}
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route('petugas.pindai', ['blok_id' => $u->blok_id, 'unit_id' => $u->id]) }}"
                                class="h-8 px-3.5 flex items-center text-[11px] font-semibold rounded-lg shrink-0 transition-all {{ $perluPindai ? 'bg-[#2563EB] text-white hover:bg-[#1D4ED8] shadow-xs' : 'bg-white text-[#334155] border border-[#CBD5E1] hover:bg-[#F8FAFC]' }}">
                                {{ $perluPindai ? 'Pindai' : 'Detail' }}
                            </a>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-[#64748B]">Tidak ada unit ditemukan.</div>
                    @endforelse
                </div>

                @if($units->hasPages())
                    <div class="mt-3">
                        {{ $units->appends(request()->query())->links('partials.petugas-pagination') }}
                    </div>
                @endif
            </section>

        </main>
    </div>

</body>

</html>
