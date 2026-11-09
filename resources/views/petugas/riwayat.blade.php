<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Riwayat Data – Grand Sulawesi Air</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

        {{-- SIDEBAR --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 w-[250px] bg-[#0F172A] flex flex-col justify-between transition-transform duration-300 ease-in-out lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div>
                <div class="h-20 px-6 flex items-center justify-between border-b border-[#1E293B]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#1E293B] flex items-center justify-center p-2 shadow-xs">
                            <x-brand-logo class="w-full h-full object-contain" />
                        </div>
                        <div>
                            <span class="text-sm font-bold text-white tracking-wide block leading-none">GRAND SULAWESI</span>
                            <span class="text-[11px] text-[#64748B] font-medium tracking-tight mt-1 block">Petugas Lapangan</span>
                        </div>
                    </div>
                    <button @click="sidebarOpen = false" class="lg:hidden text-[#94A3B8] hover:text-white p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 pt-6 pb-2 text-[11px] font-bold text-[#64748B] uppercase tracking-wider">MAIN MENU</div>

                <nav class="px-3 space-y-1">
                    <a href="{{ route('petugas.dashboard') }}" class="menu-item-modern">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                        <span>Daftar Unit</span>
                    </a>
                    <a href="{{ route('petugas.pindai') }}" class="menu-item-modern">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                        </svg>
                        <span>Pindai Meter</span>
                    </a>
                    <a href="{{ route('petugas.riwayat') }}" class="menu-item-modern active">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Riwayat Data</span>
                    </a>
                </nav>
            </div>

            <div class="p-4 border-t border-[#1E293B]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[#94A3B8] hover:text-[#EF4444] hover:bg-[#1E293B] text-sm font-medium transition-colors">
                        <svg class="w-5 h-5 text-[#EF4444]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <div class="flex-1 lg:pl-[250px] flex flex-col min-w-0">

            {{-- TOPBAR --}}
            <header class="px-4 sm:px-6 lg:px-10 pt-5 sm:pt-7 pb-4 sm:pb-5 border-b border-[#E2E8F0]/80 bg-white/50 backdrop-blur-xs">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <button @click="sidebarOpen = true" class="lg:hidden text-[#1E293B] p-2 rounded-lg hover:bg-[#F1F5F9] transition-colors border border-[#E2E8F0] shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        </button>
                        <div>
                            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-[#0F172A] tracking-tight">Riwayat Pencatatan</h1>
                            <p class="text-xs text-[#64748B] font-medium mt-0.5">Periode: {{ $periodeStr }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 shrink-0">
                        <div class="hidden md:block text-right">
                            <span class="text-xs font-bold text-[#0F172A] block leading-tight">{{ Auth::user()->name ?? 'Petugas' }}</span>
                            <span class="text-[10px] text-[#64748B] font-medium block">Petugas Lapangan</span>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-[#0F172A] text-white font-bold text-xs flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr(Auth::user()->name ?? 'HE', 0, 2)) }}
                        </div>
                    </div>
                </div>
            </header>

            {{-- KONTEN --}}
            <main class="flex-1 px-4 sm:px-6 lg:px-10 py-5 sm:py-7">

                {{-- Filter Bar --}}
                <div class="bg-white border border-[#E2E8F0] rounded-xl shadow-xs p-4 mb-5">
                    <form method="GET" action="{{ route('petugas.riwayat') }}" class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[120px]">
                            <label class="text-xs font-bold text-[#64748B] uppercase tracking-wide block mb-1.5">Bulan</label>
                            <select name="bulan" class="w-full px-3 py-2.5 rounded-lg border border-[#E2E8F0] text-sm text-[#1E293B] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/30 focus:border-[#2563EB]">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $filterBulan == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="flex-1 min-w-[100px]">
                            <label class="text-xs font-bold text-[#64748B] uppercase tracking-wide block mb-1.5">Tahun</label>
                            <select name="tahun" class="w-full px-3 py-2.5 rounded-lg border border-[#E2E8F0] text-sm text-[#1E293B] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/30 focus:border-[#2563EB]">
                                @for ($y = now()->year; $y >= now()->year - 2; $y--)
                                    <option value="{{ $y }}" {{ $filterTahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="flex-1 min-w-[140px]">
                            <label class="text-xs font-bold text-[#64748B] uppercase tracking-wide block mb-1.5">Status</label>
                            <select name="status" class="w-full px-3 py-2.5 rounded-lg border border-[#E2E8F0] text-sm text-[#1E293B] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/30 focus:border-[#2563EB]">
                                <option value="semua" {{ $filterStatus === 'semua' ? 'selected' : '' }}>Semua Status</option>
                                <option value="valid" {{ $filterStatus === 'valid' ? 'selected' : '' }}>Valid</option>
                                <option value="ter_flag" {{ $filterStatus === 'ter_flag' ? 'selected' : '' }}>Butuh Persetujuan</option>
                                <option value="ditolak" {{ $filterStatus === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-semibold transition-colors whitespace-nowrap">
                            Filter
                        </button>
                    </form>
                </div>

                {{-- Tabel Riwayat --}}
                <div class="bg-white border border-[#E2E8F0] rounded-xl shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-[#F1F5F9] flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-[#0F172A]">Daftar Pencatatan</h2>
                            <p class="text-xs text-[#64748B] mt-0.5">Total {{ $riwayat->total() }} data ditemukan</p>
                        </div>
                        <a href="{{ route('petugas.pindai') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-semibold transition-colors shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Tambah Baru
                        </a>
                    </div>

                    @if($riwayat->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-[#F1F5F9] flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-[#CBD5E1]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-[#334155]">Belum ada data pencatatan</p>
                        <p class="text-xs text-[#94A3B8] mt-1">Tidak ada riwayat untuk periode dan filter yang dipilih.</p>
                    </div>
                    @else
                    {{-- Desktop Table --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#F1F5F9]">
                                    <th class="text-left px-5 py-3 text-xs font-bold text-[#64748B] uppercase tracking-wide">Unit</th>
                                    <th class="text-left px-5 py-3 text-xs font-bold text-[#64748B] uppercase tracking-wide">Pemilik</th>
                                    <th class="text-right px-5 py-3 text-xs font-bold text-[#64748B] uppercase tracking-wide">Stand Lalu</th>
                                    <th class="text-right px-5 py-3 text-xs font-bold text-[#64748B] uppercase tracking-wide">Stand Kini</th>
                                    <th class="text-right px-5 py-3 text-xs font-bold text-[#64748B] uppercase tracking-wide">Pemakaian</th>
                                    <th class="text-center px-5 py-3 text-xs font-bold text-[#64748B] uppercase tracking-wide">Status</th>
                                    <th class="text-left px-5 py-3 text-xs font-bold text-[#64748B] uppercase tracking-wide">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F1F5F9]">
                                @foreach ($riwayat as $r)
                                @php
                                    $pemakaian = $r->angka_meter - $r->angka_meter_bulanlalu;
                                    $statusClass = match($r->status_verifikasi) {
                                        'valid'   => 'bg-[#ECFDF5] text-[#059669] border-[#A7F3D0]',
                                        'ter_flag'=> 'bg-[#FFFBEB] text-[#D97706] border-[#FDE68A]',
                                        'ditolak' => 'bg-[#FEF2F2] text-[#DC2626] border-[#FECACA]',
                                        default   => 'bg-[#F1F5F9] text-[#475569] border-[#CBD5E1]',
                                    };
                                    $statusLabel = match($r->status_verifikasi) {
                                        'valid'   => 'Valid',
                                        'ter_flag'=> 'Butuh Persetujuan',
                                        'ditolak' => 'Ditolak',
                                        default   => 'Belum Dicatat',
                                    };
                                @endphp
                                <tr class="hover:bg-[#F8FAFC] transition-colors">
                                    <td class="px-5 py-3.5 font-semibold text-[#0F172A]">{{ optional($r->unit)->nomor_rumah ?? '—' }}</td>
                                    <td class="px-5 py-3.5 text-[#475569]">{{ optional($r->unit)->nama_pemilik ?? '—' }}</td>
                                    <td class="px-5 py-3.5 text-right font-mono text-[#334155]">{{ number_format($r->angka_meter_bulanlalu, 1) }}</td>
                                    <td class="px-5 py-3.5 text-right font-mono font-semibold text-[#0F172A]">{{ number_format($r->angka_meter, 1) }}</td>
                                    <td class="px-5 py-3.5 text-right font-mono font-bold {{ $pemakaian < 0 ? 'text-[#DC2626]' : 'text-[#059669]' }}">
                                        {{ $pemakaian < 0 ? '' : '+' }}{{ number_format($pemakaian, 1) }} m³
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-[#64748B]">{{ $r->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Card List --}}
                    <div class="sm:hidden divide-y divide-[#F1F5F9]">
                        @foreach ($riwayat as $r)
                        @php
                            $pemakaian = $r->angka_meter - $r->angka_meter_bulanlalu;
                            $statusClass = match($r->status_verifikasi) {
                                'valid'   => 'bg-[#ECFDF5] text-[#059669] border-[#A7F3D0]',
                                'ter_flag'=> 'bg-[#FFFBEB] text-[#D97706] border-[#FDE68A]',
                                'ditolak' => 'bg-[#FEF2F2] text-[#DC2626] border-[#FECACA]',
                                default   => 'bg-[#F1F5F9] text-[#475569] border-[#CBD5E1]',
                            };
                            $statusLabel = match($r->status_verifikasi) {
                                'valid'   => 'Valid',
                                'ter_flag'=> 'Butuh Persetujuan',
                                'ditolak' => 'Ditolak',
                                default   => 'Belum Dicatat',
                            };
                        @endphp
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="text-sm font-bold text-[#0F172A]">{{ optional($r->unit)->nomor_rumah ?? '—' }}</span>
                                    <span class="text-xs text-[#64748B] ml-1.5">{{ optional($r->unit)->nama_pemilik ?? '' }}</span>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border shrink-0 {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <div class="mt-2.5 flex gap-4 text-xs">
                                <div>
                                    <span class="text-[#94A3B8]">Stand: </span>
                                    <span class="font-semibold text-[#334155]">{{ number_format($r->angka_meter_bulanlalu, 1) }} → {{ number_format($r->angka_meter, 1) }} m³</span>
                                </div>
                                <div>
                                    <span class="text-[#94A3B8]">Pakai: </span>
                                    <span class="font-bold {{ $pemakaian < 0 ? 'text-[#DC2626]' : 'text-[#059669]' }}">{{ $pemakaian < 0 ? '' : '+' }}{{ number_format($pemakaian, 1) }} m³</span>
                                </div>
                            </div>
                            <p class="text-[10px] text-[#94A3B8] mt-1">{{ $r->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if ($riwayat->hasPages())
                    <div class="px-5 py-4 border-t border-[#F1F5F9]">
                        {{ $riwayat->appends(request()->query())->links() }}
                    </div>
                    @endif
                    @endif
                </div>

            </main>
        </div>
    </div>

</body>
</html>

