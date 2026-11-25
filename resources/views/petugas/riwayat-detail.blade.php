<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Pencatatan {{ optional($bacaan->unit)->nomor_rumah ?? '' }} — Grand Sulawesi</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Poppins', sans-serif !important; }
    </style>
</head>
<body class="bg-[#E2E8F0] text-[#0F172A] antialiased min-h-screen flex justify-center petugas-app">

    @php
        $pemakaian = $bacaan->angka_meter - $bacaan->angka_meter_bulanlalu;
        $statusMeta = match($bacaan->status_verifikasi) {
            'valid' => ['label' => 'Valid', 'badge' => 'bg-[#DCFCE7] text-[#166534] border-[#86EFAC]', 'dot' => 'bg-[#16A34A]'],
            'ter_flag' => ['label' => 'Menunggu Verifikasi (Ter-flag)', 'badge' => 'bg-[#FEF3C7] text-[#92400E] border-[#FCD34D]', 'dot' => 'bg-[#D97706]'],
            'ditolak' => ['label' => 'Ditolak Admin', 'badge' => 'bg-[#FEE2E2] text-[#991B1B] border-[#FCA5A5]', 'dot' => 'bg-[#DC2626]'],
            default => ['label' => 'Belum Dicatat', 'badge' => 'bg-[#F1F5F9] text-[#475569] border-[#CBD5E1]', 'dot' => 'bg-[#94A3B8]'],
        };
    @endphp

    <div class="w-full max-w-[430px] min-h-screen bg-[#F1F5F9] flex flex-col border-x border-[#CBD5E1]">

        @include('petugas.partials.topbar', ['title' => 'Detail Pencatatan', 'active' => 'riwayat'])

        <main class="flex-1 px-4 py-4 space-y-4">

            {{-- Navigasi Kembali --}}
            <div class="flex items-center justify-between gap-2">
                <a href="{{ route('petugas.riwayat') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#334155] hover:text-[#0F172A]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    <span>Kembali ke Riwayat</span>
                </a>

                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $statusMeta['badge'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $statusMeta['dot'] }}"></span>
                    {{ $statusMeta['label'] }}
                </span>
            </div>

            {{-- Alasan Penolakan (Jika Ditolak Admin) --}}
            @if($bacaan->status_verifikasi === 'ditolak' || $bacaan->alasan_penolakan)
                <section class="bg-[#FEF2F2] border border-[#FCA5A5] rounded-xl p-3.5 space-y-2">
                    <p class="text-xs font-bold text-[#991B1B]">Alasan Penolakan Admin</p>
                    <p class="text-xs text-[#7F1D1D] leading-relaxed">
                        {{ $bacaan->alasan_penolakan ?? $bacaan->catatan_anomali ?? 'Foto meteran perlu dipindai ulang.' }}
                    </p>
                    @if($bacaan->unit)
                        <a href="{{ route('petugas.pindai', ['unit_id' => $bacaan->unit_id, 'blok_id' => $bacaan->unit->blok_id]) }}"
                           class="inline-flex items-center justify-center w-full h-9 rounded-lg bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-semibold transition-colors">
                            Pindai Meter Ulang
                        </a>
                    @endif
                </section>
            @endif

            {{-- Foto Bukti Meteran --}}
            <section class="bg-white border border-[#CBD5E1] rounded-xl shadow-xs overflow-hidden">
                <div class="px-3.5 py-2.5 border-b border-[#F1F5F9] flex items-center justify-between gap-2">
                    <h2 class="text-xs font-bold text-[#0F172A]">Foto Bukti Meteran</h2>
                    @if($fotoUrl)
                        <a href="{{ $fotoUrl }}" target="_blank" class="text-[11px] font-semibold text-[#2563EB] hover:underline">
                            Buka Penuh
                        </a>
                    @endif
                </div>

                <div class="p-3 bg-[#0F172A]/5">
                    @if($fotoUrl)
                        <img
                            src="{{ $fotoUrl }}"
                            alt="Foto Bukti Meteran {{ optional($bacaan->unit)->nomor_rumah ?? '' }}"
                            class="w-full max-h-64 object-contain rounded-lg border border-[#CBD5E1] bg-black/80"
                        >
                    @else
                        <div class="w-full h-44 rounded-lg border border-dashed border-[#CBD5E1] bg-[#F8FAFC] flex flex-col items-center justify-center text-center px-4 gap-1.5">
                            <svg class="w-8 h-8 text-[#94A3B8]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                            </svg>
                            <p class="text-xs font-semibold text-[#475569]">Berkas foto belum tersimpan di storage lokal</p>
                            <p class="text-[10px] text-[#94A3B8] font-mono break-all">{{ $bacaan->foto_meter ?? '—' }}</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Rincian Angka Bacaan --}}
            <section class="bg-white border border-[#CBD5E1] rounded-xl shadow-xs overflow-hidden">
                <div class="px-3.5 py-2.5 border-b border-[#F1F5F9] flex items-center justify-between gap-2">
                    <div>
                        <span class="font-mono text-xs font-bold bg-[#F1F5F9] text-[#0F172A] border border-[#CBD5E1] rounded-md px-2 py-0.5">
                            {{ optional($bacaan->unit)->nomor_rumah ?? '—' }}
                        </span>
                        <span class="text-xs font-semibold text-[#0F172A] ml-1.5">
                            {{ optional($bacaan->unit)->nama_pemilik ?? '—' }}
                        </span>
                    </div>
                    <span class="text-[11px] text-[#64748B]">{{ optional(optional($bacaan->unit)->blok)->nama_blok ?? '—' }}</span>
                </div>

                <div class="grid grid-cols-3 text-center bg-[#FAFCFF]">
                    <div class="py-3">
                        <span class="text-[10px] text-[#64748B] block">Stand Lalu</span>
                        <span class="text-sm font-bold font-mono text-[#0F172A]">{{ number_format($bacaan->angka_meter_bulanlalu, 0, ',', '.') }}</span>
                    </div>
                    <div class="py-3 border-x border-[#F1F5F9]">
                        <span class="text-[10px] text-[#64748B] block">Stand Kini</span>
                        <span class="text-sm font-bold font-mono text-[#0F172A]">{{ number_format($bacaan->angka_meter, 0, ',', '.') }}</span>
                    </div>
                    <div class="py-3">
                        <span class="text-[10px] text-[#64748B] block">Pemakaian</span>
                        <span class="text-sm font-bold font-mono {{ $pemakaian < 0 ? 'text-[#DC2626]' : 'text-[#16A34A]' }}">
                            {{ $pemakaian < 0 ? '' : '+' }}{{ number_format($pemakaian, 0, ',', '.') }} m³
                        </span>
                    </div>
                </div>
            </section>

            {{-- Informasi GPS, Waktu Catat, & Verifikasi --}}
            <section class="bg-white border border-[#CBD5E1] rounded-xl shadow-xs p-3.5 space-y-2.5 text-xs">
                <h2 class="font-bold text-[#0F172A]">Detail Verifikasi &amp; Lokasi GPS</h2>

                <div class="flex items-center justify-between gap-2">
                    <span class="text-[#64748B]">Waktu Catat</span>
                    <span class="font-mono font-semibold text-[#0F172A]">{{ $bacaan->created_at->format('d M Y, H:i:s') }}</span>
                </div>

                <div class="flex items-center justify-between gap-2">
                    <span class="text-[#64748B]">Periode Bacaan</span>
                    <span class="font-semibold text-[#0F172A]">{{ \Carbon\Carbon::create($bacaan->tahun, $bacaan->bulan, 1)->translatedFormat('F Y') }}</span>
                </div>

                <div class="flex items-center justify-between gap-2">
                    <span class="text-[#64748B]">Petugas Pencatat</span>
                    <span class="font-semibold text-[#0F172A]">{{ optional($bacaan->petugas)->name ?? $user->name }}</span>
                </div>

                <div class="flex items-center justify-between gap-2">
                    <span class="text-[#64748B]">Koordinat GPS</span>
                    @if($bacaan->latitude !== null && $bacaan->longitude !== null)
                        <a href="https://maps.google.com/?q={{ $bacaan->latitude }},{{ $bacaan->longitude }}" target="_blank" class="font-mono font-semibold text-[#2563EB] hover:underline">
                            {{ number_format((float) $bacaan->latitude, 6) }}, {{ number_format((float) $bacaan->longitude, 6) }}
                        </a>
                    @else
                        <span class="font-mono text-[#64748B]">Belum tercatat</span>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-2">
                    <span class="text-[#64748B]">Akurasi OCR</span>
                    <span class="font-mono font-semibold text-[#0F172A]">
                        {{ $bacaan->confidence_score !== null ? number_format((float) $bacaan->confidence_score, 1).'%' : 'Manual' }}
                    </span>
                </div>

                @if($bacaan->catatan_anomali)
                    <div class="pt-2 border-t border-[#F1F5F9]">
                        <span class="text-[#64748B] block mb-1">Catatan Sistem / Anomali</span>
                        <p class="text-[#334155] leading-snug">{{ $bacaan->catatan_anomali }}</p>
                    </div>
                @endif
            </section>

        </main>
    </div>

</body>
</html>
