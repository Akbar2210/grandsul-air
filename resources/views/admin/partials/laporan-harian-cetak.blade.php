<div class="report-sheet">
    <div class="header-box">
        <p class="header-title">LAPORAN HARIAN TRANSAKSI &amp; PENCATATAN METER AIR</p>
        <p class="header-sub">Perumahan Grand Sulawesi Parepare — PT. Arista Jaya (General Kontraktor &amp; Real Estate)</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 18%;"><strong>Tanggal Laporan</strong></td>
            <td style="width: 32%;">: {{ $tanggalFormatted }}</td>
            <td style="width: 18%;"><strong>Filter Blok</strong></td>
            <td style="width: 32%;">: {{ $selectedBlokName ?? 'Semua Blok' }}</td>
        </tr>
        <tr>
            <td><strong>Mode Laporan</strong></td>
            <td>: Laporan Harian (Per Hari)</td>
            <td><strong>Dicetak Pada</strong></td>
            <td>: {{ now()->translatedFormat('d F Y, H:i') }} WITA</td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            <td class="summary-card" style="width: 25%;">
                <p class="summary-label">Pemasukan Harian</p>
                <p class="summary-value">Rp {{ number_format($harianTotalMasuk, 0, ',', '.') }}</p>
                <p class="summary-foot">Tunai: Rp {{ number_format($harianTotalTunai, 0, ',', '.') }}</p>
            </td>
            <td class="summary-card" style="width: 25%;">
                <p class="summary-label">Transfer Masuk</p>
                <p class="summary-value paid">Rp {{ number_format($harianTotalTransfer, 0, ',', '.') }}</p>
                <p class="summary-foot">{{ $tanggalFormatted }}</p>
            </td>
            <td class="summary-card" style="width: 25%;">
                <p class="summary-label">Transaksi Bayar</p>
                <p class="summary-value">{{ number_format($harianJumlahTransaksi, 0, ',', '.') }}</p>
                <p class="summary-foot">Masuk kas/bank</p>
            </td>
            <td class="summary-card" style="width: 25%;">
                <p class="summary-label">Pencatatan Meter</p>
                <p class="summary-value">{{ number_format($harianJumlahBacaan, 0, ',', '.') }} Unit</p>
                <p class="summary-foot">Volume: {{ number_format($harianTotalVolume, 0, ',', '.') }} m³</p>
            </td>
        </tr>
    </table>

    <p class="section-heading">1. Rekap Pembayaran Masuk ({{ $harianJumlahTransaksi }} Transaksi)</p>
    <table class="data-table" style="margin-bottom: 12px;">
        <thead>
            <tr>
                <th class="text-left">ID</th>
                <th class="text-center">Waktu</th>
                <th class="text-left">Unit</th>
                <th class="text-left">Pemilik</th>
                <th class="text-center">Metode</th>
                <th class="text-right">Nominal Bayar</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembayaranHarian->take(12) as $p)
                <tr>
                    <td class="mono">#{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-center mono">{{ $p->created_at ? $p->created_at->format('H:i') : '-' }}</td>
                    <td><strong>{{ $p->tagihan->unit->nomor_rumah ?? '-' }}</strong></td>
                    <td>{{ $p->tagihan->unit->nama_pemilik ?? '-' }}</td>
                    <td class="text-center">{{ $p->metode_bayar }}</td>
                    <td class="text-right mono text-paid">Rp {{ number_format($p->jumlah_bayar, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $p->status_pembayaran ?? 'Sukses' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #64748B;">Tidak ada transaksi pembayaran tercatat pada tanggal {{ $tanggalFormatted }}.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="section-heading">2. Rekap Pencatatan Meter Lapangan ({{ $harianJumlahBacaan }} Unit)</p>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center">Waktu</th>
                <th class="text-left">Unit</th>
                <th class="text-left">Petugas</th>
                <th class="text-right">Stand Lalu</th>
                <th class="text-right">Stand Kini</th>
                <th class="text-right">Volume</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bacaanHarian->take(12) as $b)
                @php
                    $vol = max(0, (float) $b->angka_meter - (float) $b->angka_meter_bulanlalu);
                @endphp
                <tr>
                    <td class="text-center mono">{{ $b->created_at ? $b->created_at->format('H:i') : '-' }}</td>
                    <td><strong>{{ $b->unit->nomor_rumah ?? '-' }}</strong></td>
                    <td>{{ $b->petugas->name ?? 'Petugas' }}</td>
                    <td class="text-right mono">{{ number_format($b->angka_meter_bulanlalu, 0, ',', '.') }} m³</td>
                    <td class="text-right mono">{{ number_format($b->angka_meter, 0, ',', '.') }} m³</td>
                    <td class="text-right mono"><strong>{{ number_format($vol, 0, ',', '.') }} m³</strong></td>
                    <td class="text-center">{{ strtoupper($b->status_verifikasi) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #64748B;">Tidak ada pencatatan meteran pada tanggal {{ $tanggalFormatted }}.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem Pengelolaan Air Grand Sulawesi Parepare • Operator: {{ Auth::user()->name ?? 'Admin Pengelola Air' }}
    </div>
</div>
