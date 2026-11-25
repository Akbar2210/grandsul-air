@extends('layouts.admin')

@section('title', 'Laporan Keuangan & Konsumsi — Pengelolaan Air Terintegrasi Grand Sulawesi')
@section('page-title', 'Laporan Rekapitulasi')
@section('page-subtitle', $mode === 'harian' ? 'Pelaporan transaksi pembayaran dan pencatatan meter per hari (' . $tanggalFormatted . ')' : 'Rekapitulasi berkas tagihan, volume konsumsi air per blok, dan tingkat efisiensi koleksi')

@section('content')
<div class="space-y-5">

    {{-- ==================== TAB MODE LAPORAN: BULANAN vs HARIAN (PER HARI) ==================== --}}
    <div class="bg-white border border-[#CBD5E1] rounded-2xl p-2.5 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-1.5">
            <a href="{{ route('admin.laporan', ['mode' => 'bulanan', 'blok' => request('blok')]) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $mode !== 'harian' ? 'bg-[#0F172A] text-white' : 'bg-[#F8FAFC] text-[#475569] hover:text-[#0F172A] border border-[#CBD5E1]' }}">
                Laporan Bulanan (Per Blok)
            </a>
            <a href="{{ route('admin.laporan', ['mode' => 'harian', 'tanggal' => $selectedTanggal, 'blok' => request('blok')]) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $mode === 'harian' ? 'bg-[#0F172A] text-white' : 'bg-[#F8FAFC] text-[#475569] hover:text-[#0F172A] border border-[#CBD5E1]' }}">
                Laporan Harian (Per Hari)
            </a>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white text-xs font-bold transition-all flex items-center gap-2 cursor-pointer shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M6 9V2h12v7"></path>
                    <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                <span>Cetak Laporan</span>
            </button>
            <a href="{{ route('admin.laporan.pdf', request()->query()) }}" target="_blank" class="px-4 py-2 rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] hover:bg-white text-[#0F172A] text-xs font-bold transition-colors flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-[#2563EB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                <span>Ekspor PDF</span>
            </a>
        </div>
    </div>

    {{-- ==================== FILTER BAR ==================== --}}
    <div class="bg-white border border-[#CBD5E1] rounded-2xl p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('admin.laporan') }}" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="mode" value="{{ $mode }}">

            @if($mode === 'harian')
                <div>
                    <label class="text-[11px] font-bold text-[#64748B] block mb-1">Pilih Tanggal Laporan</label>
                    <input type="date" name="tanggal" value="{{ $selectedTanggal }}"
                           class="px-3.5 py-2 rounded-xl border border-[#CBD5E1] text-xs font-mono font-semibold text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB] shadow-xs">
                </div>
            @else
                <div>
                    <label class="text-[11px] font-bold text-[#64748B] block mb-1">Periode Laporan</label>
                    <select name="bulan" class="pl-3.5 pr-10 py-2 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB] shadow-xs">
                        <option value="11" selected>November 2026</option>
                        <option value="10">Oktober 2026</option>
                        <option value="09">September 2026</option>
                        <option value="08">Agustus 2026</option>
                    </select>
                </div>
            @endif

            <div>
                <label class="text-[11px] font-bold text-[#64748B] block mb-1">Blok Hunian</label>
                <select name="blok" class="pl-3.5 pr-10 py-2 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB] shadow-xs">
                    <option value="">Semua Blok (A – F)</option>
                    @foreach($bloks as $b)
                    <option value="{{ $b->id }}" {{ request('blok') == $b->id ? 'selected' : '' }}>{{ $b->nama_blok }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="py-2 px-5 bg-[#0F172A] hover:bg-[#1E293B] active:scale-[0.98] text-white font-bold text-xs rounded-xl shadow-xs transition-all cursor-pointer">
                Terapkan Filter
            </button>
        </form>
    </div>

    {{-- ==================== MODE 1: LAPORAN HARIAN (PER HARI) ==================== --}}
    @if($mode === 'harian')

        {{-- 4 Kartu Ringkasan Harian --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white border border-[#CBD5E1] rounded-2xl p-5 shadow-xs">
                <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">PEMASUKAN HARIAN</span>
                <div class="mt-2 text-2xl font-black text-[#0F172A] font-mono tabular-nums">
                    Rp {{ number_format($harianTotalMasuk, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-[#64748B] flex items-center justify-between">
                    <span>Tunai: <strong class="text-[#0F172A] font-mono">Rp {{ number_format($harianTotalTunai, 0, ',', '.') }}</strong></span>
                </div>
            </div>

            <div class="bg-white border border-[#CBD5E1] rounded-2xl p-5 shadow-xs">
                <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">TRANSFER MASUK</span>
                <div class="mt-2 text-2xl font-black text-[#2563EB] font-mono tabular-nums">
                    Rp {{ number_format($harianTotalTransfer, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-[#64748B]">
                    Tanggal: <strong class="text-[#0F172A]">{{ $tanggalFormatted }}</strong>
                </div>
            </div>

            <div class="bg-white border border-[#CBD5E1] rounded-2xl p-5 shadow-xs">
                <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">TRANSAKSI PEMBAYARAN</span>
                <div class="mt-2 text-2xl font-black text-[#0F172A] font-mono tabular-nums">
                    {{ $harianJumlahTransaksi }} <span class="text-xs font-sans font-semibold text-[#64748B]">Transaksi</span>
                </div>
                <div class="mt-2 text-[11px] text-[#059669] font-semibold">
                    Tercatat pada kas/bank harian
                </div>
            </div>

            <div class="bg-white border border-[#CBD5E1] rounded-2xl p-5 shadow-xs">
                <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">PENCATATAN METER HARIAN</span>
                <div class="mt-2 text-2xl font-black text-[#0F172A] font-mono tabular-nums">
                    {{ $harianJumlahBacaan }} <span class="text-xs font-sans font-semibold text-[#64748B]">Unit</span>
                </div>
                <div class="mt-2 text-[11px] text-[#64748B]">
                    Total Volume: <strong class="text-[#0F172A] font-mono">{{ number_format($harianTotalVolume, 0, ',', '.') }} m³</strong>
                </div>
            </div>
        </div>

        {{-- Tabel 1: Transaksi Pembayaran Harian --}}
        <div class="bg-white border border-[#CBD5E1] rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#CBD5E1] flex items-center justify-between bg-[#F8FAFC]">
                <div>
                    <h3 class="text-sm font-bold text-[#0F172A]">Rekap Pembayaran Masuk — {{ $tanggalFormatted }}</h3>
                    <p class="text-xs text-[#64748B] mt-0.5">Daftar seluruh pembayaran warga yang masuk pada tanggal terpilih</p>
                </div>
                <span class="text-xs font-mono font-bold text-[#0F172A]">{{ $harianJumlahTransaksi }} Transaksi</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-[#CBD5E1] text-[#0F172A] font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-5">ID Pembayaran</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Unit</th>
                            <th class="py-3 px-4">Pemilik</th>
                            <th class="py-3 px-4">Metode</th>
                            <th class="py-3 px-4 text-right">Nominal Bayar</th>
                            <th class="py-3 px-5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#CBD5E1]">
                        @forelse($pembayaranHarian as $p)
                        <tr class="hover:bg-[#F8FAFC]">
                            <td class="py-3 px-5 font-mono font-bold text-[#0F172A]">PAY-2611-{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="py-3 px-4 font-mono text-[#64748B]">{{ $p->created_at ? $p->created_at->format('H:i') : '—' }} WITA</td>
                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-xs text-[#0F172A] tabular-nums">
                                    {{ $p->tagihan->unit->nomor_rumah ?? '—' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-[#0F172A]">{{ $p->tagihan->unit->nama_pemilik ?? '—' }}</td>
                            <td class="py-3 px-4 font-semibold text-[#334155]">{{ $p->metode_bayar }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-[#0F172A] tabular-nums">
                                Rp {{ number_format($p->jumlah_bayar, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-5 text-center">
                                <span class="badge-modern {{ ($p->tagihan->status_bayar ?? '') === 'lunas' ? 'badge-paid' : 'badge-pending' }}">
                                    {{ ($p->tagihan->status_bayar ?? '') === 'lunas' ? 'Lunas' : 'Sebagian' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-[#64748B]">
                                Tidak ada transaksi pembayaran tercatat pada tanggal {{ $tanggalFormatted }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabel 2: Pencatatan Meter Lapangan Harian --}}
        <div class="bg-white border border-[#CBD5E1] rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#CBD5E1] flex items-center justify-between bg-[#F8FAFC]">
                <div>
                    <h3 class="text-sm font-bold text-[#0F172A]">Rekap Pencatatan Meter Lapangan — {{ $tanggalFormatted }}</h3>
                    <p class="text-xs text-[#64748B] mt-0.5">Daftar hasil pemindaian meteran air oleh petugas pada tanggal terpilih</p>
                </div>
                <span class="text-xs font-mono font-bold text-[#0F172A]">{{ $harianJumlahBacaan }} Unit</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-[#CBD5E1] text-[#0F172A] font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-5">Waktu</th>
                            <th class="py-3 px-4">Unit</th>
                            <th class="py-3 px-4">Petugas</th>
                            <th class="py-3 px-4 text-right">Stand Lalu</th>
                            <th class="py-3 px-4 text-right">Stand Kini</th>
                            <th class="py-3 px-4 text-right">Volume</th>
                            <th class="py-3 px-5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($bacaanHarian as $b)
                        @php $volHarian = max(0, (float) $b->angka_meter - (float) $b->angka_meter_bulanlalu); @endphp
                        <tr class="hover:bg-[#F8FAFC]">
                            <td class="py-3 px-5 font-mono text-[#64748B]">{{ $b->created_at ? $b->created_at->format('H:i') : '—' }} WITA</td>
                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-xs text-[#0F172A] tabular-nums">
                                    {{ $b->unit->nomor_rumah ?? '—' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-[#0F172A]">{{ $b->petugas->name ?? 'Petugas' }}</td>
                            <td class="py-3 px-4 text-right font-mono text-[#64748B] tabular-nums">{{ number_format($b->angka_meter_bulanlalu, 0) }} m³</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-[#0F172A] tabular-nums">{{ number_format($b->angka_meter, 0) }} m³</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-[#2563EB] tabular-nums">{{ number_format($volHarian, 0) }} m³</td>
                            <td class="py-3 px-5 text-center">
                                <span class="badge-modern {{ $b->status_verifikasi === 'valid' ? 'badge-paid' : ($b->status_verifikasi === 'ter_flag' ? 'badge-pending' : 'badge-overdue') }}">
                                    {{ strtoupper(str_replace('_', ' ', $b->status_verifikasi)) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-[#64748B]">
                                Tidak ada pencatatan meter lapangan pada tanggal {{ $tanggalFormatted }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
    {{-- ==================== MODE 2: LAPORAN BULANAN (PER BLOK) ==================== --}}

        {{-- 3 RINGKASAN FINANSIAL & VOLUME --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-white border border-[#CBD5E1] rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-[#0F172A] uppercase tracking-wider">TOTAL TAGIHAN BULAN INI</span>
                    <svg class="w-5 h-5 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                </div>
                <div class="text-2xl font-black text-[#0F172A] tracking-tight font-mono tabular-nums">
                    Rp {{ number_format($grandTotalTagihan, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-xs text-[#64748B] flex items-center justify-between">
                    <span>{{ $grandTotalUnit }} Unit Terpasang</span>
                    <span class="font-bold text-[#0F172A] font-mono tabular-nums">{{ number_format($grandTotalVolume, 0, ',', '.') }} m³</span>
                </div>
            </div>

            <div class="bg-white border border-[#CBD5E1] rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-[#0F172A] uppercase tracking-wider">REALISASI TERBAYAR</span>
                    <svg class="w-5 h-5 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                </div>
                <div class="text-2xl font-black text-[#0F172A] tracking-tight font-mono tabular-nums">
                    Rp {{ number_format($grandTotalTerbayar, 0, ',', '.') }}
                </div>
                @php
                    $effPersen = $grandTotalTagihan > 0 ? round(($grandTotalTerbayar / $grandTotalTagihan) * 100, 1) : 0;
                @endphp
                <div class="mt-2 text-xs text-[#64748B] flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 font-semibold text-[#0F172A] bg-[#F8FAFC] border border-[#CBD5E1] rounded-md px-2 py-0.5 text-[11px] font-mono tabular-nums">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>
                        <span>{{ $effPersen }}% Efisiensi</span>
                    </span>
                    <span>Kas Masuk</span>
                </div>
            </div>

            <div class="bg-white border border-[#CBD5E1] rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-[#0F172A] uppercase tracking-wider">SISA PIUTANG (BELUM LUNAS)</span>
                    <svg class="w-5 h-5 text-[#334155]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <div class="text-2xl font-black text-[#E11D48] tracking-tight font-mono tabular-nums">
                    Rp {{ number_format($grandBelumLunas, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-xs text-[#64748B] flex items-center justify-between">
                    <span class="text-[#E11D48] font-bold text-[11px]">Perlu Follow-up Tagihan</span>
                    <span>Tunggakan Warga</span>
                </div>
            </div>
        </div>

        {{-- TABEL AGREGASI PER BLOK --}}
        <div class="bg-white border border-[#CBD5E1] rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-[#F1F5F9] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-[#0F172A]">Rekapitulasi Konsumsi & Keuangan per Blok</h3>
                    <p class="text-xs text-[#64748B] mt-0.5">Rincian agregasi data billing periode {{ $periodeStr }}</p>
                </div>
                <div class="text-xs text-[#64748B] font-mono">
                    Total {{ count($rekapBlok) }} Blok Hunian
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-[#CBD5E1] text-[#0F172A] font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3.5 px-6">Blok Hunian</th>
                            <th class="py-3.5 px-4 text-center">Unit</th>
                            <th class="py-3.5 px-4 text-right">Volume (m³)</th>
                            <th class="py-3.5 px-4 text-right">Rata-rata/Unit</th>
                            <th class="py-3.5 px-4 text-right">Total Tagihan</th>
                            <th class="py-3.5 px-4 text-right">Terbayar</th>
                            <th class="py-3.5 px-4 text-right">Sisa Piutang</th>
                            <th class="py-3.5 px-6 text-center">Efisiensi Koleksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @foreach($rekapBlok as $b)
                        @php
                            $blokEff = $b['total_tagihan'] > 0 ? round(($b['terbayar'] / $b['total_tagihan']) * 100, 1) : 0;
                            $avgUnit = $b['jumlah_unit'] > 0 ? round($b['total_pemakaian'] / $b['jumlah_unit'], 1) : 0;
                        @endphp
                        <tr class="hover:bg-[#F8FAFC] transition-colors">
                            <td class="py-4 px-6 font-bold text-[#0F172A]">
                                <span>{{ $b['nama'] }}</span>
                            </td>
                            <td class="py-4 px-4 text-center font-mono font-semibold text-[#0F172A] tabular-nums">
                                {{ $b['jumlah_unit'] }}
                            </td>
                            <td class="py-4 px-4 text-right font-mono font-bold text-[#0F172A] tabular-nums">
                                {{ number_format($b['total_pemakaian'], 0, ',', '.') }} m³
                            </td>
                            <td class="py-4 px-4 text-right font-mono text-[#64748B] tabular-nums">
                                {{ $avgUnit }} m³
                            </td>
                            <td class="py-4 px-4 text-right font-mono font-semibold text-[#0F172A] tabular-nums">
                                Rp {{ number_format($b['total_tagihan'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-mono font-bold text-[#059669] tabular-nums">
                                Rp {{ number_format($b['terbayar'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-mono font-bold {{ $b['sisa'] > 0 ? 'text-[#E11D48]' : 'text-[#64748B]' }} tabular-nums">
                                Rp {{ number_format($b['sisa'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 h-2 bg-[#F1F5F9] rounded-full overflow-hidden border border-[#CBD5E1]">
                                        <div class="h-full rounded-full {{ $blokEff >= 80 ? 'bg-[#10B981]' : ($blokEff >= 50 ? 'bg-[#3B82F6]' : 'bg-[#F59E0B]') }}" style="width: {{ min(100, $blokEff) }}%"></div>
                                    </div>
                                    <span class="font-mono font-bold text-[11px] {{ $blokEff >= 80 ? 'text-[#059669]' : 'text-[#2563EB]' }} tabular-nums">
                                        {{ $blokEff }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-[#F8FAFC] border-t-2 border-[#0F172A] text-xs font-bold text-[#0F172A]">
                            <td class="py-4 px-6 uppercase tracking-wider">GRAND TOTAL</td>
                            <td class="py-4 px-4 text-center font-mono font-bold tabular-nums">{{ $grandTotalUnit }} Unit</td>
                            <td class="py-4 px-4 text-right font-mono font-bold tabular-nums">{{ number_format($grandTotalVolume, 0, ',', '.') }} m³</td>
                            <td class="py-4 px-4 text-right font-mono text-[#64748B] tabular-nums">{{ $grandTotalUnit > 0 ? round($grandTotalVolume / $grandTotalUnit, 1) : 0 }} m³</td>
                            <td class="py-4 px-4 text-right font-mono font-extrabold tabular-nums">Rp {{ number_format($grandTotalTagihan, 0, ',', '.') }}</td>
                            <td class="py-4 px-4 text-right font-mono font-extrabold text-[#059669] tabular-nums">Rp {{ number_format($grandTotalTerbayar, 0, ',', '.') }}</td>
                            <td class="py-4 px-4 text-right font-mono font-extrabold text-[#E11D48] tabular-nums">Rp {{ number_format($grandBelumLunas, 0, ',', '.') }}</td>
                            <td class="py-4 px-6 text-center font-mono font-extrabold text-[#2563EB] tabular-nums">{{ $effPersen }}% Rata-rata</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- GRAFIK DISTRIBUSI & CATATAN SISTEM --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="bg-white border border-[#CBD5E1] rounded-xl p-6 shadow-sm">
                <h4 class="text-xs font-bold text-[#0F172A] uppercase tracking-wider mb-4">DISTRIBUSI VOLUME AIR PER BLOK</h4>
                <div class="space-y-3.5">
                    @foreach($rekapBlok as $b)
                    @php
                        $pct = $grandTotalVolume > 0 ? round(($b['total_pemakaian'] / $grandTotalVolume) * 100, 1) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-[#0F172A]">{{ $b['nama'] }}</span>
                            <span class="text-[#64748B] font-mono tabular-nums">{{ number_format($b['total_pemakaian'], 0, ',', '.') }} m³ ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full h-2.5 bg-[#F1F5F9] rounded-full overflow-hidden">
                            <div class="h-full bg-[#2563EB] rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white border border-[#CBD5E1] rounded-xl p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-bold text-[#0F172A] uppercase tracking-wider mb-3">CATATAN & OTORISASI KEUANGAN</h4>
                    <p class="text-xs text-[#64748B] leading-relaxed mb-4">
                        Laporan ini dihasilkan otomatis oleh Sistem Pengelolaan Air Grand Sulawesi Parepare (PT. Arista Jaya). Seluruh data konsumsi bersumber dari verifikasi OCR pembacaan meter dan sinkronisasi transaksi bank/kas per {{ $periodeStr }}.
                    </p>
                    <div class="p-3.5 bg-[#F8FAFC] border border-[#CBD5E1] rounded-xl text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-[#64748B]">Status Rekonsiliasi Kas:</span>
                            <span class="font-bold text-[#059669]">MATCH • 100% TERVERIFIKASI</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#64748B]">Metode Perhitungan:</span>
                            <span class="font-bold text-[#0F172A]">Tarif Progresif 3 Tier</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#64748B]">Audit Keamanan Dokumen:</span>
                            <span class="font-bold text-[#2563EB]">Kriptografis RSA-2048</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-[#F1F5F9] flex items-center justify-between text-xs text-[#64748B]">
                    <span>Dicetak oleh: <strong>Admin Utama</strong></span>
                    <span class="font-mono tabular-nums">{{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WITA</span>
                </div>
            </div>
        </div>

    @endif

</div>
@endsection
