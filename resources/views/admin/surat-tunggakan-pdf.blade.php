<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Penyampaian — {{ $surat['nomor_rumah'] }} ({{ $surat['nama_pemilik'] }})</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 22mm 20mm 22mm;
        }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #111827;
            font-size: 12px;
            line-height: 1.65;
            margin: 0;
            padding: 0;
        }
        .unit-badge {
            text-align: right;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 12px;
            font-weight: bold;
            color: #334155;
            margin-bottom: 4px;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #1D4ED8;
            padding-bottom: 8px;
        }
        .kop-logo-cell {
            width: 72px;
            vertical-align: middle;
            text-align: center;
            padding-bottom: 6px;
        }
        .kop-logo-box {
            width: 58px;
            height: 58px;
            border: 2.5px solid #334155;
            line-height: 58px;
            text-align: center;
            font-family: DejaVu Serif, Georgia, serif;
            font-size: 24px;
            font-weight: bold;
            color: #1E293B;
            margin: 0 auto;
        }
        .kop-title-cell {
            text-align: center;
            vertical-align: middle;
            padding-right: 40px;
            padding-bottom: 6px;
        }
        .kop-company {
            font-family: DejaVu Serif, Georgia, serif;
            font-size: 22px;
            font-weight: bold;
            color: #991B1B;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .kop-subtitle {
            font-size: 13px;
            font-weight: bold;
            color: #1E40AF;
            margin: 2px 0 0 0;
        }
        .kop-address {
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            color: #1E293B;
            line-height: 1.4;
            padding-bottom: 8px;
        }
        .judul-wrap {
            text-align: center;
            margin: 22px 0 24px 0;
        }
        .judul-main {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            letter-spacing: 1px;
            margin: 0;
            color: #0F172A;
        }
        .judul-nomor {
            font-size: 11px;
            color: #334155;
            margin: 4px 0 0 0;
        }
        p {
            margin: 0 0 12px 0;
        }
        .indent {
            text-indent: 34px;
            text-align: justify;
        }
        .rincian-wrap {
            margin: 14px 0 18px 34px;
        }
        .rincian-table {
            width: 390px;
            border-collapse: collapse;
            font-size: 12px;
        }
        .rincian-table td {
            padding: 4px 2px;
            vertical-align: top;
        }
        .rincian-table .label {
            width: 240px;
        }
        .rincian-table .rp {
            width: 32px;
            text-align: right;
        }
        .rincian-table .nominal {
            text-align: right;
            font-family: DejaVu Sans Mono, monospace;
        }
        .border-b {
            border-bottom: 1px solid #111827;
        }
        .total-row td {
            font-weight: bold;
            padding-top: 7px;
        }
        .ttd-wrap {
            margin-top: 34px;
            font-size: 12px;
            line-height: 1.5;
        }
        .ttd-space {
            height: 64px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="unit-badge">{{ $surat['nomor_rumah'] }}</div>

    <table class="kop-table">
        <tr>
            <td class="kop-logo-cell">
                <div class="kop-logo-box">AJ</div>
            </td>
            <td class="kop-title-cell">
                <p class="kop-company">PT. ARISTA JAYA</p>
                <p class="kop-subtitle">General Kontraktor dan Real Estate</p>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="kop-address">
                KANTOR PUSAT : Jl. Tun Abdul Razak-Perum Bumi Aroepala Ruko 10-12 Telp/Fax. 2079998-8223011 Gowa<br>
                CABANG PAREPARE : Jl. Jend. M. Yusuf/Lingkar Blok A No. 12 Kel. Lompoe-Kec. Bacukiki-Telp. 0421-3313537
            </td>
        </tr>
    </table>

    <div class="judul-wrap">
        <p class="judul-main">SURAT PENYAMPAIAN</p>
        <p class="judul-nomor">{{ $surat['nomor_surat'] }}</p>
    </div>

    <p>Dengan hormat,</p>

    <p class="indent">
        Sesuai dengan catatan administrasi di kantor kami, rumah type 36 Blok <strong>{{ $surat['nomor_rumah'] }}</strong>
        Perumahan <strong><em>&ldquo;GRAND SULAWESI PAREPARE&rdquo;</em></strong> an.
        <strong>{{ $surat['nama_pemilik'] }}</strong> belum melunasi tagihan rekening air bor selama
        <strong>{{ $surat['jumlah_bulan'] }} bulan</strong> periode
        <strong>{{ $surat['periode_rentang'] }}</strong>, adapun perinciannya sbb.
    </p>

    <div class="rincian-wrap">
        <table class="rincian-table">
            <tr>
                <td class="label">1. &nbsp; Total Pemakaian ({{ $surat['jumlah_bulan'] }} bulan)</td>
                <td class="rp">Rp.</td>
                <td class="nominal">{{ number_format($surat['total_pemakaian'], 0, ',', '.') }},-</td>
            </tr>
            <tr>
                <td class="label border-b">2. &nbsp; Denda (@Rp.{{ number_format($surat['denda_per_bulan'], 0, ',', '.') }},-/bln)</td>
                <td class="rp border-b">Rp.</td>
                <td class="nominal border-b">{{ number_format($surat['total_denda'], 0, ',', '.') }},-</td>
            </tr>
            <tr class="total-row">
                <td class="label" style="text-align: right; padding-right: 12px;">Total</td>
                <td class="rp">Rp.</td>
                <td class="nominal">{{ number_format($surat['grand_total'], 0, ',', '.') }},-</td>
            </tr>
        </table>
    </div>

    <p class="indent">
        Di mohon untuk segera melunasi semua tanggungan yang ada, batas pembayaran sampai tanggal
        <strong>{{ $surat['batas_pembayaran'] }}</strong>, apabila belum melakukan pembayaran sampai tanggal yang telah
        ditentukan maka pihak developer akan <strong>menyegel</strong> kilometer di unit rumah yang menjadi objek pemasangan
        (<strong><em>Jika user menolak disegel atau membuka sendiri segel tanpa melakukan pembayaran maka dengan sangat terpaksa kilometer air di rumah tersebut akan dicabut tanpa pemberitahuan sebelumnya</em></strong>).
    </p>

    <p class="indent">
        Demikian kami sampaikan atas segala perhatiannya, kami ucapkan banyak terimakasih.
    </p>

    <div class="ttd-wrap">
        <p>Makassar, {{ $surat['tanggal_surat'] }}</p>
        <p style="margin-top: 12px; margin-bottom: 2px;"><strong>PT. ARISTA JAYA</strong></p>
        <p style="margin-bottom: 2px;">General Kontraktor dan Real Estate</p>
        <p>Ttd,</p>
        <div class="ttd-space"></div>
        <p class="ttd-name" style="margin-bottom: 2px;">Ir. Muh. Arief Mone, MT</p>
        <p>Direktur</p>
    </div>
</body>
</html>
