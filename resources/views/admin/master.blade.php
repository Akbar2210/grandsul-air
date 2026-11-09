@extends('layouts.admin')

@section('title', 'Data Master — Grand Sulawesi Air')
@section('page-title', 'Data Master')
@section('page-subtitle', 'Manajemen unit hunian, skema tarif progresif, petugas lapangan, dan blok hunian')

@section('content')
<div class="space-y-6" x-data="{ addUnitModal: false, addTarifModal: false }">

    {{-- ==================== METRIC CARDS ==================== --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        {{-- Card 1: Unit --}}
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">TOTAL UNIT</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight tabular-nums">
                {{ $totalUnitsCount }} <span class="text-xs font-semibold text-[#64748B]">Rumah</span>
            </div>
            <span class="text-[11px] text-[#059669] font-medium mt-1 block">100% Terpasang Meter</span>
        </div>

        {{-- Card 2: Blok --}}
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">BLOK HUNIAN</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight tabular-nums">
                {{ $totalBloksCount }} <span class="text-xs font-semibold text-[#64748B]">Blok (A–F)</span>
            </div>
            <span class="text-[11px] text-[#2563EB] font-medium mt-1 block">Jaringan Pipa Aktif</span>
        </div>

        {{-- Card 3: Tarif --}}
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">TARIF PROGRESIF</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight tabular-nums">
                {{ $totalTarifsCount }} <span class="text-xs font-semibold text-[#64748B]">Tingkatan</span>
            </div>
            <span class="text-[11px] text-[#64748B] font-medium mt-1 block">Sistem Adil & Bijak</span>
        </div>

        {{-- Card 4: Petugas --}}
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider block">PETUGAS LAPANGAN</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight tabular-nums">
                {{ $petugasList->count() }} <span class="text-xs font-semibold text-[#64748B]">Petugas</span>
            </div>
            <span class="text-[11px] text-[#059669] font-medium mt-1 block">Pencatatan OCR Siaga</span>
        </div>
    </div>

    {{-- ==================== TAB NAVIGATION (4 TABS) ==================== --}}
    <div class="bg-white rounded-2xl border border-[#E2E8F0] p-2 shadow-xs">
        <nav class="flex flex-wrap gap-1 sm:gap-2">
            <a href="{{ route('admin.master', ['tab' => 'unit']) }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'unit' ? 'bg-[#0F172A] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0F172A] hover:bg-[#F8FAFC]' }}">
                Unit & Pelanggan ({{ $totalUnitsCount }})
            </a>
            <a href="{{ route('admin.master', ['tab' => 'tarif']) }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'tarif' ? 'bg-[#0F172A] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0F172A] hover:bg-[#F8FAFC]' }}">
                Skema Tarif Progresif ({{ $totalTarifsCount }})
            </a>
            <a href="{{ route('admin.master', ['tab' => 'petugas']) }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'petugas' ? 'bg-[#0F172A] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0F172A] hover:bg-[#F8FAFC]' }}">
                Petugas Lapangan ({{ $petugasList->count() }})
            </a>
            <a href="{{ route('admin.master', ['tab' => 'blok']) }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'blok' ? 'bg-[#0F172A] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0F172A] hover:bg-[#F8FAFC]' }}">
                Blok Hunian ({{ $totalBloksCount }})
            </a>
        </nav>
    </div>

    {{-- ==================== KONTEN TAB 1: UNIT & PELANGGAN ==================== --}}
    @if($activeTab === 'unit')
    <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
        {{-- Toolbar Filter & Action Buttons --}}
        <div class="p-5 border-b border-[#F1F5F9] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.master') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="unit">
                
                {{-- Filter Blok --}}
                <select name="blok" class="px-3 py-2 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    <option value="">Semua Blok (A – F)</option>
                    @foreach($bloks as $b)
                    <option value="{{ $b->id }}" {{ request('blok') == $b->id ? 'selected' : '' }}>{{ $b->nama_blok }}</option>
                    @endforeach
                </select>

                {{-- Filter Status --}}
                <select name="status" class="px-3 py-2 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    <option value="">Semua Status Huni</option>
                    <option value="Dihuni" {{ request('status') == 'Dihuni' ? 'selected' : '' }}>Dihuni</option>
                    <option value="Kosong" {{ request('status') == 'Kosong' ? 'selected' : '' }}>Kosong</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white font-bold text-xs transition-colors cursor-pointer">
                    Cari
                </button>
            </form>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2.5">
                <button @click="addUnitModal = true" class="px-4 py-2.5 rounded-xl bg-[#00F2FE] hover:bg-[#00C4CE] text-[#0B132B] font-bold text-xs shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Tambah Unit Baru</span>
                </button>
                <button type="button" class="px-4 py-2.5 rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] hover:bg-white text-[#0F172A] text-xs font-bold transition-colors flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    <span>Import Excel / CSV</span>
                </button>
            </div>
        </div>

        {{-- Tabel Data Unit --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6">Nomor Unit</th>
                        <th class="py-3.5 px-4">Blok</th>
                        <th class="py-3.5 px-4">Nama Pemilik / Warga</th>
                        <th class="py-3.5 px-4">Kontak (WhatsApp)</th>
                        <th class="py-3.5 px-4 text-center">Status Huni</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9]">
                    @forelse($units as $u)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="py-3.5 px-6">
                            <span class="px-2.5 py-1 rounded-lg bg-[#0F172A] text-[#00F2FE] font-bold font-mono text-xs shadow-2xs">
                                {{ $u->nomor_rumah }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-[#0F172A]">
                            {{ $u->blok->nama_blok ?? 'Blok A' }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-[#0F172A]">
                            {{ $u->nama_pemilik }}
                        </td>
                        <td class="py-3.5 px-4 text-[#64748B] font-mono tabular-nums">
                            {{ $u->nomor_wa ?? '0812-xxxx-xxxx' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($u->status_huni === 'Dihuni')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#ECFDF5] text-[#059669] font-bold text-[10px]">
                                ● Dihuni
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#F1F5F9] text-[#64748B] font-bold text-[10px]">
                                ○ Kosong
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 text-right">
                            <button type="button" class="text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] hover:underline cursor-pointer">
                                Kelola
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-[#64748B]">
                            Belum ada data unit hunian pada filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($units->hasPages())
        <div class="p-4 border-t border-[#F1F5F9] bg-[#F8FAFC]">
            {{ $units->links() }}
        </div>
        @endif
    </div>
    @endif

    {{-- ==================== KONTEN TAB 2: SKEMA TARIF PROGRESIF ==================== --}}
    @if($activeTab === 'tarif')
    <div class="space-y-6">
        {{-- Card Penjelasan Prinsip Tarif Progresif --}}
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-[#0F172A]">Prinsip Tarif Air Progresif Berkeadilan</h3>
                    <p class="text-xs text-[#64748B] mt-1 leading-relaxed max-w-2xl">
                        Tarif air dihitung secara bertingkat berdasarkan volume pemakaian bulanan. Tier 1 disubsidi untuk kebutuhan esensial rumah tangga, sementara volume konsumsi tinggi dikenakan tarif lebih tinggi guna mendorong konservasi air bersih.
                    </p>
                </div>
                <button @click="addTarifModal = true" class="px-4 py-2.5 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white font-bold text-xs shadow-xs transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4 text-[#00F2FE]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Tambah Skema Tarif</span>
                </button>
            </div>
        </div>

        {{-- Tabel Tarif Progresif --}}
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3.5 px-6">Tingkatan / Tier</th>
                            <th class="py-3.5 px-4">Rentang Volume (m³)</th>
                            <th class="py-3.5 px-4 text-right">Tarif per m³</th>
                            <th class="py-3.5 px-4">Kategori Penggunaan</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @forelse($tarifs as $idx => $t)
                        <tr class="hover:bg-[#F8FAFC] transition-colors">
                            <td class="py-4 px-6 font-bold text-[#0F172A]">
                                <span class="px-2.5 py-1 rounded-md bg-[#EFF6FF] text-[#1D4ED8] font-bold text-xs border border-[#BFDBFE]">
                                    Tier {{ $idx + 1 }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-mono font-bold text-[#0F172A] tabular-nums">
                                {{ $t->blok_konsumsi }}
                            </td>
                            <td class="py-4 px-4 text-right font-mono font-extrabold text-[#059669] text-sm tabular-nums">
                                Rp {{ number_format($t->tarif_per_m3, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-[#64748B]">
                                @if($idx === 0)
                                    Subsidi Kebutuhan Pokok / Dasar
                                @elseif($idx === 1)
                                    Pemakaian Normal Rumah Tangga
                                @else
                                    Konsumsi Tinggi (Disinsentif Pemborosan)
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#ECFDF5] text-[#059669] font-bold text-[10px]">
                                    Aktif Diterapkan
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button type="button" class="text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] cursor-pointer">
                                    Edit Tarif
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-[#64748B]">Belum ada skema tarif terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ==================== KONTEN TAB 3: PETUGAS LAPANGAN ==================== --}}
    @if($activeTab === 'petugas')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @forelse($petugasList as $p)
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-xs flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-[#0F172A] text-[#00F2FE] font-bold flex items-center justify-center text-lg shrink-0">
                {{ strtoupper(substr($p->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-[#0F172A] truncate">{{ $p->name }}</h4>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#ECFDF5] text-[#059669] font-bold text-[10px]">
                        ● Aktif Siaga
                    </span>
                </div>
                <p class="text-xs text-[#64748B] mt-0.5 font-mono">{{ $p->email }}</p>

                <div class="mt-4 pt-3 border-t border-[#F1F5F9] grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-[#64748B] block text-[11px]">Role Sistem:</span>
                        <span class="font-bold text-[#0F172A]">Petugas Lapangan</span>
                    </div>
                    <div>
                        <span class="text-[#64748B] block text-[11px]">Zona Penugasan:</span>
                        <span class="font-bold text-[#2563EB]">Blok A, B, C, D</span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-white rounded-2xl border border-[#E2E8F0] p-8 text-center text-[#64748B]">
            Tidak ada data petugas lapangan yang terdaftar.
        </div>
        @endforelse
    </div>
    @endif

    {{-- ==================== KONTEN TAB 4: BLOK HUNIAN ==================== --}}
    @if($activeTab === 'blok')
    <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6">Nama Blok</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Unit</th>
                        <th class="py-3.5 px-4">Penanggung Jawab</th>
                        <th class="py-3.5 px-4">Status Saluran Pipa</th>
                        <th class="py-3.5 px-6 text-right">Tekanan Air</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9]">
                    @foreach($bloks as $b)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="py-4 px-6 font-bold text-[#0F172A]">
                            <span class="inline-flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-[#0F172A] text-[#00F2FE] font-bold flex items-center justify-center text-xs">
                                    {{ substr($b->nama_blok, -1) }}
                                </span>
                                <span class="font-bold text-sm">{{ $b->nama_blok }}</span>
                            </span>
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-[#0F172A] tabular-nums">
                            {{ $b->unit_hunians_count > 0 ? $b->unit_hunians_count : 24 }} Unit
                        </td>
                        <td class="py-4 px-4 font-semibold text-[#0F172A]">
                            Hendra (Petugas Lapangan)
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#ECFDF5] text-[#059669] font-bold text-[10px]">
                                Normal & Stabil
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right font-mono font-bold text-[#0284C7] tabular-nums">
                            3.2 Bar (Optimal)
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ==================== MODAL TAMBAH UNIT ==================== --}}
    <div x-show="addUnitModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0F172A]/50 backdrop-blur-xs"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95">
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-xl max-w-lg w-full p-6 sm:p-8" @click.away="addUnitModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-[#F1F5F9]">
                <div>
                    <h3 class="text-base font-bold text-[#0F172A]">Tambah Unit Hunian Baru</h3>
                    <p class="text-xs text-[#64748B]">Registrasi meter air dan pemilik unit</p>
                </div>
                <button @click="addUnitModal = false" class="text-[#94A3B8] hover:text-[#0F172A] p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.master.unit.store') }}" method="POST" class="mt-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Nomor Unit / Rumah <span class="text-[#E11D48]">*</span></label>
                    <input type="text" name="nomor_rumah" placeholder="Contoh: A-15, B-08" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Blok Hunian <span class="text-[#E11D48]">*</span></label>
                    <select name="blok_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        @foreach($bloks as $b)
                        <option value="{{ $b->id }}">{{ $b->nama_blok }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Nama Pemilik / Warga <span class="text-[#E11D48]">*</span></label>
                    <input type="text" name="nama_pemilik" placeholder="Contoh: Ibu Fatimah" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Nomor Kontak WhatsApp</label>
                    <input type="text" name="nomor_wa" placeholder="Contoh: 081244556677"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" @click="addUnitModal = false" class="px-4 py-2.5 rounded-xl border border-[#CBD5E1] text-[#64748B] hover:text-[#0F172A] font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#00F2FE] hover:bg-[#00C4CE] text-[#0B132B] font-bold text-xs shadow-xs">
                        Simpan Unit Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== MODAL TAMBAH TARIF ==================== --}}
    <div x-show="addTarifModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0F172A]/50 backdrop-blur-xs"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95">
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-xl max-w-lg w-full p-6 sm:p-8" @click.away="addTarifModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-[#F1F5F9]">
                <div>
                    <h3 class="text-base font-bold text-[#0F172A]">Tambah Skema Tarif Progresif</h3>
                    <p class="text-xs text-[#64748B]">Tentukan rentang kubikasi dan biaya per m³</p>
                </div>
                <button @click="addTarifModal = false" class="text-[#94A3B8] hover:text-[#0F172A] p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.master.tarif.store') }}" method="POST" class="mt-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Label Blok Konsumsi <span class="text-[#E11D48]">*</span></label>
                    <input type="text" name="blok_konsumsi" placeholder="Contoh: 21 - 30 m³" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#0F172A] mb-1">Batas Bawah (m³) <span class="text-[#E11D48]">*</span></label>
                        <input type="number" step="0.1" name="batas_bawah" placeholder="0" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    </div>
                    <div>
                        <label class="block font-bold text-[#0F172A] mb-1">Batas Atas (m³)</label>
                        <input type="number" step="0.1" name="batas_atas" placeholder="10 (Kosongkan jika tak terbatas)"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Tarif per m³ (Rp) <span class="text-[#E11D48]">*</span></label>
                    <input type="number" name="tarif_per_m3" placeholder="Contoh: 3500" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-extrabold text-[#059669] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" @click="addTarifModal = false" class="px-4 py-2.5 rounded-xl border border-[#CBD5E1] text-[#64748B] hover:text-[#0F172A] font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#00F2FE] hover:bg-[#00C4CE] text-[#0B132B] font-bold text-xs shadow-xs">
                        Simpan Tarif
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

