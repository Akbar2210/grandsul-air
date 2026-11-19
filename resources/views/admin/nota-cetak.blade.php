<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota Resmi Pembayaran Air #PAY-2611-{{ str_pad($pembayaran->id, 4, '0', STR_PAD_LEFT) }} — Grand Sulawesi Air</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-mono {
            font-family: 'Poppins', sans-serif;
        }
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }

        @media print {
            body {
                background: white !important;
                color: #0F172A !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-shadow-none {
                box-shadow: none !important;
                border: 1px solid #CBD5E1 !important;
            }
            .page-break {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-[#F8FAFC] font-sans text-[#1E293B] antialiased py-6 sm:py-10">

    {{-- Top Action Bar (No Print) --}}
    <div class="max-w-3xl mx-auto px-4 mb-6 no-print flex flex-col sm:flex-row items-center justify-between gap-3">
        <a href="{{ route('admin.riwayat-pembayaran') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#64748B] hover:text-[#0F172A] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Riwayat Pembayaran
        </a>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-[#60A5FA]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.673-2.684-.673-4.008 0-4.411 3.582-8 8-8s8 3.589 8 8c0 1.324-.433 2.932-.673 4.008M3 17.25h18m-15 3h12"/></svg>
                <span>Cetak Nota Resmi</span>
            </button>
            <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-white border border-[#CBD5E1] hover:bg-[#F8FAFC] text-[#0F172A] text-xs font-bold transition-colors cursor-pointer">
                Unduh PDF
            </button>
        </div>
    </div>

    {{-- Official Receipt Sheet --}}
    <main class="max-w-3xl mx-auto px-4">
        <div class="bg-white rounded-2xl border border-[#CBD5E1] shadow-xs print-shadow-none p-6 sm:p-10 relative overflow-hidden page-break">

            {{-- 1. Official Header --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-6 border-b border-[#E2E8F0] gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-[#0F172A] flex items-center justify-center p-2.5 shadow-xs">
                        <x-brand-logo class="w-full h-full object-contain" />
                    </div>
                    <div>
                        <h1 class="text-base font-extrabold text-[#0F172A] tracking-tight leading-tight">PENGELOLA AIR BERSIH MANDIRI</h1>
                        <p class="text-xs font-semibold text-[#334155]">Perumahan Grand Sulawesi • Komite Pengelola Lingkungan</p>
                        <p class="text-[11px] text-[#64748B] mt-0.5">Jl. Grand Sulawesi Boulevard No. 1, Blok Komersial, Makassar</p>
                    </div>
                </div>

                {{-- Status Badge & Receipt Number --}}
                <div class="sm:text-right">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-[#0F172A] text-white text-[11px] font-bold uppercase tracking-wider mb-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span>
                        <span>LUNAS • TERVERIFIKASI</span>
                    </div>
                    <p class="text-xs font-mono font-bold text-[#0F172A] tabular-nums">NO. NOTA: PAY-2611-{{ str_pad($pembayaran->id, 4, '0', STR_PAD_LEFT) }}</p>
                    <p class="text-[11px] text-[#64748B] font-mono tabular-nums">Tanggal Cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WITA</p>
                </div>
            </div>

            {{-- 2. Meta Information Columns --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-[#E2E8F0] text-xs">
                <div class="space-y-2 bg-[#F8FAFC] p-4 rounded-2xl border border-[#F1F5F9]">
                    <span class="text-[10px] font-bold text-[#64748B] uppercase tracking-wider block">IDENTITAS PELANGGAN</span>
                    <div class="flex justify-between">
                        <span class="text-[#64748B]">Nama Pemilik:</span>
                        <span class="font-bold text-[#0F172A]">{{ $pembayaran->tagihan->unit->nama_pemilik ?? 'Warga Grand Sulawesi' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#64748B]">Unit Hunian:</span>
                        <span class="font-mono font-bold px-2 py-0.5 rounded-md bg-[#0F172A] text-white tabular-nums">
                            {{ $pembayaran->tagihan->unit->nomor_rumah ?? '—' }} ({{ $pembayaran->tagihan->unit->blok->nama_blok ?? 'Blok A' }})
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#64748B]">ID Pelanggan:</span>
                        <span class="font-mono text-[#0F172A] font-semibold tabular-nums">GS-{{ str_pad($pembayaran->tagihan->unit->id ?? 1, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#64748B]">Kontak / WhatsApp:</span>
                        <span class="font-mono text-[#0F172A] tabular-nums">{{ $pembayaran->tagihan->unit->nomor_wa ?? '0812-4411-9800' }}</span>
                    </div>
                </div>

                <div class="space-y-2 bg-[#F8FAFC] p-4 rounded-2xl border border-[#F1F5F9]">
                    <span class="text-[10px] font-bold text-[#64748B] uppercase tracking-wider block">DETAIL TRANSAKSI</span>
                    <div class="flex justify-between">
                        <span class="text-[#64748B]">ID Tagihan:</span>
                        <span class="font-mono text-[#0F172A] font-semibold tabular-nums">TGH-2611-{{ str_pad($pembayaran->tagihan->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#64748B]">Periode Tagihan:</span>
                        <span class="font-bold text-[#0F172A]">{{ \Carbon\Carbon::parse($pembayaran->tgl_bayar)->translatedFormat('F Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#64748B]">Tanggal Pembayaran:</span>
                        <span class="font-mono font-bold text-[#0F172A] tabular-nums">{{ \Carbon\Carbon::parse($pembayaran->tgl_bayar)->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#64748B]">Metode Pembayaran:</span>
                        <span class="font-bold text-[#2563EB]">{{ $pembayaran->metode_bayar }}</span>
                    </div>
                </div>
            </div>

            {{-- 3. Detail Meter & Pemakaian Air --}}
            <div class="py-6 border-b border-[#E2E8F0]">
                <h2 class="text-xs font-bold text-[#64748B] uppercase tracking-wider mb-3">RINCIAN STAND METER & PEMAKAIAN</h2>
                <div class="grid grid-cols-3 gap-3 text-center mb-4">
                    <div class="p-3.5 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0]">
                        <p class="text-[11px] text-[#64748B] font-medium">Meter Lalu</p>
                        <p class="text-base font-bold text-[#0F172A] font-mono tabular-nums mt-0.5">
                            {{ $pembayaran->tagihan->bacaanMeter->angka_meter_bulanlalu ?? 4198 }} <span class="text-xs text-[#64748B] font-sans">m³</span>
                        </p>
                    </div>
                    <div class="p-3.5 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0]">
                        <p class="text-[11px] text-[#64748B] font-medium">Meter Sekarang</p>
                        <p class="text-base font-bold text-[#2563EB] font-mono tabular-nums mt-0.5">
                            {{ $pembayaran->tagihan->bacaanMeter->angka_meter ?? 4224 }} <span class="text-xs text-[#64748B] font-sans">m³</span>
                        </p>
                    </div>
                    <div class="p-3.5 bg-[#EFF6FF] rounded-xl border border-[#BFDBFE]">
                        <p class="text-[11px] text-[#1E40AF] font-bold">Total Volume Pakai</p>
                        <p class="text-base font-extrabold text-[#1D4ED8] font-mono tabular-nums mt-0.5">
                            {{ $pembayaran->tagihan->volume_pemakaian }} <span class="text-xs font-sans">m³</span>
                        </p>
                    </div>
                </div>

                @php
                    $volume = (float) $pembayaran->tagihan->volume_pemakaian;
                    $t1Vol = min(10, $volume);
                    $t1Cost = $t1Vol * 2000;
                    $t2Vol = max(0, min(10, $volume - 10));
                    $t2Cost = $t2Vol * 3000;
                    $t3Vol = max(0, $volume - 20);
                    $t3Cost = $t3Vol * 5000;
                @endphp
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="bg-[#F8FAFC] text-[#64748B] font-bold border-b border-[#E2E8F0]">
                            <th class="py-2.5 px-3">Tingkatan Tarif Progresif</th>
                            <th class="py-2.5 px-3 text-center">Volume (m³)</th>
                            <th class="py-2.5 px-3 text-right">Tarif / m³</th>
                            <th class="py-2.5 px-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        <tr>
                            <td class="py-2.5 px-3 font-medium text-[#0F172A]">Blok 1 (0 – 10 m³) • Tarif Dasar Subsidi</td>
                            <td class="py-2.5 px-3 text-center font-mono tabular-nums">{{ $t1Vol }} m³</td>
                            <td class="py-2.5 px-3 text-right font-mono tabular-nums">Rp 2.000</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold tabular-nums">Rp {{ number_format($t1Cost, 0, ',', '.') }}</td>
                        </tr>
                        @if($t2Vol > 0)
                        <tr>
                            <td class="py-2.5 px-3 font-medium text-[#0F172A]">Blok 2 (11 – 20 m³) • Tarif Menengah</td>
                            <td class="py-2.5 px-3 text-center font-mono tabular-nums">{{ $t2Vol }} m³</td>
                            <td class="py-2.5 px-3 text-right font-mono tabular-nums">Rp 3.000</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold tabular-nums">Rp {{ number_format($t2Cost, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($t3Vol > 0)
                        <tr>
                            <td class="py-2.5 px-3 font-medium text-[#0F172A]">Blok 3 (> 20 m³) • Tarif Konsumsi Tinggi</td>
                            <td class="py-2.5 px-3 text-center font-mono tabular-nums">{{ $t3Vol }} m³</td>
                            <td class="py-2.5 px-3 text-right font-mono tabular-nums">Rp 5.000</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold tabular-nums">Rp {{ number_format($t3Cost, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-[#0F172A] bg-[#F8FAFC]">
                            <th colspan="3" class="py-3 px-3 text-xs font-bold text-[#0F172A]">TOTAL DIBAYARKAN (LUNAS)</th>
                            <th class="py-3 px-3 text-sm font-extrabold text-[#0F172A] text-right font-mono tabular-nums">
                                Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- 4. Cryptographic Validation & Security Block --}}
            <div class="pt-6">
                <div class="bg-[#0F172A] text-white p-5 rounded-2xl flex flex-col sm:flex-row items-center gap-5">

                    {{-- QR Code Asli (SimpleSoftwareIO/simple-qrcode) berisi URL verifikasi publik --}}
                    <div class="bg-white p-2.5 rounded-xl shrink-0 shadow-md flex flex-col items-center">
                        <div class="w-36 h-36 [&>svg]:w-full [&>svg]:h-full">{!! $qrSvg !!}</div>
                        <span class="text-[8px] text-[#64748B] font-semibold mt-1">Scan untuk verifikasi keaslian</span>
                    </div>

                    {{-- Cryptographic Digest & Signature Metadata --}}
                    <div class="space-y-1.5 flex-1 min-w-0 text-left">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#60A5FA] animate-pulse"></span>
                            <span class="text-xs font-bold text-[#60A5FA] tracking-wider uppercase">TANDA TANGAN DIGITAL RESMI (RSA-2048)</span>
                        </div>
                        <p class="text-[11px] text-[#94A3B8] leading-relaxed">
                            Dokumen ini dienkripsi dan diotorisasi secara kriptografis menggunakan algoritma RSA-2048 dengan digest SHA-256 oleh Pengelola Lingkungan Grand Sulawesi.
                        </p>

                        <div class="pt-1 space-y-1">
                            <div class="flex items-baseline gap-2">
                                <span class="text-[10px] font-mono text-[#64748B] shrink-0">SHA-256 Digest:</span>
                                <span class="text-[10px] font-mono text-[#60A5FA] truncate">{{ $hashSha256 }}</span>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-[10px] font-mono text-[#64748B] shrink-0">RSA-2048 Sig:</span>
                                <span class="text-[10px] font-mono text-[#E2E8F0] truncate">{{ substr($signatureRsa, 0, 48) }}...{{ substr($signatureRsa, -16) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Signatures Footer --}}
                <div class="mt-8 pt-4 flex justify-between items-end text-xs">
                    <div>
                        <p class="text-[10px] text-[#64748B] uppercase tracking-wider font-semibold">Catatan Penting:</p>
                        <p class="text-[11px] text-[#64748B] mt-0.5">Nota ini merupakan bukti pelunasan sah pengganti kuitansi konvensional.</p>
                        <p class="text-[11px] text-[#64748B]">Simpan nota digital ini untuk pencatatan dan rekonsiliasi tahunan.</p>
                    </div>
                    <div class="text-center w-48">
                        <p class="text-[11px] text-[#64748B] mb-12">Makassar, {{ \Carbon\Carbon::parse($pembayaran->tgl_bayar)->translatedFormat('d F Y') }}</p>
                        <div class="border-t border-[#0F172A] pt-1">
                            <p class="font-bold text-[#0F172A]">Administrator Keuangan</p>
                            <p class="text-[10px] text-[#64748B]">Pengelola Grand Sulawesi</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

</body>
</html>
