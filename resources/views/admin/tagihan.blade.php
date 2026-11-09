@extends('layouts.admin')

@section('title', 'Tagihan Air — Grand Sulawesi Air')
@section('page-title', 'Tagihan')
@section('page-subtitle', 'Proses tagihan massal bertarif progresif secara asinkron')

@section('page-actions')
<form action="{{ route('admin.tagihan.batch') }}" method="POST">
    @csrf
    <button type="submit" class="btn-cyan-cta px-4 py-2.5 text-xs font-bold flex items-center gap-2 shadow-sm cursor-pointer">
        <svg class="w-4 h-4 text-[#0B132B]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
        </svg>
        <span>Proses Tagihan (Batch)</span>
    </button>
</form>
@endsection

@section('content')
<div class="space-y-6">

    {{-- ==================== FILTER BAR (PERIODE & BLOK) ==================== --}}
    <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('admin.tagihan') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-end">
            {{-- Filter Periode --}}
            <div class="sm:col-span-5">
                <label class="text-xs font-bold text-[#64748B] block mb-1">Periode Penagihan</label>
                <div class="px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] text-xs font-bold text-[#0F172A]">
                    {{ $periodeStr }}
                </div>
            </div>

            {{-- Filter Blok --}}
            <div class="sm:col-span-5">
                <label class="text-xs font-bold text-[#64748B] block mb-1">Blok Hunian</label>
                <select name="blok" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    <option value="">Semua Blok (A–F)</option>
                    @foreach($bloks as $b)
                    <option value="{{ $b->id }}" {{ $selectedBlok == $b->id ? 'selected' : '' }}>{{ $b->nama_blok }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Reset Filter --}}
            <div class="sm:col-span-2">
                <a href="{{ route('admin.tagihan') }}" class="w-full py-2.5 px-3 rounded-xl bg-[#F1F5F9] hover:bg-[#E2E8F0] text-center text-xs font-bold text-[#64748B] block transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ==================== STATUS BATCH PROCESSING (LARAVEL QUEUE MONITOR) ==================== --}}
    <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
            <div>
                <h3 class="text-sm font-bold text-[#0F172A] flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#16A34A] animate-pulse"></span>
                    <span>Status Batch Processing (Laravel Queue / Worker)</span>
                </h3>
                <p class="text-xs text-[#64748B] mt-0.5">Perhitungan tarif progresif bertingkat berjalan di antrean latar belakang tanpa risiko timeout server</p>
            </div>
            <span class="text-xs font-extrabold text-[#2563EB] tabular-nums self-start sm:self-auto px-2.5 py-1 rounded-md bg-[#EFF6FF] border border-[#BFDBFE]">
                {{ $batchInfo['processed'] }} / {{ $batchInfo['total_target'] }} Tagihan
            </span>
        </div>

        {{-- Progress Bar --}}
        <div class="w-full h-3 bg-[#EEF2F6] rounded-full overflow-hidden mb-4">
            <div class="h-full bg-linear-to-r from-[#2563EB] to-[#00F2FE] rounded-full transition-all duration-500" style="width: {{ $batchInfo['percent'] }}%;"></div>
        </div>

        {{-- 4 Indikator Worker --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-1">
            <div class="p-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                <span class="text-[10px] text-[#64748B] uppercase font-bold block">Status Worker</span>
                <span class="font-bold text-[#16A34A] block mt-0.5">{{ $batchInfo['status'] }}</span>
            </div>
            <div class="p-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                <span class="text-[10px] text-[#64748B] uppercase font-bold block">Waktu Proses</span>
                <span class="font-bold text-[#0F172A] block mt-0.5 tabular-nums">{{ $batchInfo['duration'] }}</span>
            </div>
            <div class="p-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                <span class="text-[10px] text-[#64748B] uppercase font-bold block">Berhasil / Gagal</span>
                <span class="font-bold text-[#0F172A] block mt-0.5 tabular-nums">{{ $batchInfo['success_count'] }} sukses, {{ $batchInfo['failed_count'] }} gagal</span>
            </div>
            <div class="p-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                <span class="text-[10px] text-[#64748B] uppercase font-bold block">WhatsApp Reminder</span>
                <span class="font-bold text-[#2563EB] block mt-0.5 tabular-nums">Terkirim otomatis</span>
            </div>
        </div>
    </div>

    {{-- ==================== TABEL DAFTAR TAGIHAN ==================== --}}
    <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-[#F1F5F9] flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-[#0F172A]">Daftar Tagihan Pelanggan</h3>
                <p class="text-xs text-[#64748B] mt-0.5">Menampilkan total {{ $tagihans->total() }} invoice tagihan terdaftar</p>
            </div>
            <span class="text-xs text-[#64748B] font-medium">
                Tarif Progresif Bertingkat
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-5">Kode Tagihan</th>
                        <th class="py-3.5 px-4">Unit</th>
                        <th class="py-3.5 px-4">Pemilik / Warga</th>
                        <th class="py-3.5 px-4">Volume (m³)</th>
                        <th class="py-3.5 px-4">Total Tagihan</th>
                        <th class="py-3.5 px-4">Jatuh Tempo</th>
                        <th class="py-3.5 px-5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9]">
                    @forelse($tagihans as $t)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="py-3.5 px-5 font-mono font-bold text-[#0F172A]">
                            {{ $t->kode_tagihan ?? ('TGH-2611-' . str_pad($t->id, 4, '0', STR_PAD_LEFT)) }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded-md bg-[#0F172A] text-[#00F2FE] font-bold text-xs">
                                {{ $t->unit->nomor_rumah ?? '—' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-semibold text-[#0F172A] block">{{ $t->unit->nama_pemilik ?? 'Warga' }}</span>
                            <span class="text-[10px] text-[#64748B]">{{ $t->unit->blok->nama_blok ?? 'Blok' }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-[#0F172A] tabular-nums">
                            {{ $t->volume_pemakaian }} m³
                        </td>
                        <td class="py-3.5 px-4 font-extrabold text-[#0F172A] tabular-nums">
                            Rp {{ number_format($t->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-[#64748B] tabular-nums">
                            {{ \Carbon\Carbon::parse($t->tgl_jatuh_tempo)->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-3.5 px-5 text-center">
                            @if($t->status_bayar === 'lunas')
                            <span class="px-3 py-1 rounded-full bg-[#DCFCE7] text-[#166534] font-bold text-[11px] border border-[#86EFAC]">
                                Lunas
                            </span>
                            @elseif($t->status_bayar === 'sebagian')
                            <span class="px-3 py-1 rounded-full bg-[#EFF6FF] text-[#1E40AF] font-bold text-[11px] border border-[#BFDBFE]">
                                Sebagian
                            </span>
                            @else
                            <span class="px-3 py-1 rounded-full bg-[#FEE2E2] text-[#DC2626] font-bold text-[11px] border border-[#FECACA]">
                                Belum Dibayar
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-[#64748B]">
                            <p class="font-semibold text-sm">Belum ada data tagihan untuk filter yang dipilih.</p>
                            <p class="text-xs text-[#94A3B8] mt-1">Tekan tombol "Proses Tagihan (Batch)" di atas untuk memproses tagihan periode berjalan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($tagihans->hasPages())
        <div class="px-6 py-4 border-t border-[#F1F5F9] bg-[#F8FAFC]">
            {{ $tagihans->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

