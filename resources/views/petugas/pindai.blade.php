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

        /* Primary CTA Button (Sharp Kotak) */
        .btn-cyan-cta {
            background-color: #2563EB;
            color: #FFFFFF;
            font-weight: 700;
            border-radius: 0px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-cyan-cta:hover:not(:disabled) {
            background-color: #1D4ED8;
        }
        .btn-cyan-cta:disabled {
            background-color: #CBD5E1;
            color: #64748B;
            cursor: not-allowed;
        }

        /* Tabular nums for clean meter digits */
        .meter-digits {
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body class="bg-[#E2E8F0] font-sans text-[#0F172A] antialiased min-h-screen flex justify-center">

    {{-- ==================== MOBILE WEB APP CONTAINER (MAX 430PX, TOP NAVBAR) ==================== --}}
    <div class="w-full max-w-[430px] min-h-screen bg-[#F8FAFC] flex flex-col border-x border-[#CBD5E1] shadow-sm">

        @include('petugas.partials.topbar', ['title' => 'Pindai Meter', 'active' => 'pindai'])

        <div class="flex-1 flex flex-col min-w-0">

            {{-- Konten Utama Mobile --}}
            <main class="flex-1 flex flex-col justify-between p-3 w-full mx-auto" x-data="pindaiMeterApp()">

                {{-- Flash Message Ringkas / Toast --}}
                @if(session('success') || isset($successMessage))
                <div class="mb-2 px-3 py-2 bg-white border border-[#E2E8F0] border-l-[3px] border-l-[#16A34A] text-xs text-[#0F172A] shrink-0">
                    {{ session('success') ?? $successMessage }}
                </div>
                @endif

                {{-- Flash Error Ringkas --}}
                @if(session('error') || isset($errorMessage))
                <div class="mb-2 px-3 py-2 bg-white border border-[#E2E8F0] border-l-[3px] border-l-[#DC2626] text-xs text-[#0F172A] shrink-0">
                    {{ session('error') ?? $errorMessage }}
                </div>
                @endif

                {{-- ==================== 1. KARTU UNIT TERPILIH ==================== --}}
                <div class="bg-white border border-[#E2E8F0] shrink-0">
                    <div class="px-3 py-2.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-mono text-[11px] font-semibold bg-[#0F172A] text-white px-1.5 py-0.5 whitespace-nowrap shrink-0" x-text="selectedUnit?.nomor_rumah ?? '—'"></span>
                                <span class="text-[13px] font-semibold text-[#0F172A] truncate" x-text="selectedUnit?.nama_pemilik ?? 'Pilih unit'"></span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-[#64748B] mt-1 whitespace-nowrap">
                                <span x-text="selectedUnit?.blok_nama ?? '—'"></span>
                                <span class="text-[#CBD5E1]">|</span>
                                <span>Lalu <strong class="text-[#0F172A] font-mono" x-text="(selectedUnit?.angka_lalu ?? 0) + ' m³'"></strong></span>
                                <span class="text-[#CBD5E1]">|</span>
                                <span class="font-medium" :class="selectedUnit?.sudah_dicatat ? 'text-[#16A34A]' : 'text-[#B45309]'" x-text="selectedUnit?.sudah_dicatat ? 'Tercatat' : 'Belum dicatat'"></span>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="toggleUnitSearch()"
                            class="h-8 px-3 text-[11px] font-semibold text-[#0F172A] bg-white hover:bg-[#F8FAFC] border border-[#CBD5E1] transition-colors shrink-0 cursor-pointer"
                        >
                            <span x-text="showSearch ? 'Tutup' : 'Ganti Unit'"></span>
                        </button>
                    </div>

                    {{-- Dropdown Pencarian Unit --}}
                    <div x-show="showSearch" x-transition class="px-3 pb-3 pt-2 border-t border-[#F1F5F9]">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @input.debounce.200ms="filterUnits()"
                            placeholder="Cari nomor unit / nama warga..."
                            class="w-full h-9 px-2.5 text-xs border border-[#CBD5E1] text-[#0F172A] focus:outline-none focus:border-[#2563EB] bg-white"
                        />
                        <div class="mt-1 max-h-40 overflow-y-auto border border-[#E2E8F0] bg-white">
                            <template x-for="unit in filteredUnits.slice(0, 8)" :key="unit.id">
                                <button
                                    type="button"
                                    @click="selectUnit(unit)"
                                    class="w-full px-2.5 py-2 text-left hover:bg-[#F8FAFC] border-b border-[#F1F5F9] last:border-0 flex items-center justify-between gap-2 text-[11px]"
                                >
                                    <span class="flex items-center gap-2 min-w-0">
                                        <span class="font-mono font-semibold text-[#0F172A] whitespace-nowrap" x-text="unit.nomor_rumah"></span>
                                        <span class="text-[#334155] truncate" x-text="unit.nama_pemilik"></span>
                                    </span>
                                    <span class="text-[10px] text-[#94A3B8] shrink-0" x-text="unit.blok_nama"></span>
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
                        class="px-3 py-2 border border-[#E2E8F0] border-l-[3px] bg-white shrink-0 transition-colors"
                        :class="isGpsValid ? 'border-l-[#16A34A]' : 'border-l-[#DC2626]'"
                    >
                        <div class="flex items-start gap-2">
                            <span
                                class="w-1.5 h-1.5 rounded-full shrink-0 mt-1.5"
                                :class="isGpsChecking ? 'bg-[#D97706] animate-pulse' : (isGpsValid ? 'bg-[#16A34A]' : 'bg-[#DC2626]')"
                            ></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-semibold text-[#0F172A]" x-text="isGpsValid ? 'Lokasi valid' : 'Lokasi belum terverifikasi'"></p>
                                <p class="text-[11px] text-[#64748B] leading-snug mt-0.5" x-text="gpsStatusMessage"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-2 pl-3.5">
                            <button
                                x-show="!isGpsValid"
                                type="button"
                                @click="useSimulatedCenterGps()"
                                class="h-7 px-2.5 text-[11px] font-semibold bg-[#0F172A] text-white cursor-pointer hover:bg-[#1E293B]"
                            >
                                Simulasi di Lokasi
                            </button>
                            <button
                                type="button"
                                @click="checkGpsLocation()"
                                class="h-7 px-2.5 text-[11px] font-semibold text-[#0F172A] border border-[#CBD5E1] bg-white hover:bg-[#F8FAFC] cursor-pointer"
                            >
                                Cek Ulang GPS
                            </button>
                        </div>
                    </div>

                    {{-- ==================== 2. VIEWFINDER KAMERA (RESPONSIVE FLEXIBLE HEIGHT) ==================== --}}
                    <div class="relative w-full flex-1 min-h-[160px] max-h-[240px] bg-[#0B1120] overflow-hidden flex flex-col items-center justify-center border border-[#1E293B]">

                        {{-- Corner Targeting Guides [ ] --}}
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

                        {{-- Top Viewfinder HUD --}}
                        <div class="absolute top-2 left-3 right-3 z-20 flex items-center justify-between text-[10px] text-[#94A3B8] font-mono pointer-events-none">
                            <span>GRAND SULAWESI | OCR</span>
                            <span class="flex items-center gap-1" :class="isGpsValid ? 'text-[#60A5FA]' : 'text-[#EF4444]'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="isGpsValid ? 'bg-[#16A34A]' : 'bg-[#EF4444]'"></span>
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
                                <div class="flex items-center justify-center gap-4">
                                    <button
                                        type="button"
                                        @click="startLiveCamera()"
                                        class="px-3.5 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold flex items-center gap-2 cursor-pointer transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                                        </svg>
                                        <span>Buka Kamera</span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="document.getElementById('fallback_camera_input').click()"
                                        class="px-3.5 py-2.5 bg-[#1E293B] hover:bg-[#334155] text-white border border-[#475569] text-xs font-bold flex items-center gap-2 cursor-pointer transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                        </svg>
                                        <span>Pilih Foto</span>
                                    </button>
                                </div>
                            </template>

                            <template x-if="!isGpsValid">
                                <div class="inline-flex flex-col items-center max-w-xs">
                                    <span class="text-xs font-bold text-[#FCA5A5]">Kamera Dikunci (Di Luar Radius 700m)</span>
                                    <span class="text-[10px] text-[#CBD5E1] mt-1" x-text="gpsStatusMessage"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Tombol Shutter Saat Kamera Live --}}
                        <div x-show="cameraStreamActive && !photoPreview" class="absolute bottom-3 left-0 right-0 z-30 flex items-center justify-center">
                            <button
                                type="button"
                                @click="captureFromStream()"
                                class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs flex items-center gap-2 transition-colors cursor-pointer"
                            >
                                <span>Ambil Foto Meter</span>
                            </button>
                        </div>

                        {{-- Preview Foto + OCR --}}
                        <div x-show="photoPreview" class="absolute inset-0 z-10" style="display: none;">
                            <img :src="photoPreview" alt="Foto Meter Air" class="w-full h-full object-cover">
                            <div x-show="isOcrProcessing" class="ocr-scan-line"></div>
                            <div class="absolute inset-0 bg-[#0B1120]/15"></div>

                            {{-- Indikator Sedang Memindai Teks OCR --}}
                            <div x-show="isOcrProcessing" class="absolute bottom-2.5 left-2.5 right-2.5 z-30 bg-[#0B1120]/90 px-2.5 py-1.5 border border-[#2563EB] flex items-center justify-between text-xs text-white font-semibold">
                                <span>Memindai angka meter (OCR)...</span>
                                <svg class="w-4 h-4 animate-spin text-[#60A5FA]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>

                            {{-- Tombol Foto Ulang --}}
                            <div class="absolute top-2.5 right-2.5 z-30">
                                <button
                                    type="button"
                                    @click="retakeCamera()"
                                    class="px-2.5 py-1 bg-[#0B1120]/90 hover:bg-[#0B1120] text-white text-[11px] font-semibold border border-[#475569] flex items-center gap-1 transition-colors cursor-pointer"
                                >
                                    <span>Foto Ulang</span>
                                </button>
                            </div>

                            {{-- Hasil Deteksi OCR --}}
                            <div x-show="ocrDone" x-transition class="absolute bottom-2.5 left-2.5 right-2.5 z-30 bg-[#0B1120]/90 px-2.5 py-1.5 border border-[#334155] flex items-center justify-between gap-2">
                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center gap-1.5 text-xs text-white">
                                        <span class="truncate">Angka: <strong class="text-[#60A5FA] font-mono" x-text="angkaMeter || 'Tidak terbaca'"></strong></span>
                                    </div>
                                    <span class="text-[10px] text-[#CBD5E1] truncate" x-text="'Teks Foto: ' + (ocrRawText || '—')"></span>
                                </div>
                                <span class="text-[10px] text-[#60A5FA] font-mono font-bold shrink-0" x-text="confidenceScore + '% Akurat'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- ==================== 3. INPUT ANGKA METER + RINGKASAN PEMAKAIAN ==================== --}}
                    <div class="bg-white border border-[#E2E8F0] shrink-0">
                        <div class="px-3 pt-2.5 flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-[#475569]">Angka meter saat ini</span>
                            <span x-show="ocrDone" class="text-[10px] font-medium text-[#16A34A]" style="display: none;">Terbaca OCR</span>
                        </div>

                        <div class="px-3 pt-1.5 pb-3 flex items-stretch gap-2">
                            <button
                                type="button"
                                @click="decrementMeter()"
                                aria-label="Kurangi"
                                class="w-11 bg-white hover:bg-[#F8FAFC] text-[#0F172A] text-lg flex items-center justify-center border border-[#CBD5E1] transition-colors cursor-pointer shrink-0"
                            >−</button>

                            <div class="relative flex-1">
                                <input
                                    id="angka_meter_input"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    inputmode="decimal"
                                    x-model="angkaMeter"
                                    @input="hitungPemakaian()"
                                    placeholder="0"
                                    class="w-full h-12 text-center px-10 border text-2xl font-semibold font-mono text-[#0F172A] meter-digits transition-colors focus:outline-none"
                                    :class="anomali ? 'border-[#DC2626] bg-white' : 'border-[#CBD5E1] bg-white focus:border-[#2563EB]'"
                                />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-[#64748B]">m³</span>
                            </div>

                            <button
                                type="button"
                                @click="incrementMeter()"
                                aria-label="Tambah"
                                class="w-11 bg-white hover:bg-[#F8FAFC] text-[#0F172A] text-lg flex items-center justify-center border border-[#CBD5E1] transition-colors cursor-pointer shrink-0"
                            >+</button>
                        </div>

                        <div class="grid grid-cols-3 border-t border-[#E2E8F0] text-center">
                            <div class="py-2">
                                <span class="text-[10px] text-[#64748B] block">Bulan lalu</span>
                                <span class="text-[13px] font-semibold font-mono text-[#0F172A]" x-text="(selectedUnit?.angka_lalu ?? 0) + ' m³'"></span>
                            </div>
                            <div class="py-2 border-x border-[#E2E8F0]">
                                <span class="text-[10px] text-[#64748B] block">Bulan ini</span>
                                <span class="text-[13px] font-semibold font-mono text-[#0F172A]" x-text="angkaMeter ? angkaMeter + ' m³' : '—'"></span>
                            </div>
                            <div class="py-2">
                                <span class="text-[10px] text-[#64748B] block">Pemakaian</span>
                                <span class="text-[13px] font-semibold font-mono" :class="anomali ? 'text-[#DC2626]' : 'text-[#0F172A]'" x-text="(pemakaian !== null ? pemakaian : 0) + ' m³'"></span>
                            </div>
                        </div>

                        <div x-show="anomali" x-transition class="px-3 py-2 border-t border-[#E2E8F0] border-l-[3px] border-l-[#D97706] text-[11px] text-[#334155]" style="display: none;">
                            <span x-text="anomaliPesan"></span>
                        </div>
                    </div>

                    {{-- ==================== 5. TOMBOL CTA SELESAI ==================== --}}
                    <div class="shrink-0">
                        <button
                            type="submit"
                            :disabled="!angkaMeter || isSubmitting || selectedUnit?.sudah_dicatat || !isGpsValid"
                            class="btn-cyan-cta w-full py-3 text-xs sm:text-sm font-bold flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <svg x-show="!isSubmitting" class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                            <svg x-show="isSubmitting" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24" style="display:none;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isSubmitting ? 'Menyimpan...' : (!isGpsValid ? 'Terkunci — di luar radius lokasi' : 'Simpan Pembacaan')"></span>
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
                <div class="px-3 py-2 bg-white border border-[#CBD5E1] border-l-[3px] border-l-[#16A34A] text-xs flex items-center justify-between text-[#0F172A] font-bold shrink-0 mt-1">
                    <span class="font-mono">Tercatat: {{ $standLalu }} m³ → {{ $standSkrg }} m³</span>
                    <span class="text-[#16A34A] font-mono">+{{ $volPakai }} m³ Selesai</span>
                </div>
                @endif

            </main>
        </div>
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
