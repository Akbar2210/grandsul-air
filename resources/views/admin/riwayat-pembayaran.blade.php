@extends('layouts.admin')

@section('title', 'Riwayat Pembayaran — Grand Sulawesi Air')
@section('page-title', 'Riwayat Pembayaran')
@section('page-subtitle', 'Daftar transaksi pembayaran dan cetak nota ber-QR tanda tangan digital')

@section('content')
<div class="space-y-6">

    {{-- ==================== FILTER BAR MULTI-PARAMETER ==================== --}}
    <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('admin.riwayat-pembayaran') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-end">
            {{-- Filter Periode --}}
            <div class="sm:col-span-3">
                <label class="text-xs font-bold text-[#64748B] block mb-1">Periode</label>
                <div class="px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] text-xs font-bold text-[#0F172A]">
                    {{ $periodeStr }}
                </div>
            </div>

            {{-- Filter Blok --}}
            <div class="sm:col-span-3">
                <label class="text-xs font-bold text-[#64748B] block mb-1">Blok</label>
                <select name="blok" class="w-full px-3 py-2 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    <option value="">Semua Blok (A–F)</option>
                    @foreach($bloks as $b)
                    <option value="{{ $b->id }}" {{ request('blok') == $b->id ? 'selected' : '' }}>{{ $b->nama_blok }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Metode --}}
            <div class="sm:col-span-2">
                <label class="text-xs font-bold text-[#64748B] block mb-1">Metode</label>
                <select name="metode" class="w-full px-3 py-2 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    <option value="">Semua</option>
                    <option value="Tunai" {{ $selectedMetode == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                    <option value="Transfer" {{ $selectedMetode == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                </select>
            </div>

            {{-- Filter Status --}}
            <div class="sm:col-span-2">
                <label class="text-xs font-bold text-[#64748B] block mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    <option value="">Semua</option>
                    <option value="lunas" {{ $selectedStatus == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="sebagian" {{ $selectedStatus == 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                </select>
            </div>

            {{-- Submit --}}
            <div class="sm:col-span-2">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white font-bold text-xs transition-colors cursor-pointer">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    {{-- ==================== TABEL RIWAYAT TRANSAKSI ==================== --}}
    <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-[#F1F5F9] flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-[#0F172A]">Riwayat Transaksi Pelunasan</h3>
                <p class="text-xs text-[#64748B] mt-0.5">Seluruh pembayaran tercatat permanen di audit trail</p>
            </div>
            <span class="text-xs text-[#64748B] font-mono">
                Total: {{ $pembayarans->total() }} Transaksi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-5">ID Pembayaran</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Unit</th>
                        <th class="py-3.5 px-4">Warga</th>
                        <th class="py-3.5 px-4">Nominal</th>
                        <th class="py-3.5 px-4">Metode</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9]">
                    @forelse($pembayarans as $p)
                    @php $isLunas = ($p->tagihan && $p->tagihan->status_bayar === 'lunas'); @endphp
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="py-3.5 px-5 font-mono font-bold text-[#0F172A]">
                            PAY-2611-{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="py-3.5 px-4 text-[#64748B] tabular-nums">
                            {{ \Carbon\Carbon::parse($p->tgl_bayar)->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-[#0F172A]">
                            <span class="px-2 py-0.5 rounded-md bg-[#0F172A] text-[#00F2FE] text-xs">
                                {{ $p->tagihan->unit->nomor_rumah ?? '—' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-semibold text-[#0F172A] block">{{ $p->tagihan->unit->nama_pemilik ?? 'Warga' }}</span>
                            <span class="text-[10px] text-[#64748B]">{{ $p->tagihan->unit->blok->nama_blok ?? 'Blok' }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-extrabold text-[#0F172A] tabular-nums">
                            Rp {{ number_format($p->jumlah_bayar, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-[#64748B]">
                            <span class="inline-flex items-center gap-1.5">
                                @if($p->metode_bayar === 'Transfer')
                                <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB]"></span>
                                @else
                                <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>
                                @endif
                                {{ $p->metode_bayar }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($isLunas)
                            <span class="px-2.5 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534] font-bold text-[11px] border border-[#86EFAC]">
                                Lunas
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full bg-[#FEF3C7] text-[#92400E] font-bold text-[11px] border border-[#FDE68A]">
                                Sebagian
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5 text-right">
                            @if($isLunas)
                            <a href="{{ route('admin.nota.cetak', $p->id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#F8FAFC] hover:bg-[#EFF6FF] text-[#2563EB] border border-[#CBD5E1] hover:border-[#2563EB] font-bold text-[11px] transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.206-.72-2.31-1.39-3.267M17.28 13.829c.24-1.206.72-2.31 1.39-3.267M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Cetak Nota Kriptografis</span>
                            </a>
                            @else
                            <button type="button" disabled title="Hanya tagihan LUNAS yang berhak mendapatkan nota resmi"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#F1F5F9] text-[#94A3B8] border border-[#E2E8F0] font-semibold text-[11px] cursor-not-allowed">
                                <span>Nota Terkunci</span>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-[#64748B]">
                            <p class="font-semibold text-sm">Belum ada riwayat pembayaran yang tercatat.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Aturan Bisnis Kriptografis (SRS FR-006) --}}
        <div class="px-6 py-3.5 bg-[#F8FAFC] border-t border-[#E2E8F0] flex items-center justify-between text-[11px] text-[#64748B]">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#2563EB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-2.18-8.25H6.75A2.25 2.25 0 004.5 6.75v10.5A2.25 2.25 0 006.75 19.5h10.5a2.25 2.25 0 002.25-2.25V11.25m-6.75-9l4.5 4.5m0 0l-4.5 4.5m4.5-4.5H9"/></svg>
                <span>Nota kriptografis (RSA-2048 + QR Code) hanya dapat dicetak untuk transaksi berstatus <strong>Lunas</strong>.</span>
            </span>
            <span class="font-mono text-[#94A3B8]">RSA-2048 / SHA-256</span>
        </div>

        @if($pembayarans->hasPages())
        <div class="px-6 py-4 border-t border-[#F1F5F9]">
            {{ $pembayarans->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

