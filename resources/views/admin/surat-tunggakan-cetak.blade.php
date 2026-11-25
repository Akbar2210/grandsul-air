<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Penyampaian — {{ $surat['nomor_rumah'] }} ({{ $surat['nama_pemilik'] }})</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Calibri', 'Arial', 'Helvetica', sans-serif;
            color: #111827;
        }
        .surat-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 18mm 22mm 20mm 22mm;
            box-sizing: border-box;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }
            body {
                background: #FFFFFF !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .surat-sheet {
                width: 100%;
                min-height: auto;
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-[#E2E8F0] py-6 sm:py-8 antialiased">

    {{-- Top Action Bar (No Print) --}}
    <div class="max-w-[210mm] mx-auto px-4 sm:px-0 mb-5 no-print flex items-center justify-between gap-3">
        <a href="{{ route('admin.surat-tunggakan') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#334155] hover:text-[#0F172A]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Daftar Surat Tunggakan
        </a>

        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white text-xs font-bold flex items-center gap-2 cursor-pointer shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M6 9V2h12v7"></path>
                <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <span>Cetak / Unduh PDF Surat</span>
        </button>
    </div>

    {{-- Lembar Surat Resmi A4 (Template PT. ARISTA JAYA) --}}
    <main class="surat-sheet border border-[#CBD5E1] shadow-md relative">

        {{-- Kode Unit Pojok Kanan Atas (seperti L.16 pada template fisik) --}}
        <div class="absolute top-6 right-8 text-sm font-semibold text-[#334155] font-mono">
            {{ $surat['nomor_rumah'] }}
        </div>

        {{-- ==================== KOP SURAT PT. ARISTA JAYA ==================== --}}
        <header class="pb-3 border-b-2 border-[#1D4ED8]">
            <div class="flex items-center gap-4">
                {{-- Logo Monogram AJ --}}
                <div class="w-20 h-20 flex items-center justify-center shrink-0">
                    <svg viewBox="0 0 100 100" class="w-18 h-18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M50 8L12 36V88H88V36L50 8Z" stroke="#334155" stroke-width="3.5" fill="none"/>
                        <path d="M50 18L22 39V80H78V39L50 18Z" stroke="#64748B" stroke-width="1.5" fill="none"/>
                        <text x="50" y="68" text-anchor="middle" font-family="Georgia, serif" font-size="36" font-weight="bold" fill="#1E293B">AJ</text>
                    </svg>
                </div>

                <div class="flex-1 text-center pr-6">
                    <h1 class="text-[24px] font-bold tracking-wide text-[#991B1B] leading-tight" style="font-family: 'Georgia', serif;">
                        PT. ARISTA JAYA
                    </h1>
                    <p class="text-[14px] font-semibold text-[#1E40AF] tracking-wide mt-0.5">
                        General Kontraktor dan Real Estate
                    </p>
                </div>
            </div>

            <div class="text-center text-[10px] font-semibold text-[#1E293B] mt-2 leading-snug">
                <p>KANTOR PUSAT : Jl. Tun Abdul Razak-Perum Bumi Aroepala Ruko 10-12 Telp/Fax. 2079998-8223011 Gowa</p>
                <p>CABANG PAREPARE : Jl. Jend. M. Yusuf/Lingkar Blok A No. 12 Kel. Lompoe-Kec. Bacukiki-Telp. 0421-3313537</p>
            </div>
        </header>

        {{-- ==================== JUDUL SURAT ==================== --}}
        <div class="text-center mt-6 mb-7">
            <h2 class="text-[17px] font-bold tracking-wider uppercase underline underline-offset-4 text-[#0F172A]">
                SURAT PENYAMPAIAN
            </h2>
            <p class="text-[12px] text-[#334155] mt-1 font-medium">
                {{ $surat['nomor_surat'] }}
            </p>
        </div>

        {{-- ==================== ISI SURAT ==================== --}}
        <div class="text-[13.5px] leading-[1.75] text-[#111827] space-y-4">
            <p>Dengan hormat,</p>

            <p class="indent-10 text-justify">
                Sesuai dengan catatan administrasi di kantor kami, rumah type 36 Blok <strong>{{ $surat['nomor_rumah'] }}</strong>
                Perumahan <strong><em>&ldquo;GRAND SULAWESI PAREPARE&rdquo;</em></strong> an.
                <strong>{{ $surat['nama_pemilik'] }}</strong> belum melunasi tagihan rekening air bor periode
                <strong>{{ $surat['periode_rentang'] }}</strong>, adapun perinciannya sbb.
            </p>

            {{-- Rincian Biaya & Denda --}}
            <div class="pl-10 pr-6 py-2">
                <table class="w-full max-w-[440px] text-[13.5px]">
                    <tr>
                        <td class="py-1 w-64">1. &nbsp; Total Pemakaian</td>
                        <td class="py-1 w-10 text-right">Rp.</td>
                        <td class="py-1 text-right font-mono tabular-nums">{{ number_format($surat['total_pemakaian'], 0, '.', '.') }},-</td>
                    </tr>
                    <tr>
                        <td class="py-1 border-b border-[#111827]">2. &nbsp; Denda (@Rp.{{ number_format($surat['denda_per_bulan'], 0, '.', '.') }},-/bln)</td>
                        <td class="py-1 border-b border-[#111827] text-right">Rp.</td>
                        <td class="py-1 border-b border-[#111827] text-right font-mono tabular-nums">{{ number_format($surat['total_denda'], 0, '.', '.') }},-</td>
                    </tr>
                    <tr class="font-bold">
                        <td class="pt-2 text-right pr-4">Total</td>
                        <td class="pt-2 text-right">Rp.</td>
                        <td class="pt-2 text-right font-mono tabular-nums">{{ number_format($surat['grand_total'], 0, '.', '.') }},-</td>
                    </tr>
                </table>
            </div>

            <p class="indent-10 text-justify">
                Di mohon untuk segera melunasi semua tanggungan yang ada, batas pembayaran sampai tanggal
                <strong>{{ $surat['batas_pembayaran'] }}</strong>, apabila belum melakukan pembayaran sampai tanggal yang telah
                ditentukan maka pihak developer akan <strong>menyegel</strong> kilometer di unit rumah yang menjadi objek pemasangan
                (<strong><em>Jika user menolak disegel atau membuka sendiri segel tanpa melakukan pembayaran maka dengan sangat terpaksa kilometer air di rumah tersebut akan dicabut tanpa pemberitahuan sebelumnya</em></strong>).
            </p>

            <p class="indent-10">
                Demikian kami sampaikan atas segala perhatiannya, kami ucapkan banyak terimakasih.
            </p>
        </div>

        {{-- ==================== TANDA TANGAN ==================== --}}
        <div class="mt-10 text-[13.5px] leading-relaxed text-[#111827]">
            <p>Makassar, {{ $surat['tanggal_surat'] }}</p>

            <div class="mt-4">
                <p class="font-bold">PT. ARISTA JAYA</p>
                <p>General Kontraktor dan Real Estate</p>
                <p class="mt-1">Ttd,</p>

                <div class="h-20"></div>

                <p class="font-bold underline underline-offset-4">Ir. Muh. Arief Mone, MT</p>
                <p>Direktur</p>
            </div>
        </div>

    </main>

</body>
</html>
