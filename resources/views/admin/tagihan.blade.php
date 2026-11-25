@extends('layouts.admin')

@section('title', 'Tagihan Air — Pengelolaan Air Terintegrasi Grand Sulawesi')
@section('page-title', 'Tagihan')
@section('page-subtitle', 'Proses tagihan massal bertarif progresif secara asinkron')

@section('page-actions')
<form action="{{ route('admin.tagihan.batch') }}" method="POST">
    @csrf
    <button type="submit" class="btn-cyan-cta px-4 py-2.5 text-xs font-bold flex items-center gap-2 shadow-sm cursor-pointer">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
        </svg>
        <span>Proses Tagihan (Batch)</span>
    </button>
</form>
@endsection

@section('content')
<div class="space-y-5">

    {{-- ==================== FILTER BAR (PERIODE & BLOK) ==================== --}}
    <div class="bg-white rounded-xl border border-[#CBD5E1] p-4 sm:p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.tagihan') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-end">
            <div class="sm:col-span-5">
                <label class="text-xs font-bold text-[#0F172A] block mb-1">Periode Penagihan</label>
                <select name="periode" onchange="this.form.submit()" class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white shadow-xs">
                    <option value="" {{ ($selectedPeriode ?? '') === '' ? 'selected' : '' }}>Semua Periode</option>
                    @foreach($periodeOptions as $ym => $label)
                        <option value="{{ $ym }}" {{ ($selectedPeriode ?? '') === $ym ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-5">
                <label class="text-xs font-bold text-[#0F172A] block mb-1">Blok Hunian</label>
                <select name="blok" onchange="this.form.submit()" class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white shadow-xs">
                    <option value="">Semua Blok (A–F)</option>
                    @foreach($bloks as $b)
                    <option value="{{ $b->id }}" {{ $selectedBlok == $b->id ? 'selected' : '' }}>{{ $b->nama_blok }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <a href="{{ route('admin.tagihan') }}" class="w-full py-2.5 px-3 rounded-xl bg-white hover:bg-[#F8FAFC] border border-[#CBD5E1] text-center text-xs font-bold text-[#0F172A] block transition-colors shadow-xs">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ==================== PEMROSESAN TAGIHAN MASSAL (BATCH) ==================== --}}
    <div class="bg-white rounded-xl border border-[#CBD5E1] p-5 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
            <div>
                <h3 class="text-sm font-bold text-[#0F172A] flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ ($batchInfo['unbilled_count'] ?? 0) > 0 ? 'bg-[#2563EB] animate-pulse' : 'bg-[#16A34A]' }}"></span>
                    <span>Pemrosesan Tagihan Massal (Batch)</span>
                </h3>
                <p class="text-xs text-[#64748B] mt-0.5">Antrean data meteran valid dari Petugas & hasil tinjauan Admin siap dieksekusi menjadi nota rekening</p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto">
                @if(($batchInfo['unbilled_count'] ?? 0) > 0)
                <span class="text-xs font-bold text-[#2563EB] font-mono px-3 py-1.5 rounded-lg bg-[#EFF6FF] border border-[#BFDBFE] flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#2563EB]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg>
                    <span>{{ $batchInfo['unbilled_count'] }} / {{ $batchInfo['total_target'] }} Data Valid Siap Diproses</span>
                </span>
                @else
                <span class="text-xs font-bold text-[#14532D] font-mono px-3 py-1.5 rounded-lg bg-[#DCFCE7] border border-[#22C55E] flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#16A34A]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <span>Antrean Kosong (0 Unit Siap Diproses)</span>
                </span>
                @endif
                @if(($batchInfo['processed'] ?? 0) > 0)
                <span class="text-xs font-extrabold text-[#0F172A] font-mono tabular-nums px-2.5 py-1.5 rounded-lg bg-[#F8FAFC] border border-[#CBD5E1]">
                    {{ $batchInfo['processed'] }} / {{ $batchInfo['total_target'] }} Tagihan Terbit
                </span>
                @endif
            </div>
        </div>

        {{-- PROGRESS BAR ANTREAN BATCH: BAR INI TERISI DARI DATA YANG SUDAH DIFOTO PETUGAS & VALID SERTA DATA FLAG YANG SUDAH DITINJAU --}}
        <div class="w-full h-3.5 bg-[#E2E8F0] rounded-full overflow-hidden p-0.5">
            <div class="h-full rounded-full transition-all duration-500 shadow-xs" 
                 style="width: {{ $batchInfo['percent'] }}%; background-color: {{ ($batchInfo['unbilled_count'] ?? 0) > 0 ? '#2563EB' : '#CBD5E1' }};">
            </div>
        </div>
    </div>

    {{-- ==================== TABEL DAFTAR TAGIHAN ==================== --}}
    <div class="bg-white rounded-xl border border-[#CBD5E1] shadow-sm overflow-hidden">
        <div class="px-6 py-3.5 border-b border-[#F1F5F9] flex items-center justify-between">
            <h3 class="text-sm font-bold text-[#0F172A]">Daftar Tagihan & Nota Rekening Pelanggan</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-[#CBD5E1] text-[#0F172A] font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-5 whitespace-nowrap">Kode Tagihan</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Unit</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Pemilik / Warga</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Volume (m³)</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Total Tagihan</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Jatuh Tempo</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Status</th>
                        <th class="py-3.5 px-5 text-center whitespace-nowrap">Nota Fisik & Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9]">
                    @forelse($tagihans as $t)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="py-3.5 px-5 font-mono font-bold text-[#0F172A] tabular-nums whitespace-nowrap">
                            {{ $t->kode_tagihan ?? ('TGH-2611-' . str_pad($t->id, 4, '0', STR_PAD_LEFT)) }}
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="font-mono font-bold text-xs text-[#0F172A] tabular-nums">
                                {{ $t->unit->nomor_rumah ?? '—' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="font-semibold text-[#0F172A] block">{{ $t->unit->nama_pemilik ?? 'Warga' }}</span>
                            <span class="text-[10px] text-[#64748B]">{{ $t->unit->blok->nama_blok ?? 'Blok' }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-[#0F172A] tabular-nums whitespace-nowrap">
                            {{ $t->volume_pemakaian }} m³
                        </td>
                        <td class="py-3.5 px-4 font-mono font-extrabold text-[#0F172A] tabular-nums whitespace-nowrap">
                            Rp {{ number_format($t->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[#64748B] tabular-nums whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($t->tgl_jatuh_tempo)->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            @if($t->status_bayar === 'lunas')
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-md bg-[#16A34A] text-white font-bold text-[11px] shadow-xs">
                                <span>Lunas</span>
                            </span>
                            @elseif($t->status_bayar === 'sebagian')
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-md bg-[#EFF6FF] text-[#1D4ED8] font-bold text-[11px] border border-[#93C5FD]">
                                <span>Sebagian</span>
                            </span>
                            @else
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-md bg-[#FEF2F2] text-[#B91C1C] font-bold text-[11px] border border-[#FCA5A5]">
                                <span>Belum Dibayar</span>
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.tagihan.nota', $t->id) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0F172A] hover:bg-[#1E293B] text-white font-normal text-[11px] transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <path d="M6 9V2h12v7"></path>
                                        <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"></path>
                                        <rect x="6" y="14" width="12" height="8"></rect>
                                    </svg>
                                    <span>Cetak Nota</span>
                                </a>
                                @if($t->status_bayar !== 'lunas')
                                <a href="{{ route('admin.pembayaran', ['tagihan_id' => $t->id]) }}"
                                   class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-[#F8FAFC] hover:bg-[#F1F5F9] text-[#2563EB] border border-[#CBD5E1] font-normal text-[11px] transition-colors">
                                    Bayar
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-[#64748B]">
                            <p class="font-semibold text-sm">Belum ada data tagihan untuk filter yang dipilih.</p>
                            <p class="text-xs text-[#94A3B8] mt-1">Tekan tombol "Proses Tagihan (Batch)" di atas untuk memproses tagihan periode berjalan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tagihans->hasPages())
        <div class="px-6 py-4 border-t border-[#F1F5F9] bg-[#F8FAFC]">
            {{ $tagihans->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
