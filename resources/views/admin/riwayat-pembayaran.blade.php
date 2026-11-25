@extends('layouts.admin')

@section('title', 'Riwayat Pembayaran — Pengelolaan Air Terintegrasi Grand Sulawesi')
@section('page-title', 'Riwayat Pembayaran')
@section('page-subtitle', 'Daftar riwayat transaksi pelunasan pembayaran air warga')

@section('content')
<div class="space-y-5">

    {{-- ==================== FILTER BAR MULTI-PARAMETER ==================== --}}
    <div class="bg-white rounded-xl border border-[#CBD5E1] p-4 sm:p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.riwayat-pembayaran') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-end">
            <div class="sm:col-span-3">
                <label class="text-xs font-bold text-[#0F172A] block mb-1">Periode</label>
                <select name="periode" class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white shadow-xs">
                    <option value="" {{ ($selectedPeriode ?? '') === '' ? 'selected' : '' }}>Semua Periode</option>
                    @foreach($periodeOptions as $ym => $label)
                        <option value="{{ $ym }}" {{ ($selectedPeriode ?? '') === $ym ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <label class="text-xs font-bold text-[#0F172A] block mb-1">Blok</label>
                <select name="blok" class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white shadow-xs">
                    <option value="">Semua Blok (A–F)</option>
                    @foreach($bloks as $b)
                    <option value="{{ $b->id }}" {{ (string)($selectedBlok ?? request('blok')) === (string)$b->id ? 'selected' : '' }}>{{ $b->nama_blok }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="text-xs font-bold text-[#0F172A] block mb-1">Metode</label>
                <select name="metode" class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white shadow-xs">
                    <option value="">Semua</option>
                    <option value="Tunai" {{ $selectedMetode == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                    <option value="Transfer" {{ $selectedMetode == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="text-xs font-bold text-[#0F172A] block mb-1">Status</label>
                <select name="status" class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white shadow-xs">
                    <option value="">Semua</option>
                    <option value="lunas" {{ $selectedStatus == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="sebagian" {{ $selectedStatus == 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white font-bold text-xs transition-colors cursor-pointer text-center">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    {{-- ==================== TABEL RIWAYAT TRANSAKSI ==================== --}}
    <div class="bg-white rounded-xl border border-[#CBD5E1] shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-[#F1F5F9] flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-[#0F172A]">Riwayat Transaksi Pelunasan</h3>
                <p class="text-xs text-[#64748B] mt-0.5">Seluruh pembayaran tercatat permanen di audit trail</p>
            </div>
            <span class="text-xs text-[#64748B] font-mono tabular-nums">
                Total: {{ $pembayarans->total() }} Transaksi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs table-fixed min-w-[760px]">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-[#CBD5E1] text-[#0F172A] font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-5 w-[16%] whitespace-nowrap">ID Pembayaran</th>
                        <th class="py-3 px-4 w-[14%] whitespace-nowrap">Tanggal</th>
                        <th class="py-3 px-4 w-[10%] whitespace-nowrap">Unit</th>
                        <th class="py-3 px-4 w-[24%] whitespace-nowrap">Warga</th>
                        <th class="py-3 px-4 w-[14%] whitespace-nowrap">Nominal</th>
                        <th class="py-3 px-4 w-[11%] whitespace-nowrap">Metode</th>
                        <th class="py-3 px-5 w-[11%] text-center whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9]">
                    @forelse($pembayarans as $p)
                    @php $isLunas = ($p->tagihan && $p->tagihan->status_bayar === 'lunas'); @endphp
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="py-3 px-5 font-mono font-bold text-[#0F172A] tabular-nums whitespace-nowrap">
                            PAY-2611-{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="py-3 px-4 font-mono text-[#64748B] tabular-nums whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($p->tgl_bayar)->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-3 px-4 font-bold text-[#0F172A] whitespace-nowrap">
                            <span class="font-mono font-bold text-xs text-[#0F172A] tabular-nums">
                                {{ $p->tagihan->unit->nomor_rumah ?? '—' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="font-semibold text-[#0F172A] block leading-tight">{{ $p->tagihan->unit->nama_pemilik ?? 'Warga' }}</span>
                            <span class="text-[10px] text-[#64748B] block leading-tight mt-0.5">{{ $p->tagihan->unit->blok->nama_blok ?? 'Blok' }}</span>
                        </td>
                        <td class="py-3 px-4 font-mono font-extrabold text-[#0F172A] tabular-nums whitespace-nowrap">
                            Rp {{ number_format($p->jumlah_bayar, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-[#1E293B] whitespace-nowrap">
                            <span class="inline-flex items-center gap-2">
                                @if($p->metode_bayar === 'Transfer')
                                <span class="w-2 h-2 rounded-full bg-[#3B82F6] shadow-[0_0_4px_rgba(59,130,246,0.6)] shrink-0"></span>
                                @else
                                <span class="w-2 h-2 rounded-full bg-[#22C55E] shadow-[0_0_4px_rgba(34,197,94,0.6)] shrink-0"></span>
                                @endif
                                <span class="text-[#0F172A] font-medium">{{ $p->metode_bayar }}</span>
                            </span>
                        </td>
                        <td class="py-3 px-5 text-center whitespace-nowrap">
                            @if($isLunas)
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-md bg-[#16A34A] text-white font-bold text-[11px] shadow-xs">
                                <span>Lunas</span>
                            </span>
                            @else
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-md bg-[#FEF3C7] text-[#92400E] font-bold text-[11px] border border-[#FCD34D]">
                                <span>Sebagian</span>
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-[#64748B]">
                            <p class="font-semibold text-sm">Belum ada riwayat pembayaran yang tercatat.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3.5 bg-[#F8FAFC] border-t border-[#CBD5E1] flex items-center justify-between text-[11px] text-[#64748B]">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#2563EB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-2.18-8.25H6.75A2.25 2.25 0 004.5 6.75v10.5A2.25 2.25 0 006.75 19.5h10.5a2.25 2.25 0 002.25-2.25V11.25m-6.75-9l4.5 4.5m0 0l-4.5 4.5m4.5-4.5H9"/></svg>
                <span>Seluruh riwayat pembayaran tersimpan dan terverifikasi secara permanen dalam sistem audit trail.</span>
            </span>
            <span class="font-mono text-[#64748B]">Audit Trail • Verified</span>
        </div>

        @if($pembayarans->hasPages())
        <div class="px-6 py-4 border-t border-[#F1F5F9]">
            {{ $pembayarans->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
