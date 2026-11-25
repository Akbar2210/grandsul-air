@extends('layouts.admin')

@section('title', 'Pencatatan Pembayaran — Pengelolaan Air Terintegrasi Grand Sulawesi')
@section('page-title', 'Pencatatan Pembayaran')
@section('page-subtitle', 'Pencatatan transaksi pembayaran tunai atau transfer warga secara akurat')

@section('content')
<style>
@font-face {
    font-family: 'OCR A Std';
    src: local('OCR A Std'), local('OCR A Extended'), local('OCRAStd'), url('{{ asset("fonts/ocra.ttf") }}') format('truetype');
    font-weight: normal;
    font-style: normal;
}
.font-ocr, .font-ocr * {
    font-family: 'OCR A Std', 'OCR A Extended', 'OCR-A', monospace !important;
}
</style>
<div class="space-y-5" x-data="{
    selectedTagihanId: '{{ $selectedTagihan?->id ?? '' }}',
    currentUnit: '{{ $selectedTagihan?->unit->nomor_rumah ?? '—' }}',
    currentWarga: '{{ addslashes($selectedTagihan?->unit->nama_pemilik ?? 'Pilih salah satu tagihan') }}',
    currentBlok: '{{ addslashes($selectedTagihan?->unit->blok->nama_blok ?? '—') }}',
    currentKode: '{{ $selectedTagihan ? ('TGH-2611-' . str_pad($selectedTagihan->id, 4, '0', STR_PAD_LEFT)) : '—' }}',
    currentTotal: {{ (float) ($selectedTagihan?->total_tagihan ?? 0) }},
    currentVol: {{ (float) ($selectedTagihan?->volume_pemakaian ?? 0) }},
    jumlahBayar: {{ (float) ($selectedTagihan?->total_tagihan ?? 0) }},
    metode: 'Tunai',
    statusResult: 'lunas',
    searchKeyword: '',

    pickBill(b) {
        this.selectedTagihanId = String(b.id);
        this.currentUnit = b.unit;
        this.currentWarga = b.warga;
        this.currentBlok = b.blok;
        this.currentTotal = Number(b.total);
        this.currentVol = Number(b.vol);
        this.currentKode = b.kode;
        this.jumlahBayar = Number(b.total);
        this.cekStatus();

        if (window.history && window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.set('tagihan_id', b.id);
            window.history.replaceState({}, '', url.toString());
        }

        const formCard = document.getElementById('formulir-pembayaran-card');
        if (formCard && window.innerWidth < 1024) {
            formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    },

    setExactTotal() {
        this.jumlahBayar = this.currentTotal;
        this.cekStatus();
    },

    cekStatus() {
        if (this.jumlahBayar >= this.currentTotal && this.currentTotal > 0) {
            this.statusResult = 'lunas';
        } else {
            this.statusResult = 'sebagian';
        }
    },

    formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    },

    matchesSearch(unit, warga) {
        if (!this.searchKeyword) return true;
        const q = this.searchKeyword.toLowerCase().trim();
        return unit.includes(q) || warga.includes(q);
    }
}">

    {{-- ==================== 3 KARTU STATISTIK CEPAT (BENTUK ASLI DIPERTAHANKAN) ==================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-[#CBD5E1] p-4 sm:p-5 shadow-sm">
            <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">TAGIHAN TERTUNDA</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight font-mono tabular-nums">
                {{ $unpaidBills->count() }} <span class="text-xs font-sans font-semibold text-[#0F172A]">Tagihan</span>
            </div>
            <span class="text-[11px] text-[#0F172A] font-medium mt-1 block">Menunggu Konfirmasi Pelunasan</span>
        </div>

        <div class="bg-white rounded-xl border border-[#CBD5E1] p-4 sm:p-5 shadow-sm">
            <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">TOTAL NOMINAL PIUTANG</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight font-mono tabular-nums">
                Rp {{ number_format($unpaidBills->sum('total_tagihan'), 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-[#0F172A] font-medium mt-1 block">Periode {{ $periodeStr }}</span>
        </div>

        <div class="bg-white rounded-xl border border-[#CBD5E1] p-4 sm:p-5 shadow-sm">
            <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">METODE TERVERIFIKASI</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight">
                Tunai / Transfer
            </div>
        </div>
    </div>

    {{-- ==================== 2-KOLOM UTAMA (50% / 50% BULLETPROOF GRID) ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

        {{-- ==================== PANEL KIRI: FORMULIR PEMBAYARAN ==================== --}}
        <div id="formulir-pembayaran-card" class="bg-white rounded-xl border border-[#CBD5E1] shadow-sm overflow-hidden scroll-mt-6">
            <div class="px-6 py-2.5 border-b border-[#F1F5F9] bg-[#F8FAFC]/50">
                <h2 class="text-sm font-bold text-[#0F172A]">Formulir Catat Pembayaran</h2>
                <p class="text-xs text-[#64748B]">Isi transaksi pelunasan tagihan air warga</p>
            </div>

            <form action="{{ route('admin.pembayaran.store') }}" method="POST" class="px-6 pt-2 pb-5 space-y-3.5 text-xs">
                @csrf

                <input type="hidden" name="tagihan_id" :value="selectedTagihanId">

                {{-- KARTU TIKET / VOUCHER IDENTITAS & TAGIHAN (100% PERSIS GAMBAR DARI USER - SINGLE SEAMLESS VECTOR PATH) --}}
                <div class="relative w-full h-[160px] select-none font-ocr">
                    
                    {{-- SINGLE SEAMLESS VECTOR TICKET SHAPE --}}
                    <svg class="absolute inset-0 w-full h-full pointer-events-none z-0" viewBox="0 0 460 160" preserveAspectRatio="none">
                        {{-- Background Fill & Garis Border Halus Pudar --}}
                        <path d="
                            M 18, 1.5
                            L 442, 1.5
                            A 16,16 0 0 1 458.5, 18
                            L 458.5, 26
                            A 6,6 0 0 0 458.5, 38
                            L 458.5, 48
                            A 6,6 0 0 0 458.5, 60
                            L 458.5, 68
                            A 12,12 0 0 0 458.5, 92
                            L 458.5, 100
                            A 6,6 0 0 0 458.5, 112
                            L 458.5, 122
                            A 6,6 0 0 0 458.5, 134
                            L 458.5, 142
                            A 16,16 0 0 1 442, 158.5
                            L 18, 158.5
                            A 16,16 0 0 1 1.5, 142
                            L 1.5, 134
                            A 6,6 0 0 0 1.5, 122
                            L 1.5, 112
                            A 6,6 0 0 0 1.5, 100
                            L 1.5, 92
                            A 12,12 0 0 0 1.5, 68
                            L 1.5, 60
                            A 6,6 0 0 0 1.5, 48
                            L 1.5, 38
                            A 6,6 0 0 0 1.5, 26
                            L 1.5, 18
                            A 16,16 0 0 1 18, 1.5
                            Z
                        " fill="#FFFFFF" stroke="#64748B" stroke-width="1.25" vector-effect="non-scaling-stroke" />

                        {{-- Watermark Garis Diagonal Kiri Atas --}}
                        <g opacity="0.35" stroke="#3B82F6" stroke-linecap="round" vector-effect="non-scaling-stroke">
                            <line x1="6" y1="26" x2="32" y2="2" stroke-width="4.5" />
                            <line x1="6" y1="46" x2="52" y2="2" stroke-width="6.5" />
                            <line x1="6" y1="66" x2="72" y2="2" stroke-width="4.5" />
                        </g>

                        {{-- Garis Putus-Putus Tengah (Persis di Antara Kedua Coakan Tengah) --}}
                        <line x1="14" y1="80" x2="446" y2="80" 
                              stroke="#64748B" stroke-width="1.2" stroke-dasharray="6,4" stroke-opacity="0.5" vector-effect="non-scaling-stroke" />
                    </svg>

                    {{-- BAGIAN ATAS: UNIT & IDENTITAS + PILL HITAM --}}
                    <div class="relative z-10 px-8 pt-3 font-mono">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[11px] font-bold text-[#0F172A] tracking-wider uppercase shrink-0">UNIT & IDENTITAS</span>
                            <div class="h-[1px] bg-[#64748B]/30 flex-1"></div>
                            <span class="bg-[#0B1E3D] text-white font-mono font-bold text-xs px-3 py-0.5 rounded-lg tracking-wider shrink-0" x-text="currentUnit"></span>
                        </div>

                        <div class="mt-2 space-y-0.5 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-[#0F172A] tracking-wide">Nama Pemilik &nbsp;:</span>
                                <span class="font-bold text-[#0F172A]" x-text="currentWarga"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[#0F172A] tracking-wide">Volume Pemakaian :</span>
                                <span class="font-bold text-[#0F172A]" x-text="currentVol + ' m³'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- BAGIAN BAWAH: KODE TAGIHAN & TOTAL KEWAJIBAN --}}
                    <div class="absolute bottom-2.5 left-8 right-8 z-10 flex justify-between items-end font-mono">
                        <div>
                            <span class="text-[10px] font-bold text-[#0F172A] uppercase tracking-wider block">KODE TAGIHAN</span>
                            <span class="text-sm font-extrabold text-[#0F172A] tracking-wider block mt-0.5" x-text="currentKode"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-[#0F172A] uppercase tracking-wider block">TOTAL KEWAJIBAN</span>
                            <span class="text-lg font-black text-[#2563EB] tracking-tight block mt-0.5" x-text="formatRupiah(currentTotal)"></span>
                        </div>
                    </div>

                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="font-bold text-[#0F172A] block mb-1">Tanggal Bayar <span class="text-[#DC2626]">*</span></label>
                        <input type="date" name="tgl_bayar" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white tabular-nums">
                    </div>
                    <div>
                        <label class="font-bold text-[#0F172A] block mb-1">Nominal Bayar (Rp) <span class="text-[#DC2626]">*</span></label>
                        <input type="number" name="jumlah_bayar" x-model.number="jumlahBayar" @input="cekStatus()" placeholder="0" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] font-mono tabular-nums">
                    </div>
                </div>

                <div>
                    <label class="font-bold text-[#0F172A] block mb-1.5">Metode Pembayaran <span class="text-[#DC2626]">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="metode = 'Tunai'"
                                :class="metode === 'Tunai' ? 'bg-[#0F172A] text-white font-normal border-[#0F172A] shadow-xs' : 'bg-[#F8FAFC] text-[#64748B] hover:bg-[#F1F5F9] border-[#CBD5E1] font-normal'"
                                class="py-2.5 px-3 rounded-xl border text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 13.5v-3a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 10.5v3z"/></svg>
                            <span>Tunai (Kasir)</span>
                        </button>
                        <button type="button" @click="metode = 'Transfer'"
                                :class="metode === 'Transfer' ? 'bg-[#0F172A] text-white font-normal border-[#0F172A] shadow-xs' : 'bg-[#F8FAFC] text-[#64748B] hover:bg-[#F1F5F9] border-[#CBD5E1] font-normal'"
                                class="py-2.5 px-3 rounded-xl border text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                            <span>Transfer Bank</span>
                        </button>
                    </div>
                    <input type="hidden" name="metode_bayar" :value="metode">
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-cyan-cta w-full py-3.5 text-xs font-normal flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                        <span>Simpan</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ==================== PANEL KANAN: DAFTAR TAGIHAN BELUM LUNAS ==================== --}}
        <div class="bg-white rounded-xl border border-[#CBD5E1] shadow-sm overflow-hidden">
            <div class="p-5 py-2.5 border-b border-[#F1F5F9] flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#F8FAFC]/50">
                <div>
                    <h3 class="text-sm font-bold text-[#0F172A]">Daftar Tagihan Tertunda</h3>
                    <p class="text-xs text-[#64748B]">Klik tombol "Pilih" untuk mengisi data otomatis ke form</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#F8FAFC] text-[#0F172A] text-xs font-mono font-bold border border-[#CBD5E1] shrink-0 self-start sm:self-auto">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#D97706]"></span>
                    <span>{{ $unpaidBills->count() }} Menunggu</span>
                </span>
            </div>

            <div class="p-4 border-b border-[#F1F5F9] bg-white">
                <div class="relative">
                    <svg class="w-4 h-4 text-[#94A3B8] absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" x-model="searchKeyword" placeholder="Cari unit (misal: A-05) atau nama warga..."
                           class="w-full pl-10 pr-3.5 py-2 rounded-xl border border-[#CBD5E1] text-xs font-medium text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>
            </div>

            <div class="overflow-x-auto max-h-[520px] overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead class="sticky top-0 bg-[#F8FAFC] z-10 border-b border-[#CBD5E1] text-[#0F172A] font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4 whitespace-nowrap">Unit</th>
                            <th class="py-3 px-3">Nama Warga</th>
                            <th class="py-3 px-3 text-right">Volume</th>
                            <th class="py-3 px-3 text-right">Total Tagihan</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @forelse($unpaidBills as $b)
                        <tr class="transition-colors cursor-pointer select-none"
                            :class="selectedTagihanId == '{{ $b->id }}' ? 'bg-[#F8FAFC]' : 'hover:bg-[#F8FAFC]'"
                            x-show="matchesSearch('{{ strtolower($b->unit->nomor_rumah) }}', '{{ strtolower(addslashes($b->unit->nama_pemilik)) }}')"
                            @click="pickBill({
                                id: '{{ $b->id }}',
                                unit: '{{ $b->unit->nomor_rumah }}',
                                warga: '{{ addslashes($b->unit->nama_pemilik) }}',
                                blok: '{{ addslashes($b->unit->blok->nama_blok ?? 'Blok') }}',
                                total: {{ (float) $b->total_tagihan }},
                                vol: {{ (float) $b->volume_pemakaian }},
                                kode: 'TGH-2611-{{ str_pad($b->id, 4, '0', STR_PAD_LEFT) }}'
                            })">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-xs text-[#0F172A] tabular-nums">
                                    {{ $b->unit->nomor_rumah }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="font-bold text-[#0F172A] block">{{ $b->unit->nama_pemilik }}</span>
                                <span class="text-[10px] text-[#64748B]">{{ $b->unit->blok->nama_blok ?? 'Blok' }}</span>
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono font-semibold text-[#64748B] whitespace-nowrap tabular-nums">
                                {{ $b->volume_pemakaian }} m³
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono font-extrabold text-[#0F172A] whitespace-nowrap tabular-nums">
                                Rp {{ number_format($b->total_tagihan, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.pembayaran', ['tagihan_id' => $b->id]) }}"
                                   @click.stop.prevent="pickBill({
                                       id: '{{ $b->id }}',
                                       unit: '{{ $b->unit->nomor_rumah }}',
                                       warga: '{{ addslashes($b->unit->nama_pemilik) }}',
                                       blok: '{{ addslashes($b->unit->blok->nama_blok ?? 'Blok') }}',
                                       total: {{ (float) $b->total_tagihan }},
                                       vol: {{ (float) $b->volume_pemakaian }},
                                       kode: 'TGH-2611-{{ str_pad($b->id, 4, '0', STR_PAD_LEFT) }}'
                                   })"
                                   :class="selectedTagihanId == '{{ $b->id }}' ? 'bg-white border-2 border-[#0F172A] text-[#0F172A] font-normal shadow-xs' : 'bg-[#2563EB] hover:bg-[#1D4ED8] text-white border-2 border-transparent'"
                                   class="inline-flex items-center justify-center px-3 py-1 rounded-lg font-normal text-[11px] transition-all cursor-pointer whitespace-nowrap">
                                    Pilih
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-[#64748B]">
                                <svg class="w-10 h-10 mx-auto text-[#16A34A] mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="font-bold text-[#0F172A] text-xs">Seluruh Tagihan Warga Telah Lunas</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
