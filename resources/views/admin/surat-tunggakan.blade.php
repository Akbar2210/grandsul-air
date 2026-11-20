@extends('layouts.admin')

@section('title', 'Surat Penyampaian Tunggakan — Grand Sulawesi Air')
@section('page-title', 'Surat Penyampaian Otomatis')
@section('page-subtitle', 'Penerbitan surat resmi PT. Arista Jaya otomatis bagi unit menunggak ≥ 4 bulan')

@section('content')
<div class="space-y-6">

    {{-- Ringkasan Surat Otomatis --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-[#CBD5E1] p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">UNIT MENUNGGAK ≥ 4 BULAN</span>
            <div class="mt-1.5 text-2xl font-black text-[#0F172A] font-mono tabular-nums">
                {{ $daftarSurat->count() }} <span class="text-xs font-sans font-semibold text-[#64748B]">Unit Hunian</span>
            </div>
            <span class="text-[11px] text-[#DC2626] font-semibold mt-1 block">Surat Penyampaian Siap Cetak</span>
        </div>

        <div class="bg-white border border-[#CBD5E1] p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">TOTAL TUNGGAKAN + DENDA</span>
            <div class="mt-1.5 text-2xl font-black text-[#0F172A] font-mono tabular-nums">
                Rp {{ number_format($daftarSurat->sum('grand_total'), 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-[#64748B] font-medium mt-1 block">Termasuk denda @Rp.10.000,-/bln</span>
        </div>

        <div class="bg-white border border-[#CBD5E1] p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">KEBIJAKAN PENYEGELAN</span>
            <div class="mt-1.5 text-lg font-black text-[#0F172A]">
                Surat Fisik Langsung
            </div>
            <span class="text-[11px] text-[#475569] font-medium mt-1 block">Template Resmi PT. Arista Jaya</span>
        </div>
    </div>

    {{-- Tabel Daftar Surat Penyampaian Otomatis --}}
    <div class="bg-white border border-[#CBD5E1] shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-[#F8FAFC]">
            <div>
                <h3 class="text-sm font-bold text-[#0F172A]">Daftar Surat Penyampaian Otomatis (Tunggakan ≥ 4 Bulan)</h3>
                <p class="text-xs text-[#64748B] mt-0.5">Sistem otomatis menerbitkan draf surat sesuai template PT. Arista Jaya ketika unit belum melunasi ≥ 4 bulan tagihan</p>
            </div>
            <span class="text-xs font-mono font-bold text-[#0F172A] px-2.5 py-1 bg-white border border-[#CBD5E1]">
                Periode: {{ $periodeStr }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#475569] font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-5">Nomor Surat</th>
                        <th class="py-3.5 px-4">Unit</th>
                        <th class="py-3.5 px-4">Pemilik / Warga</th>
                        <th class="py-3.5 px-4">Periode Tunggakan</th>
                        <th class="py-3.5 px-4 text-right">Pemakaian</th>
                        <th class="py-3.5 px-4 text-right">Denda</th>
                        <th class="py-3.5 px-4 text-right">Total Tagihan</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($daftarSurat as $s)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="py-3.5 px-5 font-mono font-bold text-[#0F172A] whitespace-nowrap">
                            {{ $s['nomor_surat'] }}
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 bg-[#0F172A] text-white font-mono font-bold text-xs">
                                {{ $s['nomor_rumah'] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-[#0F172A] block">{{ $s['nama_pemilik'] }}</span>
                            <span class="text-[10px] text-[#64748B]">{{ $s['nama_blok'] }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-semibold text-[#0F172A] block">{{ $s['periode_rentang'] }}</span>
                            <span class="inline-flex items-center gap-1 text-[10px] font-mono font-bold text-[#DC2626] mt-0.5">
                                {{ $s['jumlah_bulan'] }} Bulan Belum Lunas
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-[#0F172A] tabular-nums whitespace-nowrap">
                            Rp {{ number_format($s['total_pemakaian'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-[#DC2626] tabular-nums whitespace-nowrap">
                            Rp {{ number_format($s['total_denda'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-extrabold text-[#0F172A] tabular-nums whitespace-nowrap">
                            Rp {{ number_format($s['grand_total'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-5 text-right whitespace-nowrap">
                            <a href="{{ route('admin.surat-tunggakan.cetak', $s['unit_id']) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0F172A] hover:bg-[#1E293B] text-white font-bold text-[11px] transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.673-2.684-.673-4.008 0-4.411 3.582-8 8-8s8 3.589 8 8c0 1.324-.433 2.932-.673 4.008M3 17.25h18m-15 3h12"/></svg>
                                <span>Lihat & Cetak Surat</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-[#64748B]">
                            <p class="font-bold text-[#0F172A] text-sm">Tidak Ada Unit Menunggak ≥ 4 Bulan</p>
                            <p class="text-xs text-[#64748B] mt-1">Surat Penyampaian akan terbit otomatis di halaman ini apabila terdapat unit yang belum melunasi tagihan selama 4 bulan atau lebih.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
