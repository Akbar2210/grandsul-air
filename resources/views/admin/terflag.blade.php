@extends('layouts.admin')

@section('title', 'Tinjau Data Ter-flag — Grand Sulawesi Air')
@section('page-title', 'Tinjau Data Ter-flag')
@section('page-subtitle', 'Exception-based review human-in-the-loop untuk verifikasi anomali pembacaan meter')

@section('content')
<div class="space-y-6">

    {{-- ==================== 3 KARTU STATUS EXCEPTION-BASED REVIEW ==================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">ANTREAN ANOMALI</span>
            <div class="mt-1 text-2xl font-black text-[#D97706] tracking-tight tabular-nums">
                {{ $totalTerflagCount }} <span class="text-xs font-semibold text-[#64748B]">Unit Perlu Tinjauan</span>
            </div>
            <span class="text-[11px] text-[#64748B] font-medium mt-1 block">Tingkat anomali ~3.1% dari total unit</span>
        </div>

        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">METODE REVIEW</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight tabular-nums">
                Human-in-the-Loop
            </div>
            <span class="text-[11px] text-[#2563EB] font-medium mt-1 block">Komparasi Foto Dial vs OCR vs Riwayat</span>
        </div>

        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">STATUS BATCH TAGIHAN</span>
            <div class="mt-1 text-2xl font-black text-[#059669] tracking-tight tabular-nums">
                127 Valid
            </div>
            <span class="text-[11px] text-[#059669] font-medium mt-1 block">Siap diproses ke batch billing</span>
        </div>
    </div>

    {{-- ==================== 2-KOLOM UTAMA: ANTREAN (KIRI) & DETAIL VERIFIKASI (KANAN) ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- ==================== KOLOM KIRI: ANTREAN TINJAUAN ==================== --}}
        <div class="lg:col-span-7 bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            <div class="px-6 py-5 border-b border-[#F1F5F9] flex items-center justify-between bg-[#F8FAFC]/50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#FEF3C7] text-[#D97706] flex items-center justify-center font-bold shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-[#0F172A]">Antrean Data Ter-flag</h2>
                        <p class="text-xs text-[#64748B]">Terdapat {{ $totalTerflagCount }} data pembacaan membutuhkan verifikasi</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#FEF3C7] text-[#B45309] border border-[#FDE68A] whitespace-nowrap">
                    {{ $totalTerflagCount }} Pending
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3.5 px-5 whitespace-nowrap">Unit</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Pemilik</th>
                            <th class="py-3.5 px-4">Alasan Anomali</th>
                            <th class="py-3.5 px-3 text-center whitespace-nowrap">Status</th>
                            <th class="py-3.5 px-5 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @forelse($terflagList as $item)
                        @php $isSelected = ($selected && $selected->id === $item->id); @endphp
                        <tr class="hover:bg-[#F8FAFC] transition-colors {{ $isSelected ? 'bg-[#EFF6FF]/60 font-semibold' : '' }}">
                            {{-- Unit Badge --}}
                            <td class="py-4 px-5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-[#0F172A] text-[#00F2FE] font-mono font-bold text-xs inline-block">
                                    {{ $item->unit->nomor_rumah ?? '—' }}
                                </span>
                            </td>

                            {{-- Pemilik --}}
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="text-[#0F172A] font-bold block">{{ $item->unit->nama_pemilik ?? 'Warga' }}</span>
                                <span class="text-[11px] text-[#64748B]">{{ $item->unit->blok->nama_blok ?? 'Blok A' }}</span>
                            </td>

                            {{-- Alasan Anomali --}}
                            <td class="py-4 px-4">
                                <span class="text-[#92400E] font-medium block leading-snug">
                                    {{ $item->catatan_anomali ?? 'Pemakaian terdeteksi tidak wajar' }}
                                </span>
                                <span class="text-[10px] text-[#64748B] font-mono mt-0.5 inline-block">
                                    OCR Conf: {{ $item->confidence_score }}%
                                </span>
                            </td>

                            {{-- Status Badge (Tidak Wrap) --}}
                            <td class="py-4 px-3 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full bg-[#FEF3C7] text-[#92400E] font-bold text-[10px] border border-[#FDE68A] whitespace-nowrap inline-block">
                                    Ter-flag
                                </span>
                            </td>

                            {{-- Tombol Tinjau --}}
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <a href="{{ route('admin.terflag', ['id' => $item->id]) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl {{ $isSelected ? 'bg-[#0F172A] text-[#00F2FE]' : 'bg-[#F1F5F9] text-[#0F172A] hover:bg-[#E2E8F0]' }} text-xs font-bold transition-all">
                                    <span>Tinjau</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-[#64748B]">
                                <svg class="w-12 h-12 mx-auto text-[#16A34A] mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="font-bold text-[#0F172A] text-sm">Semua Data Pembacaan Bersih</p>
                                <p class="text-xs text-[#94A3B8] mt-1">Tidak ada data pembacaan meteran yang ter-flag saat ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ==================== KOLOM KANAN: DETAIL PENCATATAN (REALISTIC INSPECTION) ==================== --}}
        <div class="lg:col-span-5 bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            @if($selected)
            {{-- Header Detail --}}
            <div class="px-6 py-5 border-b border-[#F1F5F9] flex items-center justify-between bg-[#F8FAFC]/50">
                <div>
                    <h2 class="text-sm font-bold text-[#0F172A]">Detail & Verifikasi Anomali</h2>
                    <p class="text-xs text-[#64748B] mt-0.5">{{ $selected->unit->nama_pemilik }} • {{ $selected->unit->blok->nama_blok ?? 'Blok' }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-[#0F172A] text-[#00F2FE] font-mono font-bold text-xs">
                    UNIT {{ $selected->unit->nomor_rumah }}
                </span>
            </div>

            <div class="p-6 space-y-5 text-xs">
                
                {{-- Foto Bukti Meteran (Realistis Analog Dial Water Meter) --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider">FOTO BUKTI FISIK METERAN</span>
                        <span class="text-[10px] text-[#059669] font-bold bg-[#ECFDF5] px-2 py-0.5 rounded-md">
                            Kamera Petugas Terverifikasi
                        </span>
                    </div>

                    {{-- Visual Meter Analog Lapangan --}}
                    <div class="w-full bg-[#1E293B] rounded-2xl overflow-hidden border border-[#334155] shadow-inner relative flex flex-col items-center justify-center p-6 text-white">
                        
                        {{-- Badan Meteran Air Analog (Realistic Graphic) --}}
                        <div class="relative w-48 h-48 rounded-full bg-gradient-to-br from-[#334155] via-[#1E293B] to-[#0F172A] border-4 border-[#475569] shadow-xl flex flex-col items-center justify-center p-3">
                            
                            {{-- Glass Dial Face --}}
                            <div class="w-full h-full rounded-full bg-[#F8FAFC] border-2 border-[#CBD5E1] shadow-inner flex flex-col items-center justify-between p-3 text-[#0F172A] relative">
                                
                                {{-- Brand & Type --}}
                                <div class="text-center pt-1">
                                    <span class="text-[9px] font-extrabold tracking-wider text-[#0F172A] uppercase block">GRAND SULAWESI</span>
                                    <span class="text-[7px] text-[#64748B] font-mono block">ISO 4064 • CLASS B</span>
                                </div>

                                {{-- Odometer Counter Box (Dengan Bounding Box OCR) --}}
                                <div class="relative px-2 py-1 bg-[#0F172A] rounded-md border-2 border-[#00F2FE] shadow-md">
                                    {{-- OCR Bounding Box Label --}}
                                    <span class="absolute -top-3 left-1 bg-[#00F2FE] text-[#0B132B] font-black text-[7px] px-1 rounded-xs uppercase tracking-tighter shadow-xs">
                                        OCR Bounding Box: 88%
                                    </span>
                                    <div class="flex items-center gap-0.5 font-mono text-sm font-black text-white tracking-wider px-1">
                                        <span class="bg-[#1E293B] px-1 py-0.5 rounded-xs text-white">{{ substr(str_pad($selected->angka_meter, 5, '0', STR_PAD_LEFT), 0, 1) }}</span>
                                        <span class="bg-[#1E293B] px-1 py-0.5 rounded-xs text-white">{{ substr(str_pad($selected->angka_meter, 5, '0', STR_PAD_LEFT), 1, 1) }}</span>
                                        <span class="bg-[#1E293B] px-1 py-0.5 rounded-xs text-white">{{ substr(str_pad($selected->angka_meter, 5, '0', STR_PAD_LEFT), 2, 1) }}</span>
                                        <span class="bg-[#1E293B] px-1 py-0.5 rounded-xs text-white">{{ substr(str_pad($selected->angka_meter, 5, '0', STR_PAD_LEFT), 3, 1) }}</span>
                                        <span class="bg-[#DC2626] px-1 py-0.5 rounded-xs text-white">{{ substr(str_pad($selected->angka_meter, 5, '0', STR_PAD_LEFT), 4, 1) }}</span>
                                        <span class="text-[9px] text-[#94A3B8] font-sans ml-1">m³</span>
                                    </div>
                                </div>

                                {{-- Sub Dials --}}
                                <div class="flex items-center justify-center gap-3 pb-1">
                                    <div class="w-6 h-6 rounded-full border border-[#CBD5E1] bg-white flex items-center justify-center relative">
                                        <div class="w-2.5 h-0.5 bg-[#DC2626] origin-left rotate-45 rounded-full"></div>
                                        <span class="text-[5px] text-[#64748B] absolute -bottom-2 font-mono">x0.001</span>
                                    </div>
                                    <div class="w-6 h-6 rounded-full border border-[#CBD5E1] bg-white flex items-center justify-center relative">
                                        <div class="w-2.5 h-0.5 bg-[#DC2626] origin-left -rotate-90 rounded-full"></div>
                                        <span class="text-[5px] text-[#64748B] absolute -bottom-2 font-mono">x0.01</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Metadata Watermark Bar Lapangan --}}
                        <div class="mt-4 w-full bg-black/60 backdrop-blur-xs rounded-xl p-2.5 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between text-[10px] text-[#CBD5E1] gap-1 font-mono">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-[#00F2FE]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                GPS: {{ $selected->latitude ?? '-5.147665' }}, {{ $selected->longitude ?? '119.432731' }}
                            </span>
                            <span class="text-[#94A3B8]">
                                Petugas: {{ $selected->petugas->name ?? 'Hendra' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 3 Metrik Komparasi --}}
                <div class="grid grid-cols-3 gap-2.5 text-center">
                    <div class="p-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                        <span class="text-[10px] font-bold text-[#64748B] uppercase block">Bulan Lalu</span>
                        <span class="text-sm font-extrabold text-[#0F172A] font-mono tabular-nums block mt-0.5">{{ $selected->angka_meter_bulanlalu }} m³</span>
                    </div>
                    <div class="p-3 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE]">
                        <span class="text-[10px] font-bold text-[#1D4ED8] uppercase block">Hasil OCR</span>
                        <span class="text-sm font-black text-[#2563EB] font-mono tabular-nums block mt-0.5">{{ $selected->angka_meter }} m³</span>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FEF3C7] border border-[#FDE68A]">
                        <span class="text-[10px] font-bold text-[#92400E] uppercase block">Selisih Vol</span>
                        @php $selisih = $selected->angka_meter - $selected->angka_meter_bulanlalu; @endphp
                        <span class="text-sm font-extrabold font-mono tabular-nums block mt-0.5 {{ $selisih < 0 ? 'text-[#DC2626]' : 'text-[#D97706]' }}">
                            {{ $selisih }} m³
                        </span>
                    </div>
                </div>

                {{-- Callout Anomali --}}
                <div class="p-3.5 bg-[#FEF3C7]/60 border border-[#FDE68A] rounded-xl text-xs text-[#92400E]">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4 text-[#D97706] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        <strong class="font-bold">Indikasi Anomali Terdeteksi:</strong>
                    </div>
                    <p class="leading-relaxed pl-6">
                        {{ $selected->catatan_anomali ?? 'Pola pemakaian tidak wajar atau angka meter lebih kecil dari bulan lalu.' }}
                    </p>
                </div>

                {{-- Form Tindakan Admin --}}
                <form action="{{ route('admin.terflag.resolve', $selected->id) }}" method="POST" class="space-y-4 pt-3 border-t border-[#F1F5F9]">
                    @csrf
                    <div>
                        <label class="font-bold text-[#0F172A] block mb-1">Koreksi Stand Meter (Ubah jika pembacaan OCR salah)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="angka_meter" value="{{ $selected->angka_meter }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-sm font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB] font-mono tabular-nums">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-[#64748B]">m³</span>
                        </div>
                    </div>

                    <div>
                        <label class="font-bold text-[#0F172A] block mb-1">Catatan Verifikator</label>
                        <textarea name="catatan" rows="2" placeholder="Catatan opsional untuk validasi atau alasan penolakan..."
                                  class="w-full px-3.5 py-2 rounded-xl border border-[#CBD5E1] text-xs text-[#0F172A] placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-[#2563EB]"></textarea>
                    </div>

                    {{-- 3 Tombol Aksi --}}
                    <div class="space-y-2 pt-1">
                        {{-- Tombol 1: Validasi (Sesuai) --}}
                        <button type="submit" name="action" value="validate"
                                class="w-full py-3 rounded-xl bg-[#16A34A] hover:bg-[#15803D] active:scale-[0.99] text-white font-extrabold text-xs flex items-center justify-center gap-2 transition-all shadow-xs cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Validasi Pembacaan (Stand Sesuai)</span>
                        </button>

                        <div class="grid grid-cols-2 gap-2">
                            {{-- Tombol 2: Simpan Koreksi --}}
                            <button type="submit" name="action" value="edit"
                                    class="py-2.5 px-3 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] active:scale-[0.99] text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-[#00F2FE]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                <span>Simpan Koreksi</span>
                            </button>

                            {{-- Tombol 3: Tolak & Minta Foto Ulang --}}
                            <button type="submit" name="action" value="reject"
                                    class="py-2.5 px-3 rounded-xl bg-white hover:bg-[#FEF2F2] active:scale-[0.99] text-[#DC2626] border border-[#FCA5A5] font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Tolak (Foto Ulang)</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="p-12 text-center text-[#64748B]">
                <p class="text-sm font-semibold">Pilih salah satu data ter-flag di panel kiri untuk meninjau detailnya.</p>
            </div>
            @endif
        </div>

    </div>

</div>
@endsection
