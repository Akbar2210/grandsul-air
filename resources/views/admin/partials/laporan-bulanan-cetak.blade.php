<div class="report-sheet">
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
</div>
