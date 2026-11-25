<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan {{ $periodeLabel }} — Grand Sulawesi Parepare</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 14mm 16mm 14mm;
        }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #0F172A;
            font-size: 10.5px;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .header-box {
            border-bottom: 2.5px solid #0F172A;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            color: #0F172A;
            margin: 0;
            letter-spacing: 0.4px;
        }
        .header-sub {
            font-size: 10px;
            color: #475569;
            margin: 3px 0 0 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10px;
        }
        .meta-table td {
            padding: 3px 0;
        }
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px 16px -8px;
        }
        .summary-card {
            border: 1px solid #CBD5E1;
            background: #F8FAFC;
            padding: 10px 12px;
            width: 33.33%;
            vertical-align: top;
        }
        .summary-label {
            font-size: 8.5px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            margin: 0 0 4px 0;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            font-family: DejaVu Sans Mono, monospace;
            color: #0F172A;
            margin: 0;
        }
        .summary-value.paid { color: #059669; }
        .summary-value.unpaid { color: #DC2626; }
        .summary-foot {
            font-size: 8.5px;
            color: #64748B;
            margin: 4px 0 0 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        .data-table th {
            background: #0F172A;
            color: #FFFFFF;
            font-weight: bold;
            text-transform: uppercase;
            padding: 7px 6px;
            border: 1px solid #0F172A;
        }
        .data-table td {
            padding: 6px;
            border: 1px solid #CBD5E1;
        }
        .data-table tbody tr:nth-child(even) {
            background: #F8FAFC;
        }
        .data-table tfoot td {
            background: #E2E8F0;
            font-weight: bold;
            border-top: 2px solid #0F172A;
            padding: 7px 6px;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
        .text-paid { color: #059669; font-weight: bold; }
        .text-unpaid { color: #DC2626; font-weight: bold; }
        .footer {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #CBD5E1;
            font-size: 9px;
            color: #64748B;
        }
    </style>
</head>
<body>
    <div class="header-box">
        <p class="header-title">LAPORAN REKAPITULASI TAGIHAN &amp; PEMBAYARAN AIR</p>
        <p class="header-sub">Perumahan Grand Sulawesi Parepare — PT. Arista Jaya (General Kontraktor &amp; Real Estate)</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 16%;"><strong>Periode</strong></td>
            <td style="width: 34%;">: {{ $periodeLabel }} ({{ $periodeKey }})</td>
            <td style="width: 16%;"><strong>Filter Blok</strong></td>
            <td style="width: 34%;">: {{ $selectedBlokName }}</td>
        </tr>
        <tr>
            <td><strong>Filter Status</strong></td>
            <td>: {{ $selectedStatusLabel }}</td>
            <td><strong>Dicetak Pada</strong></td>
            <td>: {{ now()->translatedFormat('d F Y, H:i') }} WITA</td>
        </tr>
    </table>

    @php
        $effPersen = $grandTotalTagihan > 0 ? round(($grandTotalTerbayar / $grandTotalTagihan) * 100, 1) : 0;
    @endphp

    <table class="summary-table">
        <tr>
            <td class="summary-card">
                <p class="summary-label">Total Tagihan</p>
                <p class="summary-value">Rp {{ number_format($grandTotalTagihan, 0, ',', '.') }}</p>
                <p class="summary-foot">{{ number_format($grandTotalUnit, 0, ',', '.') }} Unit • {{ number_format($grandTotalVolume, 0, ',', '.') }} m³</p>
            </td>
            <td class="summary-card">
                <p class="summary-label">Realisasi Terbayar</p>
                <p class="summary-value paid">Rp {{ number_format($grandTotalTerbayar, 0, ',', '.') }}</p>
                <p class="summary-foot">Efisiensi Koleksi: {{ $effPersen }}%</p>
            </td>
            <td class="summary-card">
                <p class="summary-label">Belum Lunas (Piutang)</p>
                <p class="summary-value unpaid">Rp {{ number_format($grandBelumLunas, 0, ',', '.') }}</p>
                <p class="summary-foot">Selisih Tagihan - Terbayar</p>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-left">Blok Hunian</th>
                <th class="text-center">Unit</th>
                <th class="text-right">Volume (m³)</th>
                <th class="text-right">Rata-rata</th>
                <th class="text-right">Total Tagihan</th>
                <th class="text-right">Terbayar</th>
                <th class="text-right">Belum Lunas</th>
                <th class="text-center">Efisiensi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapBlok as $b)
                @php
                    $blokEff = $b['total_tagihan'] > 0 ? round(($b['terbayar'] / $b['total_tagihan']) * 100, 1) : 0;
                    $avgUnit = $b['jumlah_unit'] > 0 ? round($b['total_pemakaian'] / $b['jumlah_unit'], 1) : 0;
                @endphp
                <tr>
                    <td class="text-left"><strong>{{ $b['nama'] }}</strong></td>
                    <td class="text-center mono">{{ number_format($b['jumlah_unit'], 0, ',', '.') }}</td>
                    <td class="text-right mono">{{ number_format($b['total_pemakaian'], 0, ',', '.') }} m³</td>
                    <td class="text-right mono">{{ $avgUnit }} m³</td>
                    <td class="text-right mono">Rp {{ number_format($b['total_tagihan'], 0, ',', '.') }}</td>
                    <td class="text-right mono text-paid">Rp {{ number_format($b['terbayar'], 0, ',', '.') }}</td>
                    <td class="text-right mono text-unpaid">Rp {{ number_format($b['sisa'], 0, ',', '.') }}</td>
                    <td class="text-center mono">{{ $blokEff }}%</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="text-left">GRAND TOTAL</td>
                <td class="text-center mono">{{ number_format($grandTotalUnit, 0, ',', '.') }} Unit</td>
                <td class="text-right mono">{{ number_format($grandTotalVolume, 0, ',', '.') }} m³</td>
                <td class="text-right mono">{{ $grandTotalUnit > 0 ? round($grandTotalVolume / $grandTotalUnit, 1) : 0 }} m³</td>
                <td class="text-right mono">Rp {{ number_format($grandTotalTagihan, 0, ',', '.') }}</td>
                <td class="text-right mono text-paid">Rp {{ number_format($grandTotalTerbayar, 0, ',', '.') }}</td>
                <td class="text-right mono text-unpaid">Rp {{ number_format($grandBelumLunas, 0, ',', '.') }}</td>
                <td class="text-center mono">{{ $effPersen }}%</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem Pengelolaan Air Grand Sulawesi Parepare • Operator: {{ Auth::user()->name ?? 'Admin Pengelola Air' }}
    </div>
</body>
</html>
