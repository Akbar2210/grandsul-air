<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Petugas Lapangan — Grand Sulawesi Air</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Poppins', sans-serif !important;
        }
    </style>
</head>

<body class="bg-[#E2E8F0] text-[#0F172A] antialiased min-h-screen flex justify-center">

    <div class="w-full max-w-[430px] min-h-screen bg-[#F8FAFC] flex flex-col border-x border-[#CBD5E1]">

        @include('petugas.partials.topbar', ['title' => 'Daftar Unit', 'active' => 'dashboard'])

        <main class="px-4 py-4 space-y-4 flex-1">

            {{-- PROGRES PENCATATAN --}}
            <section class="bg-white border border-[#E2E8F0]">
                <div class="px-3.5 pt-3 pb-2.5">
                    <div class="flex items-baseline justify-between">
                        <span class="text-[11px] font-medium text-[#64748B]">Progres pencatatan bulan ini</span>
                        <span class="text-sm font-semibold font-mono text-[#0F172A]">{{ $progresPersen }}%</span>
                    </div>
                    <div class="w-full bg-[#F1F5F9] h-1.5 mt-2">
                        <div class="bg-[#2563EB] h-1.5" style="width: {{ $progresPersen }}%"></div>
                    </div>
                </div>
                <div class="grid grid-cols-3 border-t border-[#E2E8F0] text-center">
                    <div class="py-2.5">
                        <span class="text-[10px] text-[#64748B] block">Total unit</span>
                        <span class="text-[15px] font-semibold font-mono text-[#0F172A]">{{ $totalUnit }}</span>
                    </div>
                    <div class="py-2.5 border-x border-[#E2E8F0]">
                        <span class="text-[10px] text-[#64748B] block">Tercatat</span>
                        <span class="text-[15px] font-semibold font-mono text-[#0F172A]">{{ $totalTercatat }}</span>
                    </div>
                    <div class="py-2.5">
                        <span class="text-[10px] text-[#64748B] block">Belum</span>
                        <span class="text-[15px] font-semibold font-mono text-[#B45309]">{{ $totalSisa }}</span>
                    </div>
                </div>
            </section>

            {{-- CTA PINDAI --}}
            <a href="{{ route('petugas.pindai') }}"
                class="h-11 px-4 bg-[#2563EB] hover:bg-[#1D4ED8] text-white flex items-center justify-between transition-colors">
                <span class="text-[13px] font-semibold">Mulai pindai meter</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </a>

            {{-- PENCARIAN --}}
            <form method="GET" action="{{ route('petugas.dashboard') }}" class="flex">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nomor unit atau nama warga"
                    class="flex-1 min-w-0 h-10 px-3 bg-white border border-[#CBD5E1] border-r-0 text-xs text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:border-[#2563EB]">
                <button type="submit"
                    class="h-10 px-4 bg-[#0F172A] hover:bg-[#1E293B] text-white text-xs font-semibold shrink-0 cursor-pointer">
                    Cari
                </button>
            </form>

            {{-- DAFTAR UNIT --}}
            <section>
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xs font-semibold text-[#0F172A]">Unit lapangan</h2>
                    <span class="text-[11px] text-[#64748B]">{{ $units->count() }} unit</span>
                </div>

                <div class="bg-white border border-[#E2E8F0] divide-y divide-[#F1F5F9]">
                    @forelse($units as $u)
                        @php
                            $bacaan = $u->bacaanMeters->first();
                            $status = $bacaan ? $bacaan->status_verifikasi : 'belum_dicatat';
                            $statusMeta = match ($status) {
                                'valid' => ['label' => 'Valid', 'dot' => 'bg-[#16A34A]'],
                                'ter_flag' => ['label' => 'Menunggu verifikasi', 'dot' => 'bg-[#D97706]'],
                                'ditolak' => ['label' => 'Ditolak, foto ulang', 'dot' => 'bg-[#DC2626]'],
                                default => ['label' => 'Belum dicatat', 'dot' => 'bg-[#94A3B8]'],
                            };
                            $perluPindai = in_array($status, ['belum_dicatat', 'ditolak']);
                        @endphp
                        <div class="px-3.5 py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span
                                        class="font-mono text-[11px] font-semibold bg-[#F1F5F9] text-[#0F172A] border border-[#E2E8F0] px-1.5 py-0.5 whitespace-nowrap shrink-0">{{ $u->nomor_rumah }}</span>
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
                            </div>

                            <a href="{{ route('petugas.pindai', ['unit_id' => $u->id]) }}"
                                class="h-8 px-3 flex items-center text-[11px] font-semibold shrink-0 transition-colors {{ $perluPindai ? 'bg-[#0F172A] text-white hover:bg-[#1E293B]' : 'bg-white text-[#334155] border border-[#CBD5E1] hover:bg-[#F8FAFC]' }}">
                                {{ $perluPindai ? 'Pindai' : 'Detail' }}
                            </a>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-[#64748B]">Tidak ada unit ditemukan.</div>
                    @endforelse
                </div>
            </section>

        </main>
    </div>

</body>

</html>