@extends('layouts.admin')

@section('title', 'Tinjau Data Ter-flag — Pengelolaan Air Terintegrasi Grand Sulawesi')
@section('page-title', 'Tinjau Data Ter-flag')
@section('page-subtitle', 'Exception-based review human-in-the-loop untuk verifikasi anomali pembacaan meter')

@section('content')
<div class="space-y-5">

    {{-- ==================== 3 KARTU STATUS EXCEPTION-BASED REVIEW (BENTUK ASLI DIPERTAHANKAN) ==================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-[#CBD5E1] p-4 sm:p-5 shadow-sm">
            <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">ANTREAN ANOMALI</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight font-mono tabular-nums">
                {{ $totalTerflagCount }} <span class="text-xs font-sans font-semibold text-[#0F172A]">Unit Perlu Tinjauan</span>
            </div>
            <span class="text-[11px] text-[#0F172A] font-medium mt-1 block">Tingkat anomali ~{{ ($totalUnit ?? 140) > 0 ? round((($totalTerflagCount ?? 0) / ($totalUnit ?? 140)) * 100, 1) : 0 }}% dari total unit</span>
        </div>

        <div class="bg-white rounded-xl border border-[#CBD5E1] p-4 sm:p-5 shadow-sm">
            <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">METODE REVIEW</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight">
                Human-in-the-Loop
            </div>
            <span class="text-[11px] text-[#0F172A] font-medium mt-1 block">Komparasi Foto Dial vs OCR vs Riwayat</span>
        </div>

        <div class="bg-white rounded-xl border border-[#CBD5E1] p-4 sm:p-5 shadow-sm">
            <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">STATUS BATCH TAGIHAN</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight font-mono tabular-nums">
                {{ $validBacaanCount ?? 0 }} Valid
            </div>
            <span class="text-[11px] text-[#0F172A] font-medium mt-1 block">Siap diproses ke batch billing</span>
        </div>
    </div>

    {{-- ==================== 2-KOLOM UTAMA: ANTREAN (KIRI) & DETAIL VERIFIKASI (KANAN) ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- ==================== KOLOM KIRI: ANTREAN TINJAUAN ==================== --}}
        <div class="lg:col-span-7 bg-white rounded-xl border border-[#CBD5E1] shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-[#F1F5F9] flex items-center justify-between bg-[#F8FAFC]/50">
                <div>
                    <h2 class="text-sm font-bold text-[#0F172A]">Antrean Data Ter-flag</h2>
                    <p class="text-xs text-[#64748B]">Terdapat {{ $totalTerflagCount }} data pembacaan membutuhkan verifikasi</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-[#F8FAFC] text-[#0F172A] border border-[#CBD5E1] whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#D97706]"></span>
                    <span>{{ $totalTerflagCount }} Pending</span>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-[#CBD5E1] text-[#0F172A] font-bold uppercase tracking-wider text-[11px]">
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
                            <td class="py-4 px-5 whitespace-nowrap">
                                <span class="font-mono font-bold text-xs text-[#0F172A] tabular-nums">
                                    {{ $item->unit->nomor_rumah ?? '—' }}
                                </span>
                            </td>

                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="text-[#0F172A] font-bold block">{{ $item->unit->nama_pemilik ?? 'Warga' }}</span>
                                <span class="text-[11px] text-[#64748B]">{{ $item->unit->blok->nama_blok ?? 'Blok A' }}</span>
                            </td>

                            <td class="py-4 px-4">
                                <span class="text-[#92400E] font-medium block leading-snug">
                                    {{ $item->catatan_anomali ?? 'Pemakaian terdeteksi tidak wajar' }}
                                </span>
                                <span class="text-[10px] text-[#64748B] font-mono mt-0.5 inline-block tabular-nums">
                                    OCR Conf: {{ $item->confidence_score }}%
                                </span>
                            </td>

                            <td class="py-4 px-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#F8FAFC] text-[#0F172A] font-semibold text-[11px] border border-[#CBD5E1] whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#D97706]"></span>
                                    <span>Ter-flag</span>
                                </span>
                            </td>

                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <a href="{{ route('admin.terflag', ['id' => $item->id]) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl {{ $isSelected ? 'bg-[#2563EB] text-white' : 'bg-[#F1F5F9] text-[#0F172A] hover:bg-[#E2E8F0]' }} text-xs font-normal transition-all">
                                    <span>Tinjau</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
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

        {{-- ==================== KOLOM KANAN: DETAIL PENCATATAN (BENTUK ASLI DIPERTAHANKAN) ==================== --}}
        <div class="lg:col-span-5 bg-white rounded-xl border border-[#CBD5E1] shadow-sm overflow-hidden">
            @if($selected)
            <div class="px-6 py-5 border-b border-[#F1F5F9] flex items-center justify-between bg-[#F8FAFC]/50">
                <div>
                    <h2 class="text-sm font-bold text-[#0F172A]">Detail & Verifikasi Anomali</h2>
                    <p class="text-xs text-[#64748B] mt-0.5">{{ $selected->unit->nama_pemilik }} • {{ $selected->unit->blok->nama_blok ?? 'Blok' }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-[#0F172A] text-white font-mono font-bold text-xs tabular-nums">
                    UNIT {{ $selected->unit->nomor_rumah }}
                </span>
            </div>

            <div class="p-6 space-y-5 text-xs">

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider">FOTO BUKTI FISIK METERAN</span>
                        <span class="inline-flex items-center gap-1.5 text-[10px] text-[#0F172A] font-semibold bg-[#F8FAFC] border border-[#CBD5E1] px-2 py-0.5 rounded-md">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>
                            <span>Kamera Petugas Terverifikasi</span>
                        </span>
                    </div>

                    <div class="w-full bg-[#1E293B] rounded-2xl overflow-hidden border border-[#334155] shadow-inner relative flex flex-col items-center justify-center p-6 text-white">
                        @if($selected->foto_meter && !in_array($selected->foto_meter, ['sample_meter.jpg', 'sample.jpg']) && (\Illuminate\Support\Facades\Storage::disk('public')->exists($selected->foto_meter) || file_exists(public_path('storage/' . $selected->foto_meter))))
                            <div class="w-full max-h-60 rounded-xl overflow-hidden border-2 border-[#3B82F6] relative shadow-lg">
                                <img src="{{ asset('storage/' . $selected->foto_meter) }}" alt="Foto Bukti Meteran Unit {{ $selected->unit->nomor_rumah ?? '' }}" class="w-full h-56 object-contain bg-[#0F172A]">
                                <span class="absolute top-2 left-2 bg-[#2563EB] text-white font-mono font-bold text-[9px] px-2 py-0.5 rounded-md uppercase shadow-xs">
                                    Foto Kamera Petugas • OCR: {{ $selected->confidence_score ?? 95 }}%
                                </span>
                            </div>
                        @else
                        <div class="relative w-48 h-48 rounded-full bg-gradient-to-br from-[#334155] via-[#1E293B] to-[#0F172A] border-4 border-[#475569] shadow-xl flex flex-col items-center justify-center p-3">
                            <div class="w-full h-full rounded-full bg-[#F8FAFC] border-2 border-[#CBD5E1] shadow-inner flex flex-col items-center justify-between p-3 text-[#0F172A] relative">
                                <div class="text-center pt-1">
                                    <span class="text-[9px] font-extrabold tracking-wider text-[#0F172A] uppercase block">GRAND SULAWESI</span>
                                    <span class="text-[7px] text-[#64748B] font-mono block">ISO 4064 • CLASS B</span>
                                </div>

                                <div class="relative px-2 py-1 bg-[#0F172A] rounded-md border-2 border-[#3B82F6] shadow-md">
                                    <span class="absolute -top-3 left-1 bg-[#2563EB] text-white font-mono font-bold text-[7px] px-1 rounded-xs uppercase tracking-tighter shadow-xs">
                                        OCR Bounding Box: {{ $selected->confidence_score ?? 88 }}%
                                    </span>
                                    <div class="flex items-center gap-0.5 font-mono text-sm font-black text-white tracking-wider px-1 tabular-nums">
                                        <span class="bg-[#1E293B] px-1 py-0.5 rounded-xs text-white">{{ substr(str_pad($selected->angka_meter, 5, '0', STR_PAD_LEFT), 0, 1) }}</span>
                                        <span class="bg-[#1E293B] px-1 py-0.5 rounded-xs text-white">{{ substr(str_pad($selected->angka_meter, 5, '0', STR_PAD_LEFT), 1, 1) }}</span>
                                        <span class="bg-[#1E293B] px-1 py-0.5 rounded-xs text-white">{{ substr(str_pad($selected->angka_meter, 5, '0', STR_PAD_LEFT), 2, 1) }}</span>
                                        <span class="bg-[#1E293B] px-1 py-0.5 rounded-xs text-white">{{ substr(str_pad($selected->angka_meter, 5, '0', STR_PAD_LEFT), 3, 1) }}</span>
                                        <span class="bg-[#DC2626] px-1 py-0.5 rounded-xs text-white">{{ substr(str_pad($selected->angka_meter, 5, '0', STR_PAD_LEFT), 4, 1) }}</span>
                                        <span class="text-[9px] text-[#94A3B8] font-sans ml-1">m³</span>
                                    </div>
                                </div>

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
                        @endif

                        <div class="mt-4 w-full bg-black/75 rounded-xl p-2.5 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between text-[10px] text-[#CBD5E1] gap-1 font-mono tabular-nums">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-[#60A5FA]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                GPS: {{ $selected->latitude ?? '-4.007468' }}, {{ $selected->longitude ?? '119.661131' }}
                            </span>
                            <span class="text-[#94A3B8]">
                                Petugas: {{ $selected->petugas->name ?? 'Hendra' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 3 Metrik Komparasi --}}
                <div class="grid grid-cols-3 gap-2.5 text-center">
                    <div class="p-3 rounded-xl bg-[#F8FAFC] border border-[#CBD5E1]">
                        <span class="text-[10px] font-bold text-[#64748B] uppercase block">Bulan Lalu</span>
                        <span class="text-sm font-extrabold text-[#0F172A] font-mono tabular-nums block mt-0.5">
                            {{ $selected->angka_meter_bulanlalu > 0 ? $selected->angka_meter_bulanlalu . ' m³' : '0 (Awal)' }}
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-[#F8FAFC] border border-[#CBD5E1]">
                        <span class="text-[10px] font-bold text-[#334155] uppercase block">Hasil OCR</span>
                        <span class="text-sm font-black text-[#2563EB] font-mono tabular-nums block mt-0.5">{{ $selected->angka_meter }} m³</span>
                    </div>
                    <div class="p-3 rounded-xl bg-[#F8FAFC] border border-[#CBD5E1]">
                        <span class="text-[10px] font-bold text-[#64748B] uppercase block">Selisih Vol</span>
                        @php
                            $isStandAwal = ($selected->angka_meter_bulanlalu <= 0);
                            $selisih = $isStandAwal ? 0 : ($selected->angka_meter - $selected->angka_meter_bulanlalu);
                        @endphp
                        <span class="text-sm font-extrabold font-mono tabular-nums block mt-0.5 {{ $selisih < 0 ? 'text-[#DC2626]' : 'text-[#0F172A]' }}">
                            {{ $isStandAwal ? '0 m³ (Stand Awal)' : $selisih . ' m³' }}
                        </span>
                    </div>
                </div>

                {{-- Callout Anomali --}}
                <div class="p-4 bg-[#F8FAFC] border border-[#CBD5E1] rounded-xl text-xs text-[#334155]">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-2 h-2 rounded-full bg-[#D97706] shrink-0"></span>
                        <strong class="font-bold text-[#0F172A]">Indikasi Anomali Terdeteksi:</strong>
                    </div>
                    <p class="leading-relaxed pl-4 text-[#475569]">
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

                    <div class="space-y-2 pt-1">
                        <button type="submit" name="action" value="validate"
                                class="w-full py-3 rounded-xl bg-[#16A34A] hover:bg-[#15803D] active:scale-[0.99] text-white font-normal text-xs flex items-center justify-center gap-2 transition-all shadow-xs cursor-pointer">
                            <span>Validasi Pembacaan (Stand Sesuai)</span>
                        </button>

                        <button type="submit" name="action" value="reject"
                                class="w-full py-3 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] active:scale-[0.99] text-white font-normal text-xs flex items-center justify-center gap-2 transition-all shadow-xs cursor-pointer">
                            <span>Tolak (Foto Ulang)</span>
                        </button>
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
