@extends('layouts.admin')

@section('title', 'Pengelolaan Air Terintegrasi Grand Sulawesi')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Periode Pencatatan & Penagihan: ' . $periodeStr)

@section('content')
<div class="space-y-5">

    {{-- ==================== BARIS 1: 4 KARTU STATISTIK (BENTUK ASLI DIPERTAHANKAN) ==================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- 1. Total Unit --}}
        <div class="bg-white border border-[#CBD5E1] rounded-xl p-5 sm:p-5.5 shadow-sm hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#0F172A] uppercase tracking-wider">TOTAL UNIT HUNIAN</span>
                    <svg class="w-6 h-6 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                    </svg>
                </div>
                <div class="mt-3.5 flex items-baseline gap-2">
                    <h3 class="text-3xl font-extrabold text-[#0F172A] font-mono tabular-nums tracking-tight leading-none">{{ number_format($totalUnit) }}</h3>
                    <span class="text-sm font-semibold text-[#475569]">Unit Rumah</span>
                </div>
            </div>
            <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                <span class="font-bold text-[#0F172A]">{{ $persenAktif }}% Aktif</span>
                <span class="font-semibold text-[#475569]">{{ number_format($unitAktif) }} Unit Dihuni</span>
            </div>
        </div>

        {{-- 2. Pembacaan Masuk --}}
        <div class="bg-white border border-[#CBD5E1] rounded-xl p-5 sm:p-5.5 shadow-sm hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#0F172A] uppercase tracking-wider">PEMBACAAN MASUK</span>
                    <svg class="w-6 h-6 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <div class="mt-3.5 flex items-baseline gap-2">
                    <h3 class="text-3xl font-extrabold text-[#0F172A] font-mono tabular-nums tracking-tight leading-none">{{ $totalBacaanBulanIni }}</h3>
                    <span class="text-sm font-semibold text-[#475569] font-mono tabular-nums">/ {{ $totalUnit }} Unit</span>
                </div>
            </div>
            <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                <span class="font-bold text-[#0F172A] font-mono tabular-nums">{{ $progresPembacaan }}% Selesai</span>
                <span class="font-semibold text-[#475569] font-mono tabular-nums">{{ $totalUnit - $totalBacaanBulanIni }} Sisa Unit</span>
            </div>
        </div>

        {{-- 3. Data Ter-flag (Anomali) --}}
        <div class="bg-white border border-[#CBD5E1] rounded-xl p-5 sm:p-5.5 shadow-sm hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#0F172A] uppercase tracking-wider">DATA TER-FLAG</span>
                    <svg class="w-5 h-5 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5"/>
                    </svg>
                </div>
                <div class="mt-3.5 flex items-baseline gap-2">
                    <h3 class="text-3xl font-extrabold text-[#0F172A] font-mono tabular-nums tracking-tight leading-none">{{ $totalTerflag }}</h3>
                    <span class="text-sm font-semibold text-[#475569]">Unit Anomali</span>
                </div>
            </div>
            <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                <span class="font-bold text-[#0F172A]">Perlu Ditinjau</span>
                <a href="{{ route('admin.terflag') }}" class="font-bold text-[#2563EB] hover:underline">Tinjau</a>
            </div>
        </div>

        {{-- 4. Tagihan Belum Lunas --}}
        <div class="bg-white border border-[#CBD5E1] rounded-xl p-5 sm:p-5.5 shadow-sm hover:shadow-md transition-all duration-200 card-lift flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#0F172A] uppercase tracking-wider">TAGIHAN TERTUNDA</span>
                    <svg class="w-6 h-6 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                    </svg>
                </div>
                <div class="mt-3.5 flex items-baseline gap-2">
                    <h3 class="text-3xl font-extrabold text-[#0F172A] font-mono tabular-nums tracking-tight leading-none">{{ $tagihanBelumLunasCount }}</h3>
                    <span class="text-sm font-semibold text-[#475569]">Tagihan Warga</span>
                </div>
            </div>
            <div class="mt-4 pt-3.5 border-t border-[#F1F5F9] flex items-center justify-between text-xs">
                <span class="font-bold text-[#0F172A] font-mono tabular-nums">Rp {{ number_format($nominalBelumLunas, 0, ',', '.') }}</span>
                <a href="{{ route('admin.tagihan') }}" class="font-bold text-[#2563EB] hover:underline">Kelola</a>
            </div>
        </div>

    </div>

    {{-- ==================== BARIS 2: DONUT CHART & WATER USAGE SPLINE (BENTUK ASLI DIPERTAHANKAN) ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- KIRI (5 KOLOM): Statistik Tagihan Bulanan (Donut + Progress Detail) --}}
        <div class="lg:col-span-5 bg-white border border-[#CBD5E1] rounded-xl p-6 shadow-sm card-lift flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#F1F5F9]">
                    <h2 class="text-sm font-bold text-[#0F172A]">Statistik Tagihan Bulanan</h2>
                    <span class="text-xs font-semibold text-[#334155]">Bulan Ini</span>
                </div>

                @php
                    $donutTotal = max(1, $tagihanLunasCount + $tagihanBelumLunasCount + $totalTerflag);
                    $fracNavy = $tagihanLunasCount / $donutTotal;
                    $fracBelum = $tagihanBelumLunasCount / $donutTotal;
                    $fracFlag = max(0, 1 - $fracNavy - $fracBelum);

                    $buildArc = function (float $startFrac, float $endFrac, float $radius): ?string {
                        $span = $endFrac - $startFrac;
                        if ($span <= 0.0001) {
                            return null;
                        }
                        if ($span >= 0.9999) {
                            $endFrac = $startFrac + 0.9999;
                        }
                        $a0 = ($startFrac * 2 * pi()) - (pi() / 2);
                        $a1 = ($endFrac * 2 * pi()) - (pi() / 2);
                        $x0 = round(50 + $radius * cos($a0), 3);
                        $y0 = round(50 + $radius * sin($a0), 3);
                        $x1 = round(50 + $radius * cos($a1), 3);
                        $y1 = round(50 + $radius * sin($a1), 3);
                        $largeArc = ($endFrac - $startFrac) > 0.5 ? 1 : 0;

                        return "M {$x0} {$y0} A {$radius} {$radius} 0 {$largeArc} 1 {$x1} {$y1}";
                    };

                    $arcNavy = $buildArc(0, $fracNavy, 39);
                    $arcBelum = $buildArc($fracNavy, $fracNavy + $fracBelum, 37);
                    $arcFlag = $buildArc($fracNavy + $fracBelum, 1.0, 37);

                    $seps = [];
                    if ($fracNavy > 0 && ($fracBelum > 0 || $fracFlag > 0)) {
                        $seps[] = ['angle' => -pi() / 2, 'delay' => '1.05s'];
                        $seps[] = ['angle' => ($fracNavy * 2 * pi()) - (pi() / 2), 'delay' => '0.50s'];
                    }
                    if ($fracBelum > 0 && $fracFlag > 0) {
                        $seps[] = ['angle' => (($fracNavy + $fracBelum) * 2 * pi()) - (pi() / 2), 'delay' => '0.92s'];
                    }
                @endphp
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6 py-2">
                    <div class="relative w-44 h-44 flex items-center justify-center flex-shrink-0">
                        <svg class="w-full h-full" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="37" fill="transparent" stroke="#F1F5F9" stroke-width="12"></circle>

                            {{-- 1. Hitam/Navy (Lunas) muncul pertama mulai jam 12 --}}
                            @if($arcNavy)
                                <path d="{{ $arcNavy }}" pathLength="100" fill="none" stroke="#0F172A" stroke-width="16"
                                      class="donut-arc-sweep" style="animation-duration: 0.55s; animation-delay: 0.05s;" />
                            @endif

                            {{-- 2. Biru (Belum Lunas) menyambung setelah hitam --}}
                            @if($arcBelum)
                                <path d="{{ $arcBelum }}" pathLength="100" fill="none" stroke="#2563EB" stroke-width="12"
                                      class="donut-arc-sweep" style="animation-duration: 0.45s; animation-delay: 0.52s;" />
                            @endif

                            {{-- 3. Abu-abu (Perlu Review) menyambung menutup hingga jam 12 --}}
                            @if($arcFlag)
                                <path d="{{ $arcFlag }}" pathLength="100" fill="none" stroke="#64748B" stroke-width="12"
                                      class="donut-arc-sweep" style="animation-duration: 0.25s; animation-delay: 0.92s;" />
                            @endif

                            {{-- Garis pemisah putih antar-segmen --}}
                            @foreach($seps as $sep)
                                <line x1="{{ round(50 + 29 * cos($sep['angle']), 2) }}"
                                      y1="{{ round(50 + 29 * sin($sep['angle']), 2) }}"
                                      x2="{{ round(50 + 49 * cos($sep['angle']), 2) }}"
                                      y2="{{ round(50 + 49 * sin($sep['angle']), 2) }}"
                                      stroke="#FFFFFF" stroke-width="2"
                                      class="donut-sep-in" style="animation-delay: {{ $sep['delay'] }};" />
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-2">
                            <span class="text-2xl sm:text-[26px] font-extrabold text-[#0F172A] font-mono tabular-nums tracking-tight leading-none">{{ $totalTagihanCount }}</span>
                            <span class="text-[10px] text-[#0F172A] font-bold uppercase tracking-wider mt-1 block leading-tight">Total Tagihan</span>
                        </div>
                    </div>

                    <div class="space-y-5 w-full sm:flex-1 pl-0 sm:pl-1">
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <div class="flex items-center gap-2 font-semibold text-[#0F172A]">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#0F172A]"></span>
                                    <span class="text-sm font-semibold">Lunas</span>
                                </div>
                                <span class="text-sm font-bold text-[#0F172A] font-mono tabular-nums">{{ $tagihanLunasCount }} <span class="text-xs font-semibold text-[#475569]">({{ $persenLunas }}%)</span></span>
                            </div>
                            <div class="w-full bg-[#E2E8F0] rounded-full h-2.5 overflow-hidden">
                                <div class="bg-[#0F172A] h-2.5 rounded-full" style="width: {{ $persenLunas }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <div class="flex items-center gap-2 font-semibold text-[#0F172A]">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#2563EB]"></span>
                                    <span class="text-sm font-semibold">Belum Lunas</span>
                                </div>
                                <span class="text-sm font-bold text-[#0F172A] font-mono tabular-nums">{{ $tagihanBelumLunasCount }} <span class="text-xs font-semibold text-[#475569]">({{ $persenBelumLunas }}%)</span></span>
                            </div>
                            <div class="w-full bg-[#E2E8F0] rounded-full h-2.5 overflow-hidden">
                                <div class="bg-[#2563EB] h-2.5 rounded-full" style="width: {{ $persenBelumLunas }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <div class="flex items-center gap-2 font-semibold text-[#0F172A]">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#64748B]"></span>
                                    <span class="text-sm font-semibold">Perlu Review</span>
                                </div>
                                <span class="text-sm font-bold text-[#0F172A] font-mono tabular-nums">{{ $totalTerflag }} <span class="text-xs font-semibold text-[#475569]">({{ $persenTerflag }}%)</span></span>
                            </div>
                            <div class="w-full bg-[#E2E8F0] rounded-full h-2.5 overflow-hidden">
                                <div class="bg-[#64748B] h-2.5 rounded-full" style="width: {{ $persenTerflag }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-[#F1F5F9] grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-[11px] text-[#0F172A] block font-bold uppercase tracking-wider">Total Nilai Tagihan</span>
                    <span class="text-base font-bold text-[#0F172A] font-mono tabular-nums mt-0.5 block">Rp {{ number_format($totalNilaiTagihan, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-[#0F172A] block font-bold uppercase tracking-wider">Efisiensi Koleksi</span>
                    <span class="text-base font-bold text-[#0F172A] font-mono tabular-nums mt-0.5 block">{{ $efisiensiKoleksi }}% <span class="text-xs font-sans font-semibold text-[#16A34A]">(Terverifikasi)</span></span>
                </div>
            </div>
        </div>

        {{-- KANAN (7 KOLOM): Water Analytics Line Chart --}}
        <div class="lg:col-span-7 bg-white border border-[#CBD5E1] rounded-xl p-6 shadow-sm card-lift flex flex-col justify-between">
            <div>
                <div class="mb-3 pb-3 border-b border-[#F1F5F9]">
                    <h2 class="text-sm font-bold text-[#0F172A]">Tren Pemakaian Air (m³)</h2>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <span class="text-[11px] text-[#0F172A] block font-bold uppercase tracking-wider">Volume Bulan Ini</span>
                        <span class="text-lg font-bold text-[#0F172A] font-mono tabular-nums mt-0.5 block">{{ number_format($volumeBulanIni, 0, ',', '.') }} <span class="text-xs font-medium text-[#475569]">m³</span></span>
                    </div>
                    <div>
                        <span class="text-[11px] text-[#0F172A] block font-bold uppercase tracking-wider">Rata-Rata Bulanan</span>
                        <span class="text-lg font-bold text-[#0F172A] font-mono tabular-nums mt-0.5 block">{{ number_format($rataRataBulanan, 1, ',', '.') }} <span class="text-xs font-medium text-[#475569]">m³</span></span>
                    </div>
                    <div>
                        <span class="text-[11px] text-[#0F172A] block font-bold uppercase tracking-wider">Total Volume {{ $currentYear }}</span>
                        <span class="text-lg font-bold text-[#0F172A] font-mono tabular-nums mt-0.5 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                            {{ number_format($totalVolumeTahunIni, 0, ',', '.') }} <span class="text-xs font-medium text-[#475569]">m³</span>
                        </span>
                    </div>
                </div>

                @php
                    $yMax = max(2000, (int) ($chartYMax ?? 10000));
                    $yBottom = 148;
                    $yTop = 16;
                    $ySpan = $yBottom - $yTop;

                    $points = [];
                    foreach ($trendPemakaian as $idx => $item) {
                        $x = ($idx * 60) + 30;
                        $ratio = min(1, max(0, $item['volume'] / $yMax));
                        $y = round($yBottom - ($ratio * $ySpan), 2);
                        $points[] = [
                            'x' => $x,
                            'y' => $y,
                            'bulan' => $item['bulan'],
                            'volume' => (int) $item['volume'],
                            'is_current' => (bool) ($item['is_current'] ?? false),
                            'has_data' => (bool) ($item['has_data'] ?? false),
                        ];
                    }

                    // Bangun path bezier datar pada bulan bernilai 0 dan mulus pada perubahan data
                    $linePath = '';
                    $areaPath = '';
                    if (count($points) > 0) {
                        $linePath = 'M '.$points[0]['x'].','.$points[0]['y'];
                        for ($i = 1; $i < count($points); $i++) {
                            $p0 = $points[$i - 1];
                            $p1 = $points[$i];
                            $dx = ($p1['x'] - $p0['x']) * 0.45;
                            $cp1x = round($p0['x'] + $dx, 2);
                            $cp2x = round($p1['x'] - $dx, 2);
                            $linePath .= " C {$cp1x},{$p0['y']} {$cp2x},{$p1['y']} {$p1['x']},{$p1['y']}";
                        }
                        $lastPt = $points[count($points) - 1];
                        $firstPt = $points[0];
                        $areaPath = $linePath." L {$lastPt['x']},{$yBottom} L {$firstPt['x']},{$yBottom} Z";
                    }

                    $activePoint = collect($points)->firstWhere('is_current', true) ?? $points[count($points) - 1];
                    $activeIndex = collect($points)->search(fn ($p) => $p['is_current']) ?: (count($points) - 1);
                    $tooltipLeftPct = min(88, max(12, round((($activeIndex + 0.5) / 12) * 100, 1)));
                @endphp

                <div class="relative h-52 pt-1">
                    {{-- Sumbu Y (5 Garis Referensi) --}}
                    <div class="absolute inset-0 bottom-10 flex flex-col justify-between pointer-events-none text-[11px] text-[#334155] font-mono font-semibold tabular-nums">
                        @for($step = 4; $step >= 0; $step--)
                            @php $tickVal = ($yMax / 4) * $step; @endphp
                            <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-0.5">
                                <span class="w-8 text-right">{{ $tickVal >= 1000 ? rtrim(rtrim(number_format($tickVal / 1000, 1, '.', ''), '0'), '.').'k' : (int) $tickVal }}</span>
                                <div class="flex-1"></div>
                            </div>
                        @endfor
                    </div>

                    {{-- Area Grafik SVG 12 Bulan --}}
                    <div class="absolute inset-0 pl-11 pr-1 pt-2 pb-10">
                        <svg class="w-full h-full overflow-visible" viewBox="0 0 720 156" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="curveGradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#2563EB" stop-opacity="0.22" />
                                    <stop offset="65%" stop-color="#3B82F6" stop-opacity="0.07" />
                                    <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>

                            {{-- Garis Grid Vertikal Per Bulan --}}
                            @foreach($points as $pt)
                                <line x1="{{ $pt['x'] }}" y1="{{ $yTop }}" x2="{{ $pt['x'] }}" y2="{{ $yBottom }}"
                                      stroke="{{ $pt['is_current'] ? '#2563EB' : '#E2E8F0' }}"
                                      stroke-width="{{ $pt['is_current'] ? '1.5' : '1' }}"
                                      stroke-dasharray="3,3"
                                      opacity="{{ $pt['is_current'] ? '0.65' : '0.9' }}" />
                            @endforeach

                            {{-- Area Fill --}}
                            @if($areaPath !== '')
                                <path class="spline-area-fill" d="{{ $areaPath }}" fill="url(#curveGradient)" />
                                <path class="spline-line-draw" pathLength="1000" d="{{ $linePath }}"
                                      fill="none" stroke="#2563EB" stroke-width="2.5"
                                      stroke-linecap="round" stroke-linejoin="round" />
                            @endif

                            {{-- Titik Data Tiap Bulan --}}
                            @foreach($points as $idx => $pt)
                                <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}"
                                        r="{{ $pt['is_current'] ? '5.5' : ($pt['has_data'] ? '4' : '3') }}"
                                        fill="#FFFFFF"
                                        stroke="{{ $pt['has_data'] || $pt['is_current'] ? '#2563EB' : '#94A3B8' }}"
                                        stroke-width="{{ $pt['is_current'] ? '3' : '2' }}"
                                        class="spline-node"
                                        style="animation-delay: {{ number_format(0.08 + $idx * 0.08, 2, '.', '') }}s;">
                                    <title>{{ $pt['bulan'] }}: {{ number_format($pt['volume'], 0, ',', '.') }} m³</title>
                                </circle>
                            @endforeach
                        </svg>

                        {{-- Tooltip Bulan Aktif --}}
                        <div class="absolute -top-1 -translate-y-full -translate-x-1/2 spline-tooltip pointer-events-none"
                             style="left: {{ $tooltipLeftPct }}%;">
                            <div class="bg-[#0F172A] text-white px-2.5 py-1 rounded-lg text-xs font-mono font-bold shadow-md flex items-center gap-1.5 border border-[#334155] tabular-nums whitespace-nowrap">
                                <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
                                <span>{{ number_format($activePoint['volume'], 0, ',', '.') }} m³</span>
                            </div>
                        </div>
                    </div>

                    {{-- Label 12 Bulan (Jan - Des) di Sumbu X --}}
                    <div class="absolute bottom-0 inset-x-0 pl-11 pr-1 grid grid-cols-12 text-center">
                        @foreach($points as $pt)
                            @php
                                $volShort = $pt['volume'] === 0
                                    ? '0'
                                    : ($pt['volume'] >= 1000
                                        ? rtrim(rtrim(number_format($pt['volume'] / 1000, 1, '.', ''), '0'), '.').'k'
                                        : (string) $pt['volume']);
                            @endphp
                            <div class="min-w-0">
                                <span class="block text-[11px] leading-tight {{ $pt['is_current'] ? 'font-bold text-[#2563EB] underline decoration-2 underline-offset-4' : ($pt['has_data'] ? 'font-bold text-[#0F172A]' : 'font-semibold text-[#94A3B8]') }}">
                                    {{ $pt['bulan'] }}
                                </span>
                                <span class="block text-[10px] font-mono tabular-nums mt-0.5 {{ $pt['is_current'] ? 'font-bold text-[#2563EB]' : ($pt['has_data'] ? 'font-semibold text-[#475569]' : 'font-medium text-[#CBD5E1]') }}">
                                    {{ $volShort }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-[#F1F5F9] flex items-center gap-4 text-xs text-[#334155]">
                <span class="flex items-center gap-1.5 font-medium"><span class="w-2 h-2 rounded-full bg-[#2563EB]"></span> Volume Terpakai</span>
                <span class="flex items-center gap-1.5 font-medium"><span class="w-2 h-2 rounded-full bg-[#16A34A]"></span> Batas Normal (&lt;{{ rtrim(rtrim(number_format($yMax / 1000, 1, '.', ''), '0'), '.') }}k m³)</span>
            </div>
        </div>

    </div>

    {{-- ==================== BARIS 3: TABEL RECENT INVOICES / DATA TERBARU (BENTUK ASLI DIPERTAHANKAN) ==================== --}}
    <div class="bg-white border border-[#CBD5E1] rounded-xl p-6 shadow-sm card-lift">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="text-sm font-bold text-[#0F172A]">Data Pembacaan & Tagihan Terbaru</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.tagihan') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0F172A] hover:bg-[#1E293B] text-white text-xs font-normal transition-colors shadow-xs">
                    <span>Lihat Semua</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto -mx-6">
            <table class="w-full text-center text-xs">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-[#CBD5E1] text-[#0F172A] font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6 text-center w-14 whitespace-nowrap">No</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">ID Unit</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Nama Pelanggan</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Nomor Meteran</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Tanggal Rekam</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Status</th>
                        <th class="py-3.5 px-6 text-center whitespace-nowrap">Total Tagihan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9]">
                    @forelse($recentTagihans as $index => $t)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="py-3.5 px-6 text-center text-[#475569] font-mono text-xs font-semibold tabular-nums whitespace-nowrap">{{ $index + 1 }}</td>
                        <td class="py-3.5 px-4 text-center font-semibold text-[#0F172A] font-mono text-xs tabular-nums whitespace-nowrap">#{{ $t->unit->nomor_rumah ?? '—' }}</td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($t->unit->nama_pemilik ?? 'W', 0, 2)) }}
                                </div>
                                <span class="font-semibold text-xs text-[#0F172A]">{{ $t->unit->nama_pemilik ?? 'Warga' }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center text-xs text-[#334155] font-mono font-medium tabular-nums whitespace-nowrap">MTR-{{ str_pad($t->unit->id ?? 1, 4, '0', STR_PAD_LEFT) }}-{{ $t->unit->blok?->nama_blok ? substr($t->unit->blok->nama_blok, -1) : 'A' }}</td>
                        <td class="py-3.5 px-4 text-center text-xs text-[#334155] font-mono font-medium tabular-nums whitespace-nowrap">{{ $t->created_at ? $t->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            @if($t->status_bayar === 'lunas')
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-md bg-[#16A34A] text-white font-bold text-[11px] shadow-xs">
                                    Lunas
                                </span>
                            @elseif($t->status_bayar === 'sebagian')
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-md bg-[#EFF6FF] text-[#1D4ED8] font-bold text-[11px] border border-[#93C5FD]">
                                    Sebagian
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-md bg-[#FEF2F2] text-[#B91C1C] font-bold text-[11px] border border-[#FCA5A5]">
                                    Belum Dibayar
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 text-center font-semibold text-xs text-[#0F172A] font-mono tabular-nums whitespace-nowrap">Rp {{ number_format($t->total_tagihan, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-xs text-[#64748B]">Belum ada data tagihan tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
