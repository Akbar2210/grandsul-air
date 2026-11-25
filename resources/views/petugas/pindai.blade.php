<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pindai Meter — Pengelolaan Air Terintegrasi Grand Sulawesi</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif !important;
            background-color: #E2E8F0;
            color: #0F172A;
        }

        /* Viewfinder Scanner Laser Animation */
        @keyframes scanLaser {
            0%   { top: 12%; opacity: 0; }
            25%  { opacity: 1; }
            75%  { opacity: 1; }
            100% { top: 88%; opacity: 0; }
        }
        .ocr-scan-line {
            position: absolute;
            left: 8%;
            right: 8%;
            height: 2px;
            background: #2563EB;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.7);
            animation: scanLaser 2s linear infinite;
        }

        .meter-digits {
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.05em;
        }

        /* Custom scrollbar for horizontal chip bars */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-[#E2E8F0] font-sans text-[#0F172A] antialiased min-h-screen flex justify-center petugas-app">

    {{-- ==================== CONTAINER MOBILE WEB APP (MAX 430PX) ==================== --}}
    <div class="w-full max-w-[430px] min-h-screen bg-[#F1F5F9] flex flex-col border-x border-[#CBD5E1] shadow-sm pb-8" x-data="pindaiMeterApp()">

        @include('petugas.partials.topbar', ['title' => 'Pindai Meter', 'active' => 'pindai'])

        <div class="flex-1 flex flex-col min-w-0 p-3 space-y-3">

            {{-- ==================== 1. PILIHAN BLOK (SEGMENTED TABS) ==================== --}}
            <div class="bg-white border border-[#CBD5E1] rounded-2xl p-2 shadow-xs">
                <div class="text-[10px] font-bold text-[#64748B] uppercase tracking-wider px-1.5 mb-1.5 flex items-center justify-between">
                    <span>Pilih Blok Hunian</span>
                    <span class="text-[#2563EB] font-semibold">{{ $bloks->count() }} Blok Aktif</span>
                </div>
                <div class="grid grid-cols-6 gap-1">
                    @foreach($bloks as $b)
                    @php $isBlokActive = ($activeBlok && $activeBlok->id === $b->id); @endphp
                    <a
                        href="{{ route('petugas.pindai', ['blok_id' => $b->id]) }}"
                        class="h-9 rounded-xl flex flex-col items-center justify-center text-xs font-bold transition-all text-center {{ $isBlokActive ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-[#F8FAFC] text-[#334155] hover:bg-[#E2E8F0] border border-[#E2E8F0]' }}"
                    >
                        <span>{{ str_replace('Blok ', '', $b->nama_blok) }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- ==================== 2. KARTU PROGRES BLOK & FILTER CEPAT ==================== --}}
            <div class="bg-white border border-[#CBD5E1] rounded-2xl p-3 shadow-xs space-y-2.5">
                <div class="flex items-center justify-between text-xs">
                    <div>
                        <span class="font-extrabold text-[#0F172A] text-sm">{{ $activeBlok?->nama_blok ?? 'Blok A' }}</span>
                        <span class="text-[11px] text-[#64748B] ml-1.5 font-medium">
                            {{ $activeBlokStat['tercatat'] }} dari {{ $activeBlokStat['total'] }} unit selesai
                        </span>
                    </div>
                    <span class="font-mono font-bold text-xs px-2 py-0.5 rounded-md {{ $activeBlokStat['persen'] >= 100 ? 'bg-[#DCFCE7] text-[#15803D]' : 'bg-[#EFF6FF] text-[#1D4ED8]' }}">
                        {{ $activeBlokStat['persen'] }}%
                    </span>
                </div>

                {{-- Progress Bar --}}
                <div class="w-full h-2 bg-[#E2E8F0] rounded-full overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        style="width: {{ $activeBlokStat['persen'] }}%; background-color: {{ $activeBlokStat['persen'] >= 100 ? '#16A34A' : '#2563EB' }};"
                    ></div>
                </div>

                {{-- Filter & Search Box --}}
                <div class="pt-1 space-y-2">
                    <div class="flex items-center gap-1.5 text-[11px]">
                        <button
                            type="button"
                            @click="filterStatus = 'all'; applyFilter()"
                            class="px-2.5 py-1 rounded-lg font-semibold transition-colors cursor-pointer"
                            :class="filterStatus === 'all' ? 'bg-[#0F172A] text-white' : 'bg-[#F1F5F9] text-[#64748B] hover:bg-[#E2E8F0]'"
                        >
                            Semua ({{ $activeBlokStat['total'] }})
                        </button>
                        <button
                            type="button"
                            @click="filterStatus = 'belum'; applyFilter()"
                            class="px-2.5 py-1 rounded-lg font-semibold transition-colors cursor-pointer"
                            :class="filterStatus === 'belum' ? 'bg-[#2563EB] text-white' : 'bg-[#F1F5F9] text-[#64748B] hover:bg-[#E2E8F0]'"
                        >
                            Belum Dicatat ({{ $activeBlokStat['belum'] }})
                        </button>
                        @if($activeBlokStat['ditolak'] > 0)
                        <button
                            type="button"
                            @click="filterStatus = 'ditolak'; applyFilter()"
                            class="px-2.5 py-1 rounded-lg font-semibold transition-colors cursor-pointer bg-[#FEF2F2] text-[#DC2626] border border-[#FCA5A5]"
                        >
                            Ditolak ({{ $activeBlokStat['ditolak'] }})
                        </button>
                        @endif
                    </div>

                    <div class="relative">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @input.debounce.200ms="applyFilter()"
                            placeholder="Cari nomor unit / nama warga..."
                            class="w-full h-8 pl-8 pr-3 text-xs border border-[#CBD5E1] rounded-xl text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] bg-[#F8FAFC]"
                        />
                        <svg class="w-3.5 h-3.5 text-[#94A3B8] absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </div>
                </div>

                {{-- Horizontal Unit Selector (Urut Nomor Rumah) --}}
                <div class="pt-1">
                    <p class="text-[10px] font-bold text-[#64748B] uppercase tracking-wider mb-1.5">
                        Pilih Unit Rumah (<span x-text="filteredUnits.length"></span> unit):
                    </p>
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
                        <template x-for="u in filteredUnits" :key="u.id">
                            <button
                                type="button"
                                @click="selectUnit(u)"
                                class="px-2.5 py-1.5 rounded-xl text-xs font-mono font-bold shrink-0 border transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs"
                                :class="{
                                    'bg-[#0F172A] text-white border-[#0F172A] ring-2 ring-[#0F172A]/20': selectedUnit?.id === u.id,
                                    'bg-white text-[#0F172A] border-[#CBD5E1] hover:bg-[#F8FAFC]': selectedUnit?.id !== u.id && u.status === 'belum_dicatat',
                                    'bg-[#F0FDF4] text-[#15803D] border-[#BBF7D0]': selectedUnit?.id !== u.id && (u.status === 'valid' || u.status === 'ter_flag'),
                                    'bg-[#FEF2F2] text-[#DC2626] border-[#FECACA]': selectedUnit?.id !== u.id && u.status === 'ditolak',
                                }"
                            >
                                <span x-text="u.nomor_rumah"></span>
                                <span
                                    class="w-1.5 h-1.5 rounded-full shrink-0"
                                    :class="{
                                        'bg-[#22C55E]': u.status === 'valid' || u.status === 'ter_flag',
                                        'bg-[#EF4444]': u.status === 'ditolak',
                                        'bg-[#94A3B8]': u.status === 'belum_dicatat'
                                    }"
                                ></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- ==================== BANNER SUKSES & TOMBOL LANJUT KE UNIT BERIKUTNYA ==================== --}}
            @if(session('success'))
            <div class="p-3.5 bg-[#F0FDF4] border border-[#BBF7D0] rounded-2xl shadow-xs space-y-2">
                <div class="flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-[#16A34A] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-[#15803D]">{{ session('success') }}</p>
                        <p class="text-[11px] text-[#166534] mt-0.5">Data telah tercatat dan tersimpan di database.</p>
                    </div>
                </div>

                @if(session('next_unit_id'))
                <div class="pt-1">
                    <a
                        href="{{ route('petugas.pindai', ['blok_id' => $activeBlok?->id, 'unit_id' => session('next_unit_id')]) }}"
                        class="w-full py-2.5 px-3 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 shadow-xs transition-colors"
                    >
                        <span>Lanjut ke Unit Berikutnya: <strong>{{ session('next_unit_nomor') }}</strong> ({{ session('next_unit_nama') }})</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>
                @else
                <div class="p-2 bg-white/80 rounded-xl text-center text-xs text-[#15803D] font-bold">
                    🎉 Seluruh unit di blok ini telah selesai dicatat!
                </div>
                @endif
            </div>
            @endif

            {{-- Flash Error --}}
            @if(session('error'))
            <div class="p-3 bg-[#FEF2F2] border border-[#FECACA] rounded-2xl shadow-xs text-xs text-[#B91C1C] flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-[#DC2626]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            {{-- ==================== 3. KARTU UNIT AKTIF & STATUS GPS ==================== --}}
            <div class="bg-white border border-[#CBD5E1] rounded-2xl p-3.5 shadow-xs space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 bg-[#0F172A] text-white font-mono font-extrabold text-sm rounded-lg" x-text="selectedUnit?.nomor_rumah ?? '—'"></span>
                            <h2 class="text-sm font-bold text-[#0F172A] truncate" x-text="selectedUnit?.nama_pemilik ?? 'Pilih Unit'"></h2>
                        </div>
                        <p class="text-[11px] text-[#64748B] mt-1">
                            Blok: <strong class="text-[#0F172A]" x-text="selectedUnit?.blok_nama ?? '—'"></strong>
                            • Stand Lalu: <strong class="text-[#0F172A] font-mono" x-text="(selectedUnit?.angka_lalu ?? 0) + ' m³'"></strong>
                        </p>
                    </div>

                    <div class="text-right shrink-0">
                        <template x-if="selectedUnit?.status === 'valid' || selectedUnit?.status === 'ter_flag'">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#DCFCE7] text-[#15803D] text-[10px] font-bold border border-[#86EFAC]">
                                Sudah Dicatat
                            </span>
                        </template>
                        <template x-if="selectedUnit?.status === 'ditolak'">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#FEF2F2] text-[#DC2626] text-[10px] font-bold border border-[#FCA5A5]">
                                Perlu Foto Ulang
                            </span>
                        </template>
                        <template x-if="selectedUnit?.status === 'belum_dicatat'">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#F1F5F9] text-[#64748B] text-[10px] font-bold border border-[#CBD5E1]">
                                Belum Dicatat
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Alert Khusus Jika Ditolak Admin --}}
                <div x-show="selectedUnit?.status === 'ditolak' && selectedUnit?.alasan_ditolak" class="p-2.5 bg-[#FFFBEB] border border-[#FDE68A] rounded-xl text-xs text-[#92400E] space-y-1">
                    <p class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#D97706]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        <span>Ditolak Admin Sebelumnya:</span>
                    </p>
                    <p class="text-[11px] leading-relaxed pl-5 font-medium" x-text="selectedUnit?.alasan_ditolak"></p>
                    <p class="text-[10px] text-[#B45309] pl-5 italic">Silakan ambil foto ulang atau koreksi angka meteran yang tepat.</p>
                </div>

                {{-- Status Validasi GPS Unit (Radius 50m) --}}
                <div
                    class="p-2.5 rounded-xl border border-l-[3px] text-xs transition-colors"
                    :class="{
                        'bg-[#F0FDF4] border-[#BBF7D0] border-l-[#16A34A] text-[#15803D]': isGpsValid && !gpsUnverified,
                        'bg-[#FFFBEB] border-[#FDE68A] border-l-[#D97706] text-[#92400E]': isGpsValid && gpsUnverified,
                        'bg-[#FEF2F2] border-[#FECACA] border-l-[#DC2626] text-[#B91C1C]': !isGpsValid
                    }"
                >
                    <div class="flex items-start gap-2">
                        <div class="w-4 h-4 shrink-0 mt-0.5">
                            <template x-if="isGpsValid && !gpsUnverified">
                                <svg class="w-4 h-4 text-[#16A34A]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            </template>
                            <template x-if="isGpsValid && gpsUnverified">
                                <svg class="w-4 h-4 text-[#D97706]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.007H12v-.007z"/></svg>
                            </template>
                            <template x-if="!isGpsValid">
                                <svg class="w-4 h-4 text-[#DC2626]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-[11px]" x-text="gpsTitle"></p>
                            <p class="text-[10px] leading-tight mt-0.5 opacity-90" x-text="gpsStatusMessage"></p>
                        </div>
                    </div>

                    {{-- Tombol Cek Ulang & Simulasi GPS --}}
                    <div class="mt-2 pt-2 border-t border-black/5 flex items-center justify-between gap-2 text-[10px]">
                        <button
                            type="button"
                            @click="checkGpsLocation()"
                            class="px-2.5 py-1 bg-white border border-[#CBD5E1] rounded-lg font-semibold text-[#0F172A] hover:bg-[#F8FAFC] cursor-pointer"
                        >
                            Perbarui GPS HP
                        </button>

                        <button
                            type="button"
                            @click="useSimulatedUnitGps()"
                            title="Simulasi lokasi di depan rumah unit terpilih"
                            class="px-2.5 py-1 bg-[#0F172A] text-white rounded-lg font-semibold hover:bg-[#1E293B] cursor-pointer"
                        >
                            Simulasi Tepat di Unit
                        </button>
                    </div>
                </div>
            </div>

            {{-- ==================== 4. FORM PENCATATAN & KAMERA ==================== --}}
            <form
                method="POST"
                action="{{ route('petugas.pindai.store') }}"
                enctype="multipart/form-data"
                id="form-pindai-meter"
                @submit.prevent="submitForm($el)"
                class="space-y-3"
            >
                @csrf
                <input type="hidden" name="unit_id" :value="selectedUnit?.id">
                <input type="hidden" name="angka_meter" :value="angkaMeter">
                <input type="hidden" name="latitude" :value="latitude">
                <input type="hidden" name="longitude" :value="longitude">
                <input type="hidden" name="confidence_score" :value="confidenceScore">
                <input type="hidden" name="is_ocr_corrected" :value="isOcrCorrected ? '1' : '0'">
                <input type="file" name="foto_meter" id="foto_meter_hidden" class="hidden" accept="image/*">
                <input type="file" id="fallback_camera_input" accept="image/*" capture="environment" class="hidden" @change="handleImageFileSelected($event)">
                <input type="file" id="gallery_upload_input" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" @change="handleImageFileSelected($event)">

                {{-- Viewfinder Kamera --}}
                <div class="relative w-full h-[200px] bg-[#0B1120] overflow-hidden flex flex-col items-center justify-center border border-[#1E293B] rounded-2xl shadow-xs">

                    {{-- Target Corners --}}
                    <div class="absolute inset-3 pointer-events-none z-20 flex flex-col justify-between">
                        <div class="flex justify-between">
                            <div class="w-4 h-4 border-t-2 border-l-2 border-[#60A5FA]"></div>
                            <div class="w-4 h-4 border-t-2 border-r-2 border-[#60A5FA]"></div>
                        </div>
                        <div class="flex justify-between">
                            <div class="w-4 h-4 border-b-2 border-l-2 border-[#60A5FA]"></div>
                            <div class="w-4 h-4 border-b-2 border-r-2 border-[#60A5FA]"></div>
                        </div>
                    </div>

                    {{-- Live Video Stream --}}
                    <video
                        id="camera_video"
                        playsinline
                        autoplay
                        muted
                        class="w-full h-full object-cover"
                        x-show="cameraStreamActive && !photoPreview"
                    ></video>

                    <canvas id="camera_canvas" class="hidden"></canvas>

                    {{-- Viewfinder Controls (Jika Belum Memotret) --}}
                    <div x-show="!cameraStreamActive && !photoPreview" class="text-center z-10 px-4">
                        <template x-if="isGpsValid">
                            <div class="flex items-center justify-center gap-3">
                                <button
                                    type="button"
                                    @click="startLiveCamera()"
                                    class="px-4 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold rounded-xl flex items-center gap-2 cursor-pointer shadow-sm transition-transform active:scale-95"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                                    <span>Buka Kamera</span>
                                </button>
                                <button
                                    type="button"
                                    @click="document.getElementById('gallery_upload_input').click()"
                                    class="px-3.5 py-2.5 bg-[#1E293B] hover:bg-[#334155] text-white border border-[#475569] text-xs font-bold rounded-xl flex items-center gap-1.5 cursor-pointer shadow-sm transition-transform active:scale-95"
                                >
                                    <svg class="w-4 h-4 text-[#94A3B8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Upload Gambar</span>
                                </button>
                            </div>
                        </template>

                        <template x-if="!isGpsValid">
                            <div class="p-3 bg-[#FEF2F2]/10 border border-[#EF4444]/40 rounded-xl text-center max-w-[280px]">
                                <p class="text-xs font-bold text-[#F87171]">Kamera & OCR Dinonaktifkan</p>
                                <p class="text-[10px] text-[#CBD5E1] mt-0.5 leading-tight">Posisi GPS melebihi 50 m dari titik unit rumah ini.</p>
                            </div>
                        </template>
                    </div>

                    {{-- Shutter Button saat Live Stream --}}
                    <div x-show="cameraStreamActive && !photoPreview" class="absolute bottom-3 left-0 right-0 z-30 flex items-center justify-center">
                        <button
                            type="button"
                            @click="captureFromStream()"
                            class="px-5 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-xl shadow-lg flex items-center gap-2 cursor-pointer active:scale-95"
                        >
                            <span>Ambil Foto Meter</span>
                        </button>
                    </div>

                    {{-- Preview Foto + OCR --}}
                    <div x-show="photoPreview" class="absolute inset-0 z-10" style="display: none;">
                        <img :src="photoPreview" alt="Foto Meter Air" class="w-full h-full object-cover">
                        <div x-show="isOcrProcessing" class="ocr-scan-line"></div>
                        <div class="absolute inset-0 bg-black/20"></div>

                        {{-- Indikator Scan --}}
                        <div x-show="isOcrProcessing" class="absolute bottom-2.5 left-2.5 right-2.5 z-30 bg-[#0B1120]/90 px-3 py-2 border border-[#2563EB] rounded-xl flex items-center justify-between text-xs text-white font-semibold">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#38BDF8] animate-ping"></span>
                                <span>Menganalisis meter air (<span x-text="ocrProvider"></span>)...</span>
                            </span>
                            <svg class="w-4 h-4 animate-spin text-[#60A5FA]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </div>

                        {{-- Tombol Foto Ulang --}}
                        <div class="absolute top-2.5 right-2.5 z-30">
                            <button
                                type="button"
                                @click="retakeCamera()"
                                class="px-2.5 py-1 bg-black/75 hover:bg-black text-white text-[11px] font-semibold border border-white/20 rounded-lg flex items-center gap-1 cursor-pointer"
                            >
                                <span>Foto Ulang</span>
                            </button>
                        </div>

                        {{-- Hasil Deteksi OCR / AI Saran --}}
                        <div x-show="ocrDone" class="absolute bottom-2.5 left-2.5 right-2.5 z-30 bg-[#0F172A]/95 px-3 py-2 border border-[#334155] rounded-xl flex items-center justify-between gap-2 text-white">
                            <div class="min-w-0">
                                <p class="text-[10px] text-[#94A3B8] flex items-center gap-1">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>Hasil Analisis AI:</span>
                                    <span x-show="confidenceScore > 0" class="text-emerald-400 font-semibold" x-text="'(' + Math.round(confidenceScore) + '%)'"></span>
                                </p>
                                <p class="text-sm font-bold font-mono text-[#60A5FA]" x-text="ocrSuggestedDigits !== null ? (ocrSuggestedDigits + ' m³') : (ocrMessage || 'Tidak terbaca jelas, silakan ketik manual')"></p>
                            </div>
                            <template x-if="ocrSuggestedDigits !== null">
                                <span class="px-2.5 py-1 bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-semibold rounded-lg flex items-center gap-1 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>Otomatis Terisi</span>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- ==================== 5. INPUT ANGKA METER & PEMAKAIAN ==================== --}}
                <div class="bg-white border border-[#CBD5E1] rounded-2xl p-3.5 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-[#0F172A]">Konfirmasi Angka Meter (m³)</span>
                        <span class="text-[10px] text-[#64748B]">Bisa diketik manual</span>
                    </div>

                    {{-- Counter Input Angka --}}
                    <div class="flex items-stretch gap-2">
                        <button
                            type="button"
                            @click="decrementMeter()"
                            class="w-12 bg-[#F8FAFC] hover:bg-[#E2E8F0] text-[#0F172A] text-xl font-bold flex items-center justify-center border border-[#CBD5E1] rounded-xl cursor-pointer"
                        >−</button>

                        <div class="relative flex-1">
                            <input
                                id="angka_meter_input"
                                type="number"
                                step="1"
                                min="0"
                                inputmode="numeric"
                                x-model="angkaMeter"
                                @input="isOcrCorrected = true; hitungPemakaian()"
                                placeholder="0"
                                class="w-full h-12 text-center px-8 border border-[#CBD5E1] rounded-xl text-2xl font-black font-mono text-[#0F172A] meter-digits focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] bg-[#F8FAFC]"
                            />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-[#64748B]">m³</span>
                        </div>

                        <button
                            type="button"
                            @click="incrementMeter()"
                            class="w-12 bg-[#F8FAFC] hover:bg-[#E2E8F0] text-[#0F172A] text-xl font-bold flex items-center justify-center border border-[#CBD5E1] rounded-xl cursor-pointer"
                        >+</button>
                    </div>

                    {{-- Komparasi Stand Lalu vs Sekarang --}}
                    <div class="grid grid-cols-3 border border-[#CBD5E1] rounded-xl text-center bg-[#F8FAFC] overflow-hidden text-xs py-2 divide-x divide-[#CBD5E1]">
                        <div>
                            <span class="text-[10px] text-[#64748B] block">Stand Lalu</span>
                            <span class="font-mono font-bold text-[#0F172A]" x-text="(selectedUnit?.angka_lalu ?? 0) + ' m³'"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#64748B] block">Stand Sekarang</span>
                            <span class="font-mono font-bold text-[#0F172A]" x-text="angkaMeter ? (angkaMeter + ' m³') : '—'"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#64748B] block">Pemakaian</span>
                            <span class="font-mono font-black" :class="pemakaian < 0 ? 'text-[#DC2626]' : 'text-[#2563EB]'" x-text="(pemakaian ?? 0) + ' m³'"></span>
                        </div>
                    </div>

                    {{-- Peringatan Jika Angka Mundur --}}
                    <div x-show="pemakaian < 0" class="p-2 bg-[#FEF2F2] border border-[#FCA5A5] rounded-xl text-xs text-[#B91C1C] flex items-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0 text-[#DC2626]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.007H12v-.007z"/></svg>
                        <span>Peringatan: Stand sekarang lebih kecil dari bulan lalu (meter mundur).</span>
                    </div>
                </div>

                {{-- ==================== 6. TOMBOL SIMPAN PENCATATAN ==================== --}}
                <div>
                    <button
                        type="submit"
                        :disabled="!selectedUnit || !angkaMeter || isSubmitting || !isGpsValid"
                        class="w-full py-3.5 px-4 bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-[0.99] disabled:bg-[#CBD5E1] disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all cursor-pointer"
                    >
                        <svg x-show="!isSubmitting" class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        <svg x-show="isSubmitting" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24" style="display:none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isSubmitting ? 'Menyimpan Pencatatan...' : (!isGpsValid ? 'Terkunci — Di Luar Radius 50m' : (!angkaMeter ? 'Masukkan Angka Meteran' : 'Simpan Pembacaan Meter'))"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- ==================== JAVASCRIPT STATE & INTERAKSI (ALPINE.JS) ==================== --}}
    <script>
    const allUnitsInBlok = @json($unitsMapped ?? []);
    const initialUnitData = @json($selectedUnitMapped ?? null);
    const geofenceConfig = @json($geofenceConfig ?? ['center_lat' => -4.007468, 'center_lng' => 119.661131, 'radius_meters' => 700.0]);

    function pindaiMeterApp() {
        return {
            units: allUnitsInBlok,
            filteredUnits: allUnitsInBlok,
            filterStatus: 'all',
            searchQuery: '',
            selectedUnit: initialUnitData || (allUnitsInBlok.length > 0 ? allUnitsInBlok[0] : null),

            // Kamera & Media
            cameraStreamActive: false,
            mediaStream: null,
            photoPreview: null,
            photoFile: null,

            // OCR & AI Vision
            isOcrProcessing: false,
            ocrDone: false,
            ocrSuggestedDigits: null,
            confidenceScore: 95.0,
            isOcrCorrected: false,
            ocrProvider: '{{ $activeAiProvider ?? 'Gemini AI' }}',
            ocrMessage: '',

            // Meter Values
            angkaMeter: initialUnitData?.angka_sekarang ? initialUnitData.angka_sekarang.toString() : '',
            pemakaian: 0,
            isSubmitting: false,

            // GPS
            latitude: '',
            longitude: '',
            isGpsChecking: true,
            isGpsValid: true,
            gpsUnverified: false,
            gpsDistanceMeters: 0,
            gpsTitle: 'Mendeteksi Lokasi GPS...',
            gpsStatusMessage: 'Menghubungkan ke sensor lokasi HP...',

            init() {
                this.applyFilter();
                this.checkGpsLocation();
                this.hitungPemakaian();
            },

            calculateHaversineMeters(lat1, lng1, lat2, lng2) {
                const R = 6371000.0;
                const toRad = (deg) => (deg * Math.PI) / 180.0;
                const dLat = toRad(lat2 - lat1);
                const dLng = toRad(lng2 - lng1);
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                    Math.sin(dLng / 2) * Math.sin(dLng / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return Math.round(R * c);
            },

            checkGpsLocation() {
                this.isGpsChecking = true;
                this.gpsTitle = 'Memeriksa GPS...';
                this.gpsStatusMessage = 'Mengambil koordinat HP Anda...';

                if (!navigator.geolocation) {
                    this.isGpsChecking = false;
                    this.evaluateLocation(null, null);
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.isGpsChecking = false;
                        this.latitude = pos.coords.latitude;
                        this.longitude = pos.coords.longitude;
                        this.evaluateLocation(this.latitude, this.longitude);
                    },
                    (err) => {
                        this.isGpsChecking = false;
                        // Fallback simulasi jika izin GPS ditolak di browser laptop/desktop
                        console.warn('Geolocation error:', err);
                        this.useSimulatedUnitGps();
                    },
                    { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
                );
            },

            evaluateLocation(lat, lng) {
                if (lat === null || lng === null) {
                    this.isGpsValid = false;
                    this.gpsUnverified = false;
                    this.gpsTitle = 'GPS Tidak Aktif';
                    this.gpsStatusMessage = 'Aktifkan GPS pada perangkat Anda untuk memvalidasi lokasi.';
                    this.stopLiveCamera();
                    return;
                }

                const u = this.selectedUnit;
                if (!u) {
                    this.isGpsValid = true;
                    return;
                }

                if (u.has_coords && u.latitude !== null && u.longitude !== null) {
                    const dist = this.calculateHaversineMeters(lat, lng, u.latitude, u.longitude);
                    this.gpsDistanceMeters = dist;
                    const maxRadius = u.radius_toleransi || 50;

                    if (dist <= maxRadius) {
                        this.isGpsValid = true;
                        this.gpsUnverified = false;
                        this.gpsTitle = 'Lokasi Sesuai';
                        this.gpsStatusMessage = `Anda berada ${dist} m dari rumah unit ini (radius toleransi ${maxRadius} m).`;
                    } else {
                        this.isGpsValid = false;
                        this.gpsUnverified = false;
                        this.gpsTitle = 'Lokasi Tidak Sesuai';
                        this.gpsStatusMessage = `Jarak Anda ${dist} m dari unit (melebihi batas ${maxRadius} m). Kamera dinonaktifkan.`;
                        this.stopLiveCamera();
                    }
                } else {
                    // Unit belum memiliki koordinat acuan (grace period)
                    this.isGpsValid = true;
                    this.gpsUnverified = true;
                    this.gpsDistanceMeters = 0;
                    this.gpsTitle = 'Unit Belum Ada Koordinat (GPS Unverified)';
                    this.gpsStatusMessage = 'Pencatatan diizinkan. Titik GPS saat ini otomatis disimpan sebagai titik acuan awal.';
                }
            },

            useSimulatedUnitGps() {
                const u = this.selectedUnit;
                if (u && u.has_coords && u.latitude !== null && u.longitude !== null) {
                    this.latitude = u.latitude;
                    this.longitude = u.longitude;
                    this.gpsDistanceMeters = 0;
                    this.isGpsValid = true;
                    this.gpsUnverified = false;
                    this.gpsTitle = 'Mode Simulasi Lokasi Unit';
                    this.gpsStatusMessage = `Posisi diset tepat di depan ${u.nomor_rumah} (0 m dari koordinat unit).`;
                } else {
                    this.latitude = geofenceConfig.center_lat;
                    this.longitude = geofenceConfig.center_lng;
                    this.gpsDistanceMeters = 0;
                    this.isGpsValid = true;
                    this.gpsUnverified = true;
                    this.gpsTitle = 'Mode Simulasi Area Kawasan';
                    this.gpsStatusMessage = 'Unit belum berkoordinat. Lokasi diset di kawasan perumahan (GPS Unverified).';
                }
            },

            applyFilter() {
                let res = this.units;
                if (this.filterStatus === 'belum') {
                    res = res.filter(u => u.status === 'belum_dicatat' || u.status === 'ditolak');
                } else if (this.filterStatus === 'ditolak') {
                    res = res.filter(u => u.status === 'ditolak');
                }

                const q = this.searchQuery.toLowerCase().trim();
                if (q !== '') {
                    res = res.filter(u =>
                        (u.nomor_rumah && u.nomor_rumah.toLowerCase().includes(q)) ||
                        (u.nama_pemilik && u.nama_pemilik.toLowerCase().includes(q))
                    );
                }
                this.filteredUnits = res;
            },

            selectUnit(u) {
                this.selectedUnit = u;
                this.angkaMeter = u.angka_sekarang ? u.angka_sekarang.toString() : '';
                this.photoPreview = u.foto_meter ? (u.foto_meter.startsWith('http') ? u.foto_meter : '/storage/' + u.foto_meter) : null;
                this.ocrDone = false;
                this.ocrSuggestedDigits = null;
                this.stopLiveCamera();
                this.evaluateLocation(this.latitude, this.longitude);
                this.hitungPemakaian();
            },

            hitungPemakaian() {
                const lalu = Number(this.selectedUnit?.angka_lalu || 0);
                const skrg = Number(this.angkaMeter || 0);
                if (this.angkaMeter === '') {
                    this.pemakaian = 0;
                } else {
                    this.pemakaian = skrg - lalu;
                }
            },

            incrementMeter() {
                let val = Number(this.angkaMeter || (this.selectedUnit?.angka_lalu || 0));
                this.angkaMeter = (val + 1).toString();
                this.isOcrCorrected = true;
                this.hitungPemakaian();
            },

            decrementMeter() {
                let val = Number(this.angkaMeter || (this.selectedUnit?.angka_lalu || 0));
                if (val > 0) {
                    this.angkaMeter = (val - 1).toString();
                    this.isOcrCorrected = true;
                    this.hitungPemakaian();
                }
            },

            useOcrDigits() {
                if (this.ocrSuggestedDigits !== null) {
                    this.angkaMeter = this.ocrSuggestedDigits.toString();
                    this.isOcrCorrected = true;
                    this.hitungPemakaian();
                }
            },

            // WebRTC Live Camera
            async startLiveCamera() {
                if (!this.isGpsValid) {
                    this.checkGpsLocation();
                    return;
                }
                this.photoPreview = null;
                this.ocrDone = false;

                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 } },
                            audio: false
                        });
                        this.mediaStream = stream;
                        this.cameraStreamActive = true;
                        this.$nextTick(() => {
                            const video = document.getElementById('camera_video');
                            if (video) {
                                video.srcObject = stream;
                                video.play();
                            }
                        });
                        return;
                    } catch (err) {
                        console.warn('Live WebRTC tidak didukung/gagal, beralih ke input file kamera:', err);
                    }
                }
                document.getElementById('fallback_camera_input').click();
            },

            stopLiveCamera() {
                if (this.mediaStream) {
                    this.mediaStream.getTracks().forEach(t => t.stop());
                    this.mediaStream = null;
                }
                this.cameraStreamActive = false;
            },

            captureFromStream() {
                const video = document.getElementById('camera_video');
                const canvas = document.getElementById('camera_canvas');
                if (!video || !canvas) return;

                const maxDim = 960;
                let width = video.videoWidth || 640;
                let height = video.videoHeight || 480;
                if (width > maxDim || height > maxDim) {
                    if (width > height) {
                        height = Math.round((height * maxDim) / width);
                        width = maxDim;
                    } else {
                        width = Math.round((width * maxDim) / height);
                        height = maxDim;
                    }
                }
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, width, height);

                const dataUrl = canvas.toDataURL('image/jpeg', 0.72);
                this.photoPreview = dataUrl;

                canvas.toBlob((blob) => {
                    if (blob) {
                        const file = new File([blob], 'meter_snapshot.jpg', { type: 'image/jpeg' });
                        this.photoFile = file;
                        try {
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            document.getElementById('foto_meter_hidden').files = dt.files;
                        } catch (e) {}
                        this.runOcrProcess();
                    }
                }, 'image/jpeg', 0.72);

                this.stopLiveCamera();
            },

            handleImageFileSelected(e) {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                // Tampilkan indikator proses cepat
                this.isOcrProcessing = true;
                this.ocrDone = false;
                this.stopLiveCamera();

                const reader = new FileReader();
                reader.onload = (evt) => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        const maxDim = 960;
                        let w = img.width;
                        let h = img.height;
                        if (w > maxDim || h > maxDim) {
                            if (w > h) {
                                h = Math.round((h * maxDim) / w);
                                w = maxDim;
                            } else {
                                w = Math.round((w * maxDim) / h);
                                h = maxDim;
                            }
                        }
                        canvas.width = w;
                        canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, w, h);

                        const compressedDataUrl = canvas.toDataURL('image/jpeg', 0.72);
                        this.photoPreview = compressedDataUrl;

                        canvas.toBlob((blob) => {
                            if (blob) {
                                const optimizedFile = new File([blob], file.name ? file.name.replace(/\.[^/.]+$/, '.jpg') : 'meter_upload.jpg', { type: 'image/jpeg' });
                                this.photoFile = optimizedFile;
                                try {
                                    const dt = new DataTransfer();
                                    dt.items.add(optimizedFile);
                                    document.getElementById('foto_meter_hidden').files = dt.files;
                                } catch (err) {}
                                this.runOcrProcess();
                            }
                        }, 'image/jpeg', 0.72);
                    };
                    img.onerror = () => {
                        this.photoFile = file;
                        this.photoPreview = evt.target.result;
                        try {
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            document.getElementById('foto_meter_hidden').files = dt.files;
                        } catch (err) {}
                        this.runOcrProcess();
                    };
                    img.src = evt.target.result;
                };
                reader.readAsDataURL(file);
                e.target.value = '';
            },

            handleNativeCameraCapture(e) {
                this.handleImageFileSelected(e);
            },

            retakeCamera() {
                this.photoPreview = null;
                this.photoFile = null;
                this.ocrDone = false;
                this.ocrSuggestedDigits = null;
                this.startLiveCamera();
            },

            async runOcrProcess() {
                this.isOcrProcessing = true;
                this.ocrDone = false;
                this.ocrSuggestedDigits = null;

                try {
                    if (this.photoFile && this.selectedUnit) {
                        const formData = new FormData();
                        formData.append('unit_id', this.selectedUnit.id);
                        formData.append('foto_meter', this.photoFile);
                        formData.append('latitude', this.latitude);
                        formData.append('longitude', this.longitude);

                        const response = await fetch("{{ route('petugas.pindai.ocr') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        const result = await response.json();
                        this.ocrMessage = result.message || '';
                        if (result.ai_provider) {
                            this.ocrProvider = result.ai_provider;
                        }
                        if (result.success && result.suggested_digits !== null) {
                            this.ocrSuggestedDigits = result.suggested_digits;
                            this.confidenceScore = result.confidence_score || 95.0;
                            // Masukkan langsung sebagai saran
                            this.angkaMeter = result.suggested_digits.toString();
                            this.hitungPemakaian();
                        }
                    }
                } catch (err) {
                    console.warn('OCR error:', err);
                } finally {
                    this.isOcrProcessing = false;
                    this.ocrDone = true;
                }
            },

            submitForm(formEl) {
                this.isSubmitting = true;
                formEl.submit();
            }
        };
    }
    </script>
</body>
</html>
