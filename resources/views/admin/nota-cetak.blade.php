<!DOCTYPE html>
<html lang="id">
<head>
    @php
        $tagihan = $tagihan ?? $pembayaran->tagihan;
        $kodeNota = 'TGH-2611-' . str_pad((string) $tagihan->id, 4, '0', STR_PAD_LEFT);
        $isLunas = ($tagihan->status_bayar === 'lunas');
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekening Air Sumur Bor #{{ $kodeNota }} — Grand Sulawesi Parepare</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Courier New', Courier, 'Consolas', monospace;
            color: #111827;
        }
        .ui-sans {
            font-family: 'Poppins', system-ui, sans-serif;
        }
        .nota-table {
            width: 100%;
            border-collapse: collapse;
        }
        .nota-table th,
        .nota-table td {
            border: 1px solid #334155;
            padding: 3px 6px;
            font-size: 11.5px;
            line-height: 1.35;
        }
        .nota-table th {
            background-color: #F8FAFC;
            font-weight: 700;
            text-align: center;
        }
        @media print {
            @page {
                size: A5 landscape;
                margin: 6mm;
            }
            body {
                background: #FFFFFF !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .nota-sheet {
                box-shadow: none !important;
                border: 1px solid #334155 !important;
            }
        }
    </style>
</head>
<body class="bg-[#E2E8F0] py-6 sm:py-8 antialiased">

    {{-- Top Action Bar (No Print) --}}
    <div class="max-w-[820px] mx-auto px-4 mb-5 no-print ui-sans flex flex-col sm:flex-row items-center justify-between gap-3">
        <a href="{{ route('admin.tagihan') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#334155] hover:text-[#0F172A]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Daftar Tagihan
        </a>

        <div class="flex items-center gap-2.5">
            @if(! $isLunas)
                <a href="{{ route('admin.pembayaran', ['tagihan_id' => $tagihan->id]) }}"
                   class="px-4 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold flex items-center gap-1.5">
                    <span>Catat Pembayaran Nota Ini</span>
                </a>
            @endif
            <button onclick="window.print()" class="px-5 py-2.5 bg-[#0F172A] hover:bg-[#1E293B] text-white text-xs font-bold flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.673-2.684-.673-4.008 0-4.411 3.582-8 8-8s8 3.589 8 8c0 1.324-.433 2.932-.673 4.008M3 17.25h18m-15 3h12"/></svg>
                <span>Cetak Nota Fisik</span>
            </button>
        </div>
    </div>

    @php
        $volume = (float) ($tagihan->volume_pemakaian ?? 0);
        $meterLalu = (float) ($tagihan->bacaanMeter->angka_meter_bulanlalu ?? 0);
        $meterKini = (float) ($tagihan->bacaanMeter->angka_meter ?? ($meterLalu + $volume));

        // Rincian tarif progresif 3 tier
        $t1Vol = min(10, $volume);
        $t1Rate = 2000;
        $t1Cost = $t1Vol * $t1Rate;

        $t2Vol = max(0, min(10, $volume - 10));
        $t2Rate = 3000;
        $t2Cost = $t2Vol * $t2Rate;

        $t3Vol = max(0, $volume - 20);
        $t3Rate = 5000;
        $t3Cost = $t3Vol * $t3Rate;

        $hargaAir = $t1Cost + $t2Cost + $t3Cost;
        $totalTagihanDb = (float) ($tagihan->total_tagihan ?? $hargaAir);
        $biayaAdm = max(0, $totalTagihanDb - $hargaAir);
        $denda = isset($pembayaran) && $pembayaran ? max(0, (float) $pembayaran->jumlah_bayar - ($hargaAir + $biayaAdm)) : 0;
        $totalBayar = isset($pembayaran) && $pembayaran ? (float) $pembayaran->jumlah_bayar : ($hargaAir + $biayaAdm);

        try {
            $periodeDate = \Carbon\Carbon::createFromFormat('Y-m', (string) $tagihan->priode);
            $periodeLabel = strtoupper($periodeDate->translatedFormat('F Y'));
        } catch (\Throwable) {
            $periodeLabel = strtoupper(\Carbon\Carbon::now()->translatedFormat('F Y'));
        }
    @endphp

    {{-- ==================== LEMBAR NOTA RESMI MITRA (PT. ARISTA JAYA - GRAND SULAWESI PAREPARE) ==================== --}}
    <main class="max-w-[820px] mx-auto px-4">
        <div class="nota-sheet bg-white border border-[#94A3B8] shadow-md p-6 sm:p-8">

            {{-- 1. KOP NOTA MITRA --}}
            <div class="flex items-start justify-between gap-4 pb-2.5 border-b-2 border-[#1E293B]">
                <div class="w-40 shrink-0 text-center">
                    <svg viewBox="0 0 100 72" class="w-14 h-10 mx-auto" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M50 4L14 28V66H86V28L50 4Z" stroke="#1E293B" stroke-width="3.5" fill="none"/>
                        <text x="50" y="54" text-anchor="middle" font-family="Georgia, serif" font-size="30" font-weight="bold" fill="#1E293B">AJ</text>
                    </svg>
                    <p class="text-[9.5px] font-bold tracking-wide text-[#0F172A] leading-tight mt-0.5">PT. ARISTA JAYA</p>
                    <p class="text-[8px] text-[#334155] leading-tight">General Kontraktor dan Real Estate</p>
                </div>

                <div class="flex-1 text-center pr-6">
                    <p class="text-[12px] font-bold tracking-widest uppercase text-[#0F172A]">PERUMAHAN</p>
                    <h1 class="text-[17px] font-extrabold tracking-wider uppercase text-[#0F172A] leading-tight">
                        GRAND SULAWESI PAREPARE
                    </h1>
                    <p class="text-[9px] text-[#334155] mt-1 leading-snug">
                        KANTOR PUSAT : Jl. Tun Abdul Razak-Perum Bumi Aroepala Ruko 10-12 Telp/Fax 2079998-8223011 Gowa
                    </p>
                    <p class="text-[9px] text-[#334155] leading-snug">
                        CABANG PAREPARE : Jl. Jend. M. Yusuf/Lingkar Blok A No.12 Kel. Lompoe-Kec. Bacukiki Telp. 0421-3313537
                    </p>
                </div>
            </div>

            {{-- Subjudul Rekening --}}
            <div class="text-center py-2">
                <p class="text-[12px] font-bold tracking-widest uppercase text-[#0F172A]">REKENING AIR SUMUR BOR</p>
                <p class="text-[11.5px] font-bold tracking-wider uppercase text-[#0F172A]">BULAN {{ $periodeLabel }}</p>
            </div>

            {{-- 2. TABEL IDENTITAS PELANGGAN --}}
            <table class="nota-table mb-3">
                <tr>
                    <td class="w-[15%] font-bold">No. Langg</td>
                    <td class="w-[15%] font-bold">Gol. Tarif</td>
                    <td class="w-[13%] font-bold">Tn/Ny. :</td>
                    <td class="w-[57%] text-center font-bold uppercase">{{ $tagihan->unit->nama_pemilik ?? 'WARGA GRAND SULAWESI' }}</td>
                </tr>
                <tr>
                    <td class="font-bold">{{ $tagihan->unit->nomor_rumah ?? '—' }}</td>
                    <td class="text-center">RT-1</td>
                    <td class="font-bold">Alamat :</td>
                    <td class="text-center font-bold">{{ $tagihan->unit->nomor_rumah ?? '—' }} ({{ $tagihan->unit->blok->nama_blok ?? 'Blok A' }})</td>
                </tr>
            </table>

            {{-- 3. DUA TABEL BERDAMPINGAN: HARGA AIR (KIRI) & ADM DLL (KANAN) --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-3">
                {{-- Kiri: Harga Satuan & Pemakaian --}}
                <div class="md:col-span-7">
                    <table class="nota-table">
                        <thead>
                            <tr>
                                <th colspan="2">Harga Satuan</th>
                                <th class="w-[18%]">Pemakaian</th>
                                <th class="w-[30%]">Harga Air</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="w-[24%]">0-10 M3</td>
                                <td class="w-[28%] text-right">Rp {{ number_format($t1Rate, 0, ',', '.') }}</td>
                                <td class="text-center">{{ number_format($t1Vol, 0) }}</td>
                                <td class="text-right">Rp {{ number_format($t1Cost, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>11-20 M3</td>
                                <td class="text-right">Rp {{ number_format($t2Rate, 0, ',', '.') }}</td>
                                <td class="text-center">{{ number_format($t2Vol, 0) }}</td>
                                <td class="text-right">Rp {{ number_format($t2Cost, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>&gt; 20 M3</td>
                                <td class="text-right">Rp {{ number_format($t3Rate, 0, ',', '.') }}</td>
                                <td class="text-center">{{ number_format($t3Vol, 0) }}</td>
                                <td class="text-right">Rp {{ number_format($t3Cost, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="font-bold bg-[#F8FAFC]">
                                <td colspan="3" class="text-center">TOTAL</td>
                                <td class="text-right">Rp {{ number_format($hargaAir, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Kanan: ADM dll. --}}
                <div class="md:col-span-5">
                    <table class="nota-table">
                        <thead>
                            <tr>
                                <th colspan="2">ADM dll.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="w-[45%]">Pem Meter</td>
                                <td class="text-right">-</td>
                            </tr>
                            <tr>
                                <td>PPN 10%</td>
                                <td class="text-right">Rp -</td>
                            </tr>
                            <tr>
                                <td>Biaya ADM</td>
                                <td class="text-right">Rp {{ number_format($biayaAdm, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="font-bold bg-[#F8FAFC]">
                                <td>TOTAL</td>
                                <td class="text-right">Rp {{ number_format($biayaAdm, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 4. BAGIAN BAWAH: STAND METER + TTD ADMIN (KIRI), QR VERIFIKASI RSA-2048 (TENGAH), TOTAL BAYAR (KANAN) --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start pt-1">

                {{-- Kolom Kiri: Stand Meter & Tanda Tangan Admin --}}
                <div class="md:col-span-4 space-y-3">
                    <table class="nota-table text-center">
                        <thead>
                            <tr>
                                <th>Bulan Ini</th>
                                <th>Bulan Lalu</th>
                                <th>Pemakaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="font-bold">
                                <td>{{ number_format($meterKini, 0) }}</td>
                                <td>{{ number_format($meterLalu, 0) }}</td>
                                <td>{{ number_format($volume, 0) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="pt-1 text-center text-[11px] leading-relaxed">
                        <p>Ttd.</p>
                        <div class="h-10"></div>
                        <p class="font-bold underline">Admin</p>
                        <p>PT. Arista Jaya</p>
                    </div>
                </div>

                {{-- Kolom Tengah: PENEMPATAN QR CODE VERIFIKASI KRIPTOGRAFIS RSA-2048 --}}
                <div class="md:col-span-3 flex flex-col items-center justify-center text-center border border-[#334155] p-2 bg-[#F8FAFC]">
                    <span class="text-[8.5px] font-bold uppercase tracking-wider text-[#0F172A] mb-1">
                        VERIFIKASI QR RSA-2048
                    </span>
                    <div class="w-24 h-24 bg-white p-1 border border-[#CBD5E1] [&>svg]:w-full [&>svg]:h-full">
                        {!! $qrSvg !!}
                    </div>
                    <span class="text-[8px] font-bold text-[#0F172A] mt-1">
                        {{ $isLunas ? 'LUNAS' : 'TAGIHAN RESMI' }} • {{ $kodeNota }}
                    </span>
                    <span class="text-[7.5px] text-[#475569] break-all leading-tight mt-0.5">
                        SHA256: {{ substr($hashSha256, 0, 14) }}...
                    </span>
                </div>

                {{-- Kolom Kanan: Ringkasan Total Bayar & Denda --}}
                <div class="md:col-span-5 space-y-2">
                    <table class="nota-table">
                        <thead>
                            <tr>
                                <th>Harga Air</th>
                                <th>ADM dll.</th>
                                <th>TOTAL BAYAR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="font-bold text-right">
                                <td>Rp {{ number_format($hargaAir, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($biayaAdm, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($hargaAir + $biayaAdm, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="font-bold">DENDA</td>
                                <td class="text-right">Rp {{ number_format($denda, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="font-bold bg-[#F8FAFC]">
                                <td colspan="2">TOTAL BAYAR</td>
                                <td class="text-right text-[12.5px]">Rp {{ number_format($totalBayar, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="text-[9.5px] text-[#334155] flex items-center justify-between pt-1">
                        <span>Status: <strong>{{ $isLunas ? 'LUNAS (' . ($pembayaran->metode_bayar ?? 'Tunai') . ')' : 'BELUM DIBAYAR (NOTA TAGIHAN)' }}</strong></span>
                        <span>Jatuh Tempo: <strong>{{ \Carbon\Carbon::parse($tagihan->tgl_jatuh_tempo)->format('d/m/Y') }}</strong></span>
                    </div>
                </div>

            </div>

        </div>
    </main>

</body>
</html>
