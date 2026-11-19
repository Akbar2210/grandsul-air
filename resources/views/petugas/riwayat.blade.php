<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Riwayat Pencatatan (Mobile) – Grand Sulawesi Air</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Poppins', sans-serif !important;
        }
    </style>
</head>
<body class="bg-[#0B132B] text-[#1E293B] antialiased min-h-screen flex justify-center">

    {{-- ==================== MOBILE WEB APP CONTAINER (MAX 430PX) ==================== --}}
    <div class="w-full max-w-[430px] min-h-screen bg-[#F1F5F9] flex flex-col justify-between relative shadow-2xl border-x border-[#1E293B]/40 pb-20">

        <div class="flex-1 flex flex-col min-w-0">

            {{-- TOPBAR MOBILE --}}
            <header class="px-4 py-3.5 bg-[#0F172A] text-white flex items-center justify-between rounded-b-2xl shadow-md">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#1E293B] border border-[#334155] flex items-center justify-center p-1.5 shrink-0">
                        <x-brand-logo class="w-full h-full object-contain" />
                    </div>
                    <div>
                        <h1 class="text-xs font-extrabold text-white tracking-tight leading-none">Riwayat Pencatatan</h1>
                        <p class="text-[10px] text-[#00F2FE] font-medium mt-0.5">Periode: {{ $periodeStr }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <div class="text-right">
                        <span class="text-[11px] font-bold text-white block leading-tight">{{ Auth::user()->name ?? 'Petugas' }}</span>
                        <span class="text-[9px] text-[#94A3B8] font-medium block">Mobile Petugas</span>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-[#00F2FE] text-[#0B132B] font-extrabold text-xs flex items-center justify-center shadow-xs">
                        {{ strtoupper(substr(Auth::user()->name ?? 'HE', 0, 2)) }}
                    </div>
                </div>
            </header>

            {{-- KONTEN MOBILE --}}
            <main class="flex-1 px-3.5 py-4 space-y-3.5">

                {{-- Filter Ringkas Mobile --}}
                <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-xs p-3">
                    <form method="GET" action="{{ route('petugas.riwayat') }}" class="grid grid-cols-3 gap-2 items-end">
                        <div>
                            <label class="text-[10px] font-bold text-[#64748B] uppercase block mb-1">Bulan</label>
                            <select name="bulan" class="w-full px-2 py-1.5 rounded-lg border border-[#CBD5E1] text-xs text-[#0F172A]">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $filterBulan == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-[#64748B] uppercase block mb-1">Status</label>
                            <select name="status" class="w-full px-2 py-1.5 rounded-lg border border-[#CBD5E1] text-xs text-[#0F172A]">
                                <option value="semua" {{ $filterStatus === 'semua' ? 'selected' : '' }}>Semua</option>
                                <option value="valid" {{ $filterStatus === 'valid' ? 'selected' : '' }}>Valid</option>
                                <option value="ter_flag" {{ $filterStatus === 'ter_flag' ? 'selected' : '' }}>Ter-flag</option>
                                <option value="ditolak" {{ $filterStatus === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <input type="hidden" name="tahun" value="{{ $filterTahun }}">
                        <button type="submit" class="py-1.5 px-3 rounded-lg bg-[#0F172A] text-[#00F2FE] text-xs font-bold cursor-pointer">
                            Filter
                        </button>
                    </form>
                </div>

                {{-- Daftar Kartu Riwayat --}}
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between px-1">
                        <span class="text-xs font-extrabold text-[#0F172A]">Total {{ $riwayat->total() }} Data</span>
                        <a href="{{ route('petugas.pindai') }}" class="text-[11px] font-bold text-[#2563EB]">+ Pindai Baru</a>
                    </div>

                    @forelse ($riwayat as $r)
                        @php
                            $pemakaian = $r->angka_meter - $r->angka_meter_bulanlalu;
                            $statusClass = match($r->status_verifikasi) {
                                'valid'   => 'bg-[#DCFCE7] text-[#166534] border-[#86EFAC]',
                                'ter_flag'=> 'bg-[#FEF3C7] text-[#92400E] border-[#FDE68A]',
                                'ditolak' => 'bg-[#FEE2E2] text-[#991B1B] border-[#FCA5A5]',
                                default   => 'bg-[#F1F5F9] text-[#475569] border-[#CBD5E1]',
                            };
                            $statusLabel = match($r->status_verifikasi) {
                                'valid'   => 'Valid',
                                'ter_flag'=> 'Butuh Verifikasi Admin',
                                'ditolak' => 'Ditolak',
                                default   => 'Belum Dicatat',
                            };
                        @endphp
                        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-3.5 shadow-xs space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-[#0F172A] text-[#00F2FE] font-extrabold text-xs flex items-center justify-center shrink-0">
                                        {{ optional($r->unit)->nomor_rumah ?? '—' }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-[#0F172A]">{{ optional($r->unit)->nama_pemilik ?? '—' }}</p>
                                        <p class="text-[10px] text-[#64748B]">{{ $r->created_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <div class="grid grid-cols-3 gap-1.5 text-center bg-[#F8FAFC] p-2 rounded-xl border border-[#E2E8F0] text-xs">
                                <div>
                                    <span class="text-[9px] text-[#64748B] block">Lalu</span>
                                    <strong class="text-[#0F172A]">{{ number_format($r->angka_meter_bulanlalu, 0) }} m³</strong>
                                </div>
                                <div>
                                    <span class="text-[9px] text-[#64748B] block">Kini (OCR)</span>
                                    <strong class="text-[#0F172A]">{{ number_format($r->angka_meter, 0) }} m³</strong>
                                </div>
                                <div>
                                    <span class="text-[9px] text-[#64748B] block">Pemakaian</span>
                                    <strong class="{{ $pemakaian < 0 ? 'text-[#DC2626]' : 'text-[#16A34A]' }}">
                                        {{ $pemakaian < 0 ? '' : '+' }}{{ number_format($pemakaian, 0) }} m³
                                    </strong>
                                </div>
                            </div>

                            @if($r->catatan_anomali)
                                <p class="text-[10px] text-[#92400E] bg-[#FEF3C7]/60 px-2.5 py-1 rounded-lg">
                                    Catatan: {{ $r->catatan_anomali }}
                                </p>
                            @endif
                        </div>
                    @empty
                        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-8 text-center text-xs text-[#64748B]">
                            Belum ada data pencatatan untuk filter ini.
                        </div>
                    @endforelse

                    @if ($riwayat->hasPages())
                        <div class="pt-2">
                            {{ $riwayat->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>

            </main>
        </div>

        {{-- ==================== BOTTOM NAVIGATION BAR (KHAS MOBILE APP) ==================== --}}
        <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[430px] bg-[#0F172A] border-t border-[#1E293B] px-4 py-2 flex items-center justify-around z-50 rounded-t-2xl shadow-lg">
            <a href="{{ route('petugas.dashboard') }}" class="flex flex-col items-center gap-0.5 text-[#94A3B8] hover:text-white font-medium text-[10px]">
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

            <a href="{{ route('petugas.riwayat') }}" class="flex flex-col items-center gap-0.5 text-[#00F2FE] font-bold text-[10px]">
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
