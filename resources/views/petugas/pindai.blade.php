<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pindai Meter – Grand Sulawesi Air</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

    <style>
        :root {
            --color-ice-white: #F1F5F9;
            --color-navy: #0B132B;
            --color-navy-700: #131D3B;
            --color-navy-600: #1C2849;
            --color-navy-400: #5B6785;
            --color-navy-200: #CBD5E1;
            --color-cyan: #00F2FE;
            --color-cyan-600: #00C4CE;
            --color-cyan-100: #D6FDFF;
            --color-cyan-glow: rgba(0, 242, 254, 0.35);
            --surface-card: #FFFFFF;
            --shadow-card: 0 1px 2px rgba(11, 19, 43, 0.04), 0 4px 16px rgba(11, 19, 43, 0.06);
        }

        body {
            font-family: 'Poppins', sans-serif !important;
            background-color: var(--color-ice-white);
            color: var(--color-navy);
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
            background: #00F2FE;
            box-shadow: 0 0 14px #00F2FE, 0 0 28px #00F2FE;
            animation: scanLaser 2s linear infinite;
        }

        /* Primary Cyan CTA Button */
        .btn-cyan-cta {
            background-color: #00F2FE;
            color: #0B132B;
            font-weight: 700;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-cyan-cta:hover:not(:disabled) {
            background-color: #00C4CE;
            box-shadow: 0 0 18px rgba(0, 242, 254, 0.45);
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

        /* Tabular nums for clean meter digits */
        .meter-digits {
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body class="bg-[#0B132B] font-sans text-[#0B132B] antialiased min-h-screen flex justify-center">

    {{-- ==================== MOBILE WEB APP CONTAINER (MAX 430PX) ==================== --}}
    <div class="w-full max-w-[430px] min-h-screen bg-[#F1F5F9] flex flex-col justify-between relative shadow-2xl border-x border-[#1E293B]/40 pb-20">

        <div class="flex-1 flex flex-col min-w-0">

            {{-- Topbar Mobile App --}}
            <header class="px-4 py-3.5 bg-[#0F172A] text-white flex items-center justify-between shrink-0 z-30 rounded-b-2xl shadow-md">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#1E293B] border border-[#334155] flex items-center justify-center p-1.5 shrink-0">
                        <x-brand-logo class="w-full h-full object-contain" />
                    </div>
                    <div>
                        <h1 class="text-xs font-extrabold text-white tracking-tight leading-none">Pindai Meter Air (OCR)</h1>
                        <p class="text-[10px] text-[#00F2FE] font-medium mt-0.5">Periode: {{ $periodeStr }}</p>
                    </div>
                </div>

                {{-- Status & Avatar --}}
                <div class="flex items-center gap-2">
                    <div class="text-right">
                        <span class="text-[11px] font-bold text-white block leading-tight">{{ Auth::user()->name ?? 'Hendra' }}</span>
                        <span class="text-[9px] text-[#94A3B8] block">Mobile Petugas</span>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-[#00F2FE] text-[#0B132B] font-extrabold text-xs flex items-center justify-center shadow-xs">
                        {{ strtoupper(substr(Auth::user()->name ?? 'HE', 0, 2)) }}
                    </div>
                </div>
            </header>

            {{-- Konten Utama Mobile --}}
            <main class="flex-1 flex flex-col justify-between p-3 w-full mx-auto" x-data="pindaiMeterApp()">

                {{-- Flash Message Ringkas / Toast --}}
                @if(session('success') || isset($successMessage))
                <div class="mb-2 px-3 py-1.5 bg-[#DCFCE7] border border-[#86EFAC] rounded-xl text-xs flex items-center justify-between gap-2 shrink-0">
                    <span class="text-[#166534] font-semibold truncate">{{ session('success') ?? $successMessage }}</span>
                    <span class="text-[10px] text-[#16A34A] font-bold shrink-0">Tersimpan</span>
                </div>
                @endif

                {{-- Flash Error Ringkas --}}
                @if(session('error') || isset($errorMessage))
                <div class="mb-2 px-3 py-1.5 bg-[#FEE2E2] border border-[#FCA5A5] rounded-xl text-xs flex items-center justify-between gap-2 shrink-0">
                    <span class="text-[#991B1B] font-semibold truncate">{{ session('error') ?? $errorMessage }}</span>
                    <span class="text-[10px] text-[#DC2626] font-bold shrink-0">Peringatan</span>
                </div>
                @endif

                {{-- ==================== 1. BAR UNIT MINIMALIS (1 ROW RINGKAS) ==================== --}}
                <div class="bg-white rounded-xl px-3 py-2 border border-[#CBD5E1] shadow-xs shrink-0">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-[#0B132B] text-white flex flex-col items-center justify-center shrink-0">
                                <span class="text-[7px] font-semibold text-[#94A3B8] leading-none">UNIT</span>
                                <span class="text-xs sm:text-sm font-bold text-[#00F2FE]" x-text="selectedUnit?.nomor_rumah ?? 'A-03'"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs sm:text-sm font-bold text-[#0B132B] truncate" x-text="selectedUnit?.nama_pemilik ?? 'Ibu Siti Nurhaliza'"></span>
                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-[#E2E8F0] text-[#0B132B] font-semibold shrink-0" x-text="selectedUnit?.blok_nama ?? 'Blok A'"></span>
                                </div>
                                <div class="flex items-center gap-2 text-[10px] text-[#5B6785] mt-0.5">
                                    <span>Lalu: <strong class="text-[#0B132B] meter-digits" x-text="(selectedUnit?.angka_lalu ?? 4198) + ' m³'"></strong></span>
                                    <span>•</span>
                                    <span class="font-medium" :class="selectedUnit?.sudah_dicatat ? 'text-[#16A34A]' : 'text-[#D97706]'" x-text="selectedUnit?.sudah_dicatat ? 'Tercatat' : 'Belum Dicatat'"></span>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="toggleUnitSearch()"
                            class="text-[11px] font-bold text-[#0B132B] hover:text-[#00C4CE] bg-[#F1F5F9] hover:bg-[#E2E8F0] px-2.5 py-1 rounded-lg border border-[#CBD5E1] transition-all shrink-0 cursor-pointer"
                        >
                            <span x-text="showSearch ? 'Tutup' : 'Ganti'"></span>
                        </button>
                    </div>

                    {{-- Dropdown Pencarian Unit --}}
                    <div x-show="showSearch" x-transition class="mt-2 pt-2 border-t border-[#F1F5F9]">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @input.debounce.200ms="filterUnits()"
                            placeholder="Cari nomor unit / nama warga..."
                            class="w-full px-2.5 py-1 text-xs rounded-lg border border-[#CBD5E1] text-[#0B132B] focus:outline-none focus:ring-1 focus:ring-[#00F2FE] bg-white"
                        />
                        <div class="mt-1 max-h-32 overflow-y-auto border border-[#CBD5E1] rounded-lg bg-white shadow-md">
                            <template x-for="unit in filteredUnits.slice(0, 8)" :key="unit.id">
                                <button
                                    type="button"
                                    @click="selectUnit(unit)"
                                    class="w-full px-2.5 py-1.5 text-left hover:bg-[#F1F5F9] border-b border-[#F1F5F9] last:border-0 flex items-center justify-between text-[11px]"
                                >
                                    <span class="font-bold text-[#0B132B]" x-text="'UNIT ' + unit.nomor_rumah + ' - ' + unit.nama_pemilik"></span>
                                    <span class="text-[10px] text-[#94A3B8]" x-text="unit.blok_nama"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- ==================== FORM PENCATATAN ==================== --}}
                <form
                    method="POST"
                    action="{{ route('petugas.pindai.store') }}"
                    enctype="multipart/form-data"
                    id="form-pindai-meter"
                    @submit.prevent="submitForm($el)"
                    class="flex-1 flex flex-col justify-between my-2 gap-2 overflow-hidden"
                >
                    @csrf
                    <input type="hidden" name="unit_id" :value="selectedUnit?.id">
                    <input type="hidden" name="angka_meter" :value="angkaMeter">
                    <input type="hidden" name="latitude" :value="latitude">
                    <input type="hidden" name="longitude" :value="longitude">
                    <input type="hidden" name="confidence_score" :value="confidenceScore">
                    <input type="hidden" name="is_ocr_corrected" :value="isOcrCorrected ? '1' : '0'">
                    <input type="file" name="foto_meter" id="foto_meter_hidden" class="hidden" accept="image/*">
                    <input type="file" id="fallback_camera_input" accept="image/*" capture="environment" class="hidden" @change="handleNativeCameraCapture($event)">

                    {{-- ==================== STATUS DOMAIN LOKASI GPS (GEOFENCING 700 METER) ==================== --}}
                    <div
                        class="px-3 py-1.5 rounded-xl border text-[11px] flex items-center justify-between gap-2 shrink-0 transition-colors"
                        :class="isGpsValid ? 'bg-[#DCFCE7] border-[#86EFAC] text-[#166534]' : 'bg-[#FEE2E2] border-[#FCA5A5] text-[#991B1B]'"
                    >
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span
                                class="w-2 h-2 rounded-full shrink-0"
                                :class="isGpsChecking ? 'bg-[#D97706] animate-ping' : (isGpsValid ? 'bg-[#16A34A]' : 'bg-[#DC2626]')"
                            ></span>
                            <span class="font-semibold truncate" x-text="gpsStatusMessage"></span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                x-show="!isGpsValid"
                                type="button"
                                @click="useSimulatedCenterGps()"
                                class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#0B132B] text-[#00F2FE] cursor-pointer hover:opacity-90"
                            >
                                Simulasi di Lokasi
                            </button>
                            <button
                                type="button"
                                @click="checkGpsLocation()"
                                class="text-[10px] font-bold underline cursor-pointer hover:opacity-80"
                            >
                                Cek Ulang GPS
                            </button>
                        </div>
                    </div>

                    {{-- ==================== 2. VIEWFINDER KAMERA (RESPONSIVE FLEXIBLE HEIGHT) ==================== --}}
                    <div class="relative w-full flex-1 min-h-[150px] max-h-[220px] sm:max-h-[260px] bg-[#0B132B] rounded-xl sm:rounded-2xl overflow-hidden flex flex-col items-center justify-center border-2 border-[#1C2849] shadow-xs">

                        {{-- Corner Targeting Guides [ ] --}}
                        <div class="absolute inset-3 pointer-events-none z-20 flex flex-col justify-between">
                            <div class="flex justify-between">
                                <div class="w-5 h-5 border-t-2 border-l-2 border-[#00F2FE]"></div>
                                <div class="w-5 h-5 border-t-2 border-r-2 border-[#00F2FE]"></div>
                            </div>
                            <div class="flex justify-between">
                                <div class="w-5 h-5 border-b-2 border-l-2 border-[#00F2FE]"></div>
                                <div class="w-5 h-5 border-b-2 border-r-2 border-[#00F2FE]"></div>
                            </div>
                        </div>

                        {{-- Top Viewfinder HUD --}}
                        <div class="absolute top-2 left-3 right-3 z-20 flex items-center justify-between text-[10px] text-[#94A3B8] font-mono pointer-events-none">
                            <span>AIRGRAND | PINDAI METER</span>
                            <span class="flex items-center gap-1" :class="isGpsValid ? 'text-[#00F2FE]' : 'text-[#EF4444]'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="isGpsValid ? 'bg-[#00F2FE] animate-ping' : 'bg-[#EF4444]'"></span>
                                <span x-text="isGpsValid ? 'GPS VALID (' + gpsDistanceMeters + 'm)' : 'DI LUAR RADIUS'"></span>
                            </span>
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

                        {{-- State Siaga (Tombol Buka Kamera / Terkunci Jika Di Luar Radius) --}}
                        <div x-show="!cameraStreamActive && !photoPreview" class="text-center z-10 px-4">
                            <template x-if="isGpsValid">
                                <div class="flex items-center justify-center gap-5">
                                    <button
                                        type="button"
                                        @click="startLiveCamera()"
                                        class="inline-flex flex-col items-center group cursor-pointer"
                                    >
                                        <div class="w-13 h-13 sm:w-15 sm:h-15 rounded-full bg-[#131D3B] border-2 border-[#00F2FE] flex items-center justify-center shadow-[0_0_16px_rgba(0,242,254,0.35)] group-hover:scale-105 group-hover:bg-[#1C2849] transition-all">
                                            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-[#00F2FE]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                                            </svg>
                                        </div>
                                        <span class="mt-2 text-xs sm:text-sm font-bold text-white group-hover:text-[#00F2FE] transition-colors">Buka Kamera</span>
                                        <span class="text-[10px] text-[#94A3B8]">Kamera Langsung</span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="document.getElementById('fallback_camera_input').click()"
                                        class="inline-flex flex-col items-center group cursor-pointer"
                                    >
                                        <div class="w-13 h-13 sm:w-15 sm:h-15 rounded-full bg-[#131D3B] border-2 border-[#94A3B8] group-hover:border-[#00F2FE] flex items-center justify-center group-hover:scale-105 group-hover:bg-[#1C2849] transition-all">
                                            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-[#CBD5E1] group-hover:text-[#00F2FE]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                            </svg>
                                        </div>
                                        <span class="mt-2 text-xs sm:text-sm font-bold text-white group-hover:text-[#00F2FE] transition-colors">Pilih File Foto</span>
                                        <span class="text-[10px] text-[#94A3B8]">Upload dari Laptop</span>
                                    </button>
                                </div>
                            </template>

                            <template x-if="!isGpsValid">
                                <div class="inline-flex flex-col items-center max-w-xs">
                                    <div class="w-12 h-12 rounded-full bg-[#1C2849] border-2 border-[#EF4444] flex items-center justify-center">
                                        <svg class="w-6 h-6 text-[#EF4444]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                        </svg>
                                    </div>
                                    <span class="mt-2 text-xs sm:text-sm font-bold text-[#FCA5A5]">Kamera Dikunci (Di Luar Radius)</span>
                                    <span class="text-[10px] text-[#CBD5E1] mt-0.5" x-text="gpsStatusMessage"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Tombol Shutter Saat Kamera Live --}}
                        <div x-show="cameraStreamActive && !photoPreview" class="absolute bottom-3 left-0 right-0 z-30 flex items-center justify-center">
                            <button
                                type="button"
                                @click="captureFromStream()"
                                class="px-4 py-2 rounded-xl bg-[#00F2FE] hover:bg-[#00C4CE] text-[#0B132B] font-bold text-xs flex items-center gap-2 shadow-[0_0_14px_rgba(0,242,254,0.4)] transition-all cursor-pointer"
                            >
                                <span class="w-2.5 h-2.5 rounded-full bg-[#0B132B]"></span>
                                <span>Ambil Foto Meter</span>
                            </button>
                        </div>

                        {{-- Preview Foto + OCR --}}
                        <div x-show="photoPreview" class="absolute inset-0 z-10" style="display: none;">
                            <img :src="photoPreview" alt="Foto Meter Air" class="w-full h-full object-cover">
                            <div x-show="isOcrProcessing" class="ocr-scan-line"></div>
                            <div class="absolute inset-0 bg-[#0B132B]/15"></div>

                            {{-- Indikator Sedang Memindai Teks OCR --}}
                            <div x-show="isOcrProcessing" class="absolute bottom-2.5 left-2.5 right-2.5 z-30 bg-[#0B132B]/90 backdrop-blur-xs px-2.5 py-1.5 rounded-lg border border-[#00F2FE]/50 flex items-center justify-between text-xs text-[#00F2FE] font-semibold">
                                <span>Memindai teks dari foto (OCR)...</span>
                                <svg class="w-4 h-4 animate-spin text-[#00F2FE]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>

                            {{-- Tombol Foto Ulang --}}
                            <div class="absolute top-2.5 right-2.5 z-30">
                                <button
                                    type="button"
                                    @click="retakeCamera()"
                                    class="px-2.5 py-1 rounded-lg bg-[#0B132B]/85 hover:bg-[#0B132B] text-white text-[11px] font-semibold backdrop-blur-xs border border-[#1C2849] flex items-center gap-1 transition-colors cursor-pointer"
                                >
                                    <svg class="w-3 h-3 text-[#00F2FE]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                    <span>Ulang</span>
                                </button>
                            </div>

                            {{-- Hasil Deteksi OCR --}}
                            <div x-show="ocrDone" x-transition class="absolute bottom-2.5 left-2.5 right-2.5 z-30 bg-[#0B132B]/90 backdrop-blur-xs px-2.5 py-1.5 rounded-lg border border-[#00F2FE]/40 flex items-center justify-between gap-2">
                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center gap-1.5 text-xs text-white">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#00F2FE] shrink-0"></span>
                                        <span class="truncate">Angka: <strong class="text-[#00F2FE]" x-text="angkaMeter || 'Tidak terbaca'"></strong></span>
                                    </div>
                                    <span class="text-[10px] text-[#CBD5E1] truncate" x-text="'Teks Foto: ' + (ocrRawText || '—')"></span>
                                </div>
                                <span class="text-[10px] text-[#00F2FE] font-bold shrink-0" x-text="confidenceScore + '% Akurat'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- ==================== 3. INPUT STEPPER MINIMALIS ==================== --}}
                    <div class="bg-white rounded-xl px-3 py-2 border border-[#CBD5E1] shadow-xs shrink-0">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[11px] font-bold text-[#0B132B]">Hasil Pembacaan Meter (m³)</span>
                            <span x-show="ocrDone" class="inline-flex items-center gap-1 text-[10px] font-bold text-[#16A34A] bg-[#DCFCE7] px-2 py-0.5 rounded-full" style="display: none;">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span>Format Terbaca</span>
                            </span>
                        </div>

                        {{-- Input Row --}}
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="decrementMeter()"
                                class="w-10 h-10 rounded-lg bg-[#F1F5F9] hover:bg-[#E2E8F0] text-[#0B132B] font-bold text-lg flex items-center justify-center border border-[#CBD5E1] transition-colors cursor-pointer shrink-0"
                            >
                                –
                            </button>

                            <div class="relative flex-1">
                                <input
                                    id="angka_meter_input"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    x-model="angkaMeter"
                                    @input="hitungPemakaian()"
                                    placeholder="0000"
                                    class="w-full text-center py-1.5 sm:py-2 px-8 rounded-lg border-2 text-xl sm:text-2xl font-extrabold text-[#0B132B] meter-digits transition-all focus:outline-none"
                                    :class="anomali ? 'border-[#FCA5A5] bg-[#FEE2E2]' : 'border-[#CBD5E1] bg-white focus:border-[#00F2FE] focus:ring-1 focus:ring-[#00F2FE]'"
                                />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-[#5B6785]">m³</span>
                            </div>

                            <button
                                type="button"
                                @click="incrementMeter()"
                                class="w-10 h-10 rounded-lg bg-[#F1F5F9] hover:bg-[#E2E8F0] text-[#0B132B] font-bold text-lg flex items-center justify-center border border-[#CBD5E1] transition-colors cursor-pointer shrink-0"
                            >
                                +
                            </button>
                        </div>
                    </div>

                    {{-- ==================== 4. KALKULASI 3 KOLOM SEJAJAR (TIDAK BERTUMPUK) ==================== --}}
                    <div class="bg-white rounded-xl p-2 sm:p-2.5 border border-[#CBD5E1] shadow-xs shrink-0">
                        <div class="grid grid-cols-3 gap-1.5 text-center">
                            {{-- Sebelumnya --}}
                            <div class="p-1.5 rounded-lg bg-[#F8FAFC] border border-[#CBD5E1]">
                                <span class="text-[9px] font-bold text-[#5B6785] uppercase tracking-tight block">Sebelumnya</span>
                                <span class="text-xs sm:text-sm font-extrabold text-[#0B132B] meter-digits block mt-0.5" x-text="(selectedUnit?.angka_lalu ?? 4198) + ' m³'"></span>
                            </div>

                            {{-- Sekarang --}}
                            <div class="p-1.5 rounded-lg bg-[#F8FAFC] border border-[#CBD5E1]">
                                <span class="text-[9px] font-bold text-[#5B6785] uppercase tracking-tight block">Sekarang</span>
                                <span class="text-xs sm:text-sm font-extrabold text-[#0B132B] meter-digits block mt-0.5" x-text="(angkaMeter ? angkaMeter : '—') + (angkaMeter ? ' m³' : '')"></span>
                            </div>

                            {{-- Total Pemakaian --}}
                            <div class="p-1.5 rounded-lg border" :class="anomali ? 'bg-[#FEE2E2] border-[#FCA5A5]' : 'bg-[#DCFCE7] border-[#86EFAC]'">
                                <span class="text-[9px] font-bold uppercase tracking-tight block" :class="anomali ? 'text-[#DC2626]' : 'text-[#16A34A]'">Total Pakai</span>
                                <span class="text-xs sm:text-sm font-extrabold meter-digits block mt-0.5" :class="anomali ? 'text-[#DC2626]' : 'text-[#16A34A]'" x-text="(pemakaian !== null ? pemakaian : 0) + ' m³'"></span>
                            </div>
                        </div>

                        {{-- Alert Anomali Singkat (Inline) --}}
                        <div x-show="anomali" x-transition class="mt-1 px-2 py-1 rounded-md bg-[#FEF3C7] text-[10px] text-[#92400E] font-medium flex items-center gap-1.5" style="display: none;">
                            <span class="font-bold">⚠️</span>
                            <span class="truncate" x-text="anomaliPesan"></span>
                        </div>
                    </div>

                    {{-- ==================== 5. TOMBOL CTA SELESAI ==================== --}}
                    <div class="shrink-0">
                        <button
                            type="submit"
                            :disabled="!angkaMeter || isSubmitting || selectedUnit?.sudah_dicatat || !isGpsValid"
                            class="btn-cyan-cta w-full py-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 cursor-pointer shadow-md"
                        >
                            <svg x-show="!isSubmitting" class="w-4 h-4 text-[#0B132B]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                            <svg x-show="isSubmitting" class="w-4 h-4 animate-spin text-[#0B132B]" fill="none" viewBox="0 0 24 24" style="display:none;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isSubmitting ? 'Menyimpan...' : (!isGpsValid ? 'Terkunci: Anda Di Luar Radius Lokasi' : 'Selesai & Simpan Pembacaan')"></span>
                        </button>
                    </div>
                </form>

                {{-- Rekap Tercatat (Hanya Jika Sudah Tersimpan) --}}
                @if(isset($recordedData) || ($selectedUnit && $selectedUnit->bacaanMeters->isNotEmpty()))
                @php
                    $bacaanCurrent = $selectedUnit?->bacaanMeters->first();
                    $standLalu = $recordedData['lalu'] ?? ($bacaanCurrent ? $bacaanCurrent->angka_meter_bulanlalu : $angkaLalu);
                    $standSkrg = $recordedData['sekarang'] ?? ($bacaanCurrent ? $bacaanCurrent->angka_meter : $angkaLalu);
                    $volPakai = $recordedData['pemakaian'] ?? max(0, $standSkrg - $standLalu);
                @endphp
                <div class="px-3 py-1.5 bg-[#DCFCE7] border border-[#86EFAC] rounded-xl text-xs flex items-center justify-between text-[#166534] font-bold shrink-0 mt-1">
                    <span>Tercatat: {{ $standLalu }} m³ → {{ $standSkrg }} m³</span>
                    <span class="text-[#16A34A]">+{{ $volPakai }} m³ Selesai</span>
                </div>
                @endif

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
                <div class="w-12 h-12 rounded-full bg-[#00F2FE] text-[#0B132B] border-4 border-[#0F172A] flex items-center justify-center shadow-[0_0_16px_rgba(0,242,254,0.45)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-[#00F2FE] mt-0.5">Pindai OCR</span>
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

    {{-- Script Interaksi Kamera & OCR --}}
    <script>
    const allUnitsData = @json($unitsMapped ?? []);
    const preSelectedUnitId = {{ $selectedUnit?->id ?? 'null' }};
    const preSelectedAngkaLalu = {{ $angkaLalu ?? 4198 }};
    const geofenceDomain = @json($geofenceConfig ?? ['center_lat' => -4.007468, 'center_lng' => 119.661131, 'radius_meters' => 700.0]);

    function pindaiMeterApp() {
        return {
            allUnits: allUnitsData,
            filteredUnits: allUnitsData,
            searchQuery: '',
            showSearch: false,
            selectedUnit: null,

            cameraStreamActive: false,
            mediaStream: null,
            photoPreview: null,
            photoFile: null,

            isOcrProcessing: false,
            ocrDone: false,
            ocrRawText: '',
            confidenceScore: 98.0,
            isOcrCorrected: false,

            angkaMeter: '',
            pemakaian: 0,
            anomali: false,
            anomaliPesan: '',

            latitude: '',
            longitude: '',
            isGpsChecking: true,
            isGpsValid: false,
            gpsDistanceMeters: 0,
            gpsStatusMessage: 'Mendeteksi lokasi GPS petugas...',
            isSubmitting: false,

            init() {
                if (preSelectedUnitId) {
                    const found = this.allUnits.find(u => u.id == preSelectedUnitId);
                    if (found) {
                        found.angka_lalu = preSelectedAngkaLalu;
                        this.selectedUnit = found;
                    }
                } else if (this.allUnits.length > 0) {
                    this.selectedUnit = this.allUnits[0];
                }

                this.checkGpsLocation();
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
                this.gpsStatusMessage = 'Memeriksa radius lokasi GPS...';

                if (!navigator.geolocation) {
                    this.isGpsChecking = false;
                    this.isGpsValid = false;
                    this.gpsStatusMessage = 'Peringatan: Browser tidak mendukung GPS.';
                    this.stopLiveCamera();
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.isGpsChecking = false;
                        this.latitude = position.coords.latitude;
                        this.longitude = position.coords.longitude;

                        const dist = this.calculateHaversineMeters(
                            this.latitude,
                            this.longitude,
                            geofenceDomain.center_lat,
                            geofenceDomain.center_lng
                        );
                        this.gpsDistanceMeters = dist;

                        if (dist <= geofenceDomain.radius_meters) {
                            this.isGpsValid = true;
                            this.gpsStatusMessage = `Dalam Area Perumahan (${dist} m / batas ${geofenceDomain.radius_meters} m)`;
                        } else {
                            this.isGpsValid = false;
                            this.gpsStatusMessage = `Peringatan: Di luar radius area (${dist} m dari pusat, maks ${geofenceDomain.radius_meters} m)`;
                            this.stopLiveCamera();
                        }
                    },
                    (error) => {
                        this.isGpsChecking = false;
                        this.isGpsValid = false;
                        this.latitude = '';
                        this.longitude = '';
                        this.gpsStatusMessage = 'Peringatan: Izin lokasi GPS ditolak atau tidak aktif. Aktifkan GPS untuk memotret.';
                        this.stopLiveCamera();
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            },

            useSimulatedCenterGps() {
                this.isGpsChecking = false;
                this.latitude = geofenceDomain.center_lat;
                this.longitude = geofenceDomain.center_lng;
                this.gpsDistanceMeters = 0;
                this.isGpsValid = true;
                this.gpsStatusMessage = `Mode Tes: Titik Pusat Perumahan (0 m / batas ${geofenceDomain.radius_meters} m)`;
            },

            extractDigitsFromRawText(rawText) {
                if (!rawText) return null;
                const match = rawText.match(/\b(\d{3,7})\b/);
                if (match && match[1]) {
                    return parseInt(match[1], 10);
                }
                const digitsOnly = rawText.replace(/\D+/g, '');
                if (digitsOnly.length > 0) {
                    return parseInt(digitsOnly.slice(0, 7), 10);
                }
                return null;
            },

            toggleUnitSearch() {
                this.showSearch = !this.showSearch;
                this.filteredUnits = this.allUnits;
            },

            filterUnits() {
                const q = this.searchQuery.toLowerCase().trim();
                if (!q) {
                    this.filteredUnits = this.allUnits;
                } else {
                    this.filteredUnits = this.allUnits.filter(u =>
                        (u.nomor_rumah && u.nomor_rumah.toLowerCase().includes(q)) ||
                        (u.nama_pemilik && u.nama_pemilik.toLowerCase().includes(q))
                    );
                }
            },

            selectUnit(unit) {
                this.selectedUnit = unit;
                this.showSearch = false;
                this.angkaMeter = '';
                this.pemakaian = 0;
                this.anomali = false;
                this.photoPreview = null;
                this.ocrDone = false;
                this.stopLiveCamera();
            },

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
                            video: {
                                facingMode: { ideal: 'environment' },
                                width: { ideal: 1280 },
                                height: { ideal: 720 }
                            },
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
                        console.warn('Live WebRTC gagal, fallback ke kamera perangkat:', err);
                    }
                }

                const fallbackInput = document.getElementById('fallback_camera_input');
                if (fallbackInput) {
                    fallbackInput.click();
                }
            },

            stopLiveCamera() {
                if (this.mediaStream) {
                    this.mediaStream.getTracks().forEach(track => track.stop());
                    this.mediaStream = null;
                }
                this.cameraStreamActive = false;
            },

            captureFromStream() {
                const video = document.getElementById('camera_video');
                const canvas = document.getElementById('camera_canvas');
                if (!video || !canvas) return;

                const width = video.videoWidth || 640;
                const height = video.videoHeight || 480;
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, width, height);

                const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
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
                }, 'image/jpeg', 0.88);

                this.stopLiveCamera();
            },

            handleNativeCameraCapture(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.photoFile = file;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                    this.runOcrProcess();
                };
                reader.readAsDataURL(file);

                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    document.getElementById('foto_meter_hidden').files = dt.files;
                } catch (e) {}
            },

            async runOcrProcess() {
                this.isOcrProcessing = true;
                this.ocrDone = false;
                this.ocrRawText = '';

                try {
                    if (this.photoFile) {
                        const formData = new FormData();
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

                        if (result.success) {
                            this.ocrRawText = (result.raw_text || '').replace(/\s+/g, ' ').trim();
                            this.confidenceScore = result.confidence_score || 95.0;
                            if (result.suggested_digits !== null) {
                                this.angkaMeter = result.suggested_digits.toString();
                                this.hitungPemakaian();
                            }
                            this.isOcrProcessing = false;
                            this.ocrDone = true;
                            return;
                        }
                    }

                    // Fallback ke Real OCR di Browser (Tesseract.js) jika API Key Google Vision di .env belum diisi
                    if (window.Tesseract && this.photoPreview) {
                        const tesseractResult = await window.Tesseract.recognize(this.photoPreview, 'eng');
                        const raw = (tesseractResult?.data?.text || '').replace(/\s+/g, ' ').trim();
                        const conf = Math.round(tesseractResult?.data?.confidence || 85);
                        this.ocrRawText = raw || 'Tidak ada teks terbaca pada gambar';
                        this.confidenceScore = conf > 0 ? conf : 85.0;

                        const extracted = this.extractDigitsFromRawText(raw);
                        if (extracted !== null) {
                            this.angkaMeter = extracted.toString();
                            this.hitungPemakaian();
                        }
                    }
                } catch (err) {
                    console.warn('OCR gagal:', err);
                    this.ocrRawText = 'Gagal memindai teks otomatis, silakan ketik manual.';
                } finally {
                    this.isOcrProcessing = false;
                    this.ocrDone = true;
                }
            },

            retakeCamera() {
                this.photoPreview = null;
                this.photoFile = null;
                this.ocrDone = false;
                this.angkaMeter = '';
                this.pemakaian = 0;
                this.anomali = false;
                const fileInput = document.getElementById('foto_meter_hidden');
                if (fileInput) fileInput.value = '';
                const fallbackInput = document.getElementById('fallback_camera_input');
                if (fallbackInput) fallbackInput.value = '';

                this.startLiveCamera();
            },

            incrementMeter() {
                let current = parseFloat(this.angkaMeter || (this.selectedUnit?.angka_lalu ?? 4198));
                this.angkaMeter = (current + 1).toString();
                this.isOcrCorrected = true;
                this.hitungPemakaian();
            },

            decrementMeter() {
                let current = parseFloat(this.angkaMeter || (this.selectedUnit?.angka_lalu ?? 4198));
                if (current > 0) {
                    this.angkaMeter = Math.max(0, current - 1).toString();
                    this.isOcrCorrected = true;
                    this.hitungPemakaian();
                }
            },

            hitungPemakaian() {
                if (!this.selectedUnit || this.angkaMeter === '' || isNaN(this.angkaMeter)) {
                    this.pemakaian = 0;
                    this.anomali = false;
                    return;
                }
                const angkaLalu = parseFloat(this.selectedUnit.angka_lalu ?? 4198);
                const angkaSkrg = parseFloat(this.angkaMeter);
                const diff = (angkaSkrg - angkaLalu).toFixed(2);
                this.pemakaian = diff;

                if (parseFloat(diff) < 0) {
                    this.anomali = true;
                    this.anomaliPesan = 'Angka meter lebih kecil dari bulan lalu (-' + Math.abs(diff) + ' m³). Periksa kembali.';
                } else if (angkaLalu > 0 && parseFloat(diff) > (angkaLalu * 2)) {
                    this.anomali = true;
                    this.anomaliPesan = 'Lonjakan pemakaian tinggi (>2x rata-rata). Butuh verifikasi admin.';
                } else {
                    this.anomali = false;
                    this.anomaliPesan = '';
                }
            },

            submitForm(formEl) {
                if (!this.selectedUnit || !this.angkaMeter) return;
                if (this.selectedUnit.sudah_dicatat) return;
                if (!this.isGpsValid) return;

                this.isSubmitting = true;

                if (this.photoFile) {
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(this.photoFile);
                        document.getElementById('foto_meter_hidden').files = dt.files;
                    } catch (err) {}
                }

                formEl.submit();
            }
        };
    }
    </script>

</body>
</html>
