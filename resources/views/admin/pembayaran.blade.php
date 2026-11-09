@extends('layouts.admin')

@section('title', 'Pencatatan Pembayaran — Grand Sulawesi Air')
@section('page-title', 'Pencatatan Pembayaran')
@section('page-subtitle', 'Pencatatan transaksi pembayaran tunai atau transfer warga secara akurat')

@section('content')
<div class="space-y-6" x-data="pembayaranApp()">

    {{-- ==================== 3 KARTU STATISTIK CEPAT ==================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">TAGIHAN TERTUNDA</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight tabular-nums">
                {{ $unpaidBills->count() }} <span class="text-xs font-semibold text-[#64748B]">Tagihan</span>
            </div>
            <span class="text-[11px] text-[#D97706] font-medium mt-1 block">Menunggu Konfirmasi Pelunasan</span>
        </div>

        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">TOTAL NOMINAL PIUTANG</span>
            <div class="mt-1 text-2xl font-black text-[#2563EB] tracking-tight tabular-nums">
                Rp {{ number_format($unpaidBills->sum('total_tagihan'), 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-[#64748B] font-medium mt-1 block">Periode {{ $periodeStr }}</span>
        </div>

        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">METODE TERVERIFIKASI</span>
            <div class="mt-1 text-2xl font-black text-[#059669] tracking-tight tabular-nums">
                Tunai / Transfer
            </div>
            <span class="text-[11px] text-[#059669] font-medium mt-1 block">Nota Kriptografis Otomatis Diterbitkan</span>
        </div>
    </div>

    {{-- ==================== 2-KOLOM UTAMA (50% / 50% BULLETPROOF GRID) ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

        {{-- ==================== PANEL KIRI: FORMULIR PEMBAYARAN ==================== --}}
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            {{-- Header Form --}}
            <div class="px-6 py-5 border-b border-[#F1F5F9] flex items-center justify-between bg-[#F8FAFC]/50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center font-bold shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 13.5v-3a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 10.5v3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-[#0F172A]">Formulir Catat Pembayaran</h2>
                        <p class="text-xs text-[#64748B]">Isi transaksi pelunasan tagihan air warga</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-[#F1F5F9] text-[#64748B] text-[10px] font-mono font-bold">
                    FR-008
                </span>
            </div>

            {{-- Form Isi --}}
            <form action="{{ route('admin.pembayaran.store') }}" method="POST" class="p-6 space-y-4 text-xs">
                @csrf

                {{-- Pilih Tagihan Target --}}
                <div>
                    <label class="font-bold text-[#0F172A] block mb-1.5">Pilih Unit / Tagihan Target <span class="text-[#DC2626]">*</span></label>
                    <select name="tagihan_id" x-model="selectedTagihanId" @change="updateFromSelect()"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                        <option value="">-- Pilih Tagihan Warga --</option>
                        @foreach($unpaidBills as $bill)
                        <option value="{{ $bill->id }}"
                                data-unit="{{ $bill->unit->nomor_rumah }}"
                                data-warga="{{ $bill->unit->nama_pemilik }}"
                                data-blok="{{ $bill->unit->blok->nama_blok ?? 'Blok' }}"
                                data-total="{{ $bill->total_tagihan }}"
                                data-vol="{{ $bill->volume_pemakaian }}"
                                data-kode="TGH-2611-{{ str_pad($bill->id, 4, '0', STR_PAD_LEFT) }}">
                            Unit {{ $bill->unit->nomor_rumah }} — {{ $bill->unit->nama_pemilik }} (Rp {{ number_format($bill->total_tagihan, 0, ',', '.') }})
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Ringkasan Tagihan Terpilih Card --}}
                <div class="p-4 rounded-xl bg-[#F8FAFC] border border-[#CBD5E1] space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-[#64748B] uppercase tracking-wider">UNIT & IDENTITAS</span>
                        <span class="px-2.5 py-0.5 rounded-md bg-[#0F172A] text-[#00F2FE] font-mono font-bold text-xs" x-text="currentUnit"></span>
                    </div>

                    <div class="flex justify-between items-center text-xs">
                        <span class="text-[#64748B]">Nama Pemilik:</span>
                        <span class="font-bold text-[#0F172A]" x-text="currentWarga"></span>
                    </div>

                    <div class="flex justify-between items-center text-xs">
                        <span class="text-[#64748B]">Volume Pemakaian:</span>
                        <span class="font-bold text-[#0F172A] font-mono" x-text="currentVol + ' m³'"></span>
                    </div>

                    <div class="pt-2 border-t border-[#E2E8F0] flex justify-between items-center">
                        <div>
                            <span class="text-[10px] font-bold text-[#64748B] uppercase block">Kode Tagihan</span>
                            <span class="font-mono text-xs font-bold text-[#0F172A]" x-text="currentKode"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-[#64748B] uppercase block">Total Kewajiban</span>
                            <span class="text-base font-black text-[#2563EB] font-mono tabular-nums" x-text="formatRupiah(currentTotal)"></span>
                        </div>
                    </div>
                </div>

                {{-- Tanggal Bayar & Nominal Input --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="font-bold text-[#0F172A] block mb-1">Tanggal Bayar <span class="text-[#DC2626]">*</span></label>
                        <input type="date" name="tgl_bayar" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-bold text-[#0F172A]">Nominal Bayar (Rp) <span class="text-[#DC2626]">*</span></label>
                            <button type="button" @click="setExactTotal()" class="text-[10px] font-bold text-[#2563EB] hover:underline cursor-pointer">
                                Bayar Pas
                            </button>
                        </div>
                        <input type="number" name="jumlah_bayar" x-model.number="jumlahBayar" @input="cekStatus()" placeholder="0" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] font-mono tabular-nums">
                    </div>
                </div>

                {{-- Metode Pembayaran (Tunai / Transfer) --}}
                <div>
                    <label class="font-bold text-[#0F172A] block mb-1.5">Metode Pembayaran <span class="text-[#DC2626]">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="metode = 'Tunai'"
                                :class="metode === 'Tunai' ? 'bg-[#0F172A] text-white font-bold border-[#0F172A] shadow-xs' : 'bg-[#F8FAFC] text-[#64748B] hover:bg-[#F1F5F9] border-[#CBD5E1]'"
                                class="py-2.5 px-3 rounded-xl border text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 13.5v-3a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 10.5v3z"/></svg>
                            <span>Tunai (Kasir)</span>
                        </button>
                        <button type="button" @click="metode = 'Transfer'"
                                :class="metode === 'Transfer' ? 'bg-[#0F172A] text-white font-bold border-[#0F172A] shadow-xs' : 'bg-[#F8FAFC] text-[#64748B] hover:bg-[#F1F5F9] border-[#CBD5E1]'"
                                class="py-2.5 px-3 rounded-xl border text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                            <span>Transfer Bank</span>
                        </button>
                    </div>
                    <input type="hidden" name="metode_bayar" :value="metode">
                </div>

                {{-- Status Indikator Otomatis --}}
                <div class="p-3.5 rounded-xl border flex items-center justify-between"
                     :class="statusResult === 'lunas' ? 'bg-[#ECFDF5] text-[#065F46] border-[#A7F3D0]' : 'bg-[#FEF3C7] text-[#92400E] border-[#FDE68A]'">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full" :class="statusResult === 'lunas' ? 'bg-[#10B981]' : 'bg-[#F59E0B]'"></span>
                        <span class="font-bold">Status Tagihan Hasil Transaksi:</span>
                    </div>
                    <span class="font-extrabold font-mono uppercase tracking-wider"
                          x-text="statusResult === 'lunas' ? 'LUNAS • SIAP TERBIT NOTA' : 'DIBAYAR SEBAGIAN'"></span>
                </div>

                {{-- Tombol Submit --}}
                <div class="pt-2">
                    <button type="submit" class="btn-cyan-cta w-full py-3.5 text-xs font-extrabold flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                        <svg class="w-4 h-4 text-[#0B132B]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span>Simpan & Terbitkan Pembayaran</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ==================== PANEL KANAN: DAFTAR TAGIHAN BELUM LUNAS ==================== --}}
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            {{-- Header & Search --}}
            <div class="p-5 border-b border-[#F1F5F9] flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#F8FAFC]/50">
                <div>
                    <h3 class="text-sm font-bold text-[#0F172A]">Daftar Tagihan Tertunda</h3>
                    <p class="text-xs text-[#64748B]">Klik tombol "Pilih" untuk mengisi data otomatis ke form</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-[#FEF3C7] text-[#B45309] text-xs font-bold border border-[#FDE68A] shrink-0 self-start sm:self-auto">
                    {{ $unpaidBills->count() }} Menunggu
                </span>
            </div>

            {{-- Filter Cepat Pencarian Unit / Warga --}}
            <div class="p-4 border-b border-[#F1F5F9] bg-white">
                <div class="relative">
                    <svg class="w-4 h-4 text-[#94A3B8] absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" x-model="searchKeyword" placeholder="Cari unit (misal: A-05) atau nama warga..."
                           class="w-full pl-10 pr-3.5 py-2 rounded-xl border border-[#CBD5E1] text-xs font-medium text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>
            </div>

            {{-- Tabel Tagihan --}}
            <div class="overflow-x-auto max-h-[520px] overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead class="sticky top-0 bg-[#F8FAFC] z-10 border-b border-[#E2E8F0] text-[#64748B] font-bold uppercase tracking-wider text-[11px]">
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
                        <tr class="hover:bg-[#F8FAFC] transition-colors"
                            x-show="matchesSearch('{{ strtolower($b->unit->nomor_rumah) }}', '{{ strtolower($b->unit->nama_pemilik) }}')">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-md bg-[#0F172A] text-[#00F2FE] font-mono font-bold text-xs">
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
                            <td class="py-3.5 px-3 text-right font-extrabold text-[#0F172A] whitespace-nowrap tabular-nums">
                                Rp {{ number_format($b->total_tagihan, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <button type="button"
                                        @click="pickBill('{{ $b->id }}', '{{ $b->unit->nomor_rumah }}', '{{ $b->unit->nama_pemilik }}', '{{ $b->unit->blok->nama_blok ?? "Blok" }}', {{ $b->total_tagihan }}, {{ $b->volume_pemakaian }}, 'TGH-2611-{{ str_pad($b->id, 4, '0', STR_PAD_LEFT) }}')"
                                        class="px-3 py-1.5 rounded-lg bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-[11px] transition-all cursor-pointer">
                                    Pilih
                                </button>
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

@push('scripts')
<script>
function pembayaranApp() {
    return {
        selectedTagihanId: '{{ $selectedTagihan?->id ?? "" }}',
        currentUnit: '{{ $selectedTagihan?->unit->nomor_rumah ?? "—" }}',
        currentWarga: '{{ $selectedTagihan?->unit->nama_pemilik ?? "Pilih salah satu tagihan" }}',
        currentBlok: '{{ $selectedTagihan?->unit->blok->nama_blok ?? "—" }}',
        currentKode: '{{ $selectedTagihan ? ("TGH-2611-" . str_pad($selectedTagihan->id, 4, "0", STR_PAD_LEFT)) : "—" }}',
        currentTotal: {{ $selectedTagihan?->total_tagihan ?? 0 }},
        currentVol: {{ $selectedTagihan?->volume_pemakaian ?? 0 }},
        jumlahBayar: {{ $selectedTagihan?->total_tagihan ?? 0 }},
        metode: 'Tunai',
        statusResult: 'lunas',
        searchKeyword: '',

        pickBill(id, unit, warga, blok, total, vol, kode) {
            this.selectedTagihanId = id;
            this.currentUnit = unit;
            this.currentWarga = warga;
            this.currentBlok = blok;
            this.currentTotal = total;
            this.currentVol = vol;
            this.currentKode = kode;
            this.jumlahBayar = total;
            this.cekStatus();
        },

        updateFromSelect() {
            const selectEl = document.querySelector('select[name="tagihan_id"]');
            const opt = selectEl.options[selectEl.selectedIndex];
            if (opt && opt.value) {
                this.currentUnit = opt.dataset.unit || '—';
                this.currentWarga = opt.dataset.warga || '—';
                this.currentBlok = opt.dataset.blok || '—';
                this.currentTotal = parseFloat(opt.dataset.total) || 0;
                this.currentVol = parseFloat(opt.dataset.vol) || 0;
                this.currentKode = opt.dataset.kode || '—';
                this.jumlahBayar = this.currentTotal;
            } else {
                this.currentUnit = '—';
                this.currentWarga = 'Pilih salah satu tagihan';
                this.currentTotal = 0;
                this.currentVol = 0;
                this.currentKode = '—';
                this.jumlahBayar = 0;
            }
            this.cekStatus();
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
    };
}
</script>
@endpush
@endsection
