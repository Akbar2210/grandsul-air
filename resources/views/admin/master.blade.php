@extends('layouts.admin')

@section('title', 'Data Master — Pengelolaan Air Terintegrasi Grand Sulawesi')
@section('page-title', 'Data Master')
@section('page-subtitle', 'Manajemen unit hunian, skema tarif progresif, petugas lapangan, dan blok hunian')

@section('content')
<div class="space-y-5" x-data="{
    addUnitModal: false,
    addTarifModal: false,
    editUnitModal: false,
    editTarifModal: false,
    importModal: false,
    addPetugasModal: false,
    editPetugasModal: false,
    addBlokModal: false,
    unitData: { id: '', nomor_rumah: '', blok_id: '', nama_pemilik: '', nomor_wa: '', status_huni: 'Dihuni' },
    tarifData: { id: '', blok_konsumsi: '', batas_bawah: 0, batas_atas: '', tarif_per_m3: 0 },
    petugasData: { id: '', name: '', email: '', password: '', zona_type: 'semua', zona_blok: [], is_active: true },
    openEditUnit(u) {
        this.unitData = {
            id: u.id,
            nomor_rumah: u.nomor_rumah,
            blok_id: u.blok_id,
            nama_pemilik: u.nama_pemilik,
            nomor_wa: u.nomor_wa || '',
            status_huni: u.status_huni || 'Dihuni'
        };
        this.editUnitModal = true;
    },
    openEditTarif(t) {
        this.tarifData = {
            id: t.id,
            blok_konsumsi: t.blok_konsumsi,
            batas_bawah: t.batas_bawah,
            batas_atas: t.batas_atas !== null ? t.batas_atas : '',
            tarif_per_m3: Math.round(t.tarif_per_m3)
        };
        this.editTarifModal = true;
    },
    openAddPetugas() {
        this.petugasData = { id: '', name: '', email: '', password: '', zona_type: 'semua', zona_blok: [], is_active: true };
        this.addPetugasModal = true;
    },
    openEditPetugas(p) {
        let isSemua = !p.zona_penugasan || p.zona_penugasan.toLowerCase().includes('semua');
        let selectedBloks = [];
        if (!isSemua && p.zona_penugasan) {
            selectedBloks = p.zona_penugasan.split(',').map(s => s.trim());
        }
        this.petugasData = {
            id: p.id,
            name: p.name,
            email: p.email,
            password: '',
            zona_type: isSemua ? 'semua' : 'spesifik',
            zona_blok: selectedBloks,
            is_active: Boolean(p.is_active ?? true)
        };
        this.editPetugasModal = true;
    },
    toggleBlokSelection(blokName) {
        const idx = this.petugasData.zona_blok.indexOf(blokName);
        if (idx > -1) {
            this.petugasData.zona_blok.splice(idx, 1);
        } else {
            this.petugasData.zona_blok.push(blokName);
        }
    }
}">

    {{-- ==================== METRIC CARDS (BENTUK ASLI DIPERTAHANKAN) ==================== --}}
    <div id="master-metrics" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-[#CBD5E1] p-4 sm:p-5 shadow-sm">
            <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">TOTAL UNIT</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight font-mono tabular-nums">
                {{ $totalUnitsCount }} <span class="text-xs font-sans font-semibold text-[#475569]">Rumah</span>
            </div>
            <span class="text-[11px] text-[#059669] font-medium mt-1 block">100% Terpasang Meter</span>
        </div>

        <div class="bg-white rounded-xl border border-[#CBD5E1] p-4 sm:p-5 shadow-sm">
            <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">BLOK HUNIAN</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight font-mono tabular-nums">
                {{ $totalBloksCount }} <span class="text-xs font-sans font-semibold text-[#475569]">Blok (A–F)</span>
            </div>
            <span class="text-[11px] text-[#2563EB] font-medium mt-1 block">Jaringan Pipa Aktif</span>
        </div>

        <div class="bg-white rounded-xl border border-[#CBD5E1] p-4 sm:p-5 shadow-sm">
            <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">TARIF PROGRESIF</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight font-mono tabular-nums">
                {{ $totalTarifsCount }} <span class="text-xs font-sans font-semibold text-[#475569]">Tingkatan</span>
            </div>
            <span class="text-[11px] text-[#64748B] font-medium mt-1 block">Sistem Adil & Bijak</span>
        </div>

        <div class="bg-white rounded-xl border border-[#CBD5E1] p-4 sm:p-5 shadow-sm">
            <span class="text-[11px] font-bold text-[#0F172A] uppercase tracking-wider block">PETUGAS LAPANGAN</span>
            <div class="mt-1 text-2xl font-black text-[#0F172A] tracking-tight font-mono tabular-nums">
                {{ $petugasList->count() }} <span class="text-xs font-sans font-semibold text-[#475569]">Petugas</span>
            </div>
            <span class="text-[11px] text-[#059669] font-medium mt-1 block">Pencatatan OCR Siaga</span>
        </div>
    </div>

    {{-- ==================== TAB NAVIGATION (UNDERLINE TABS BERSIH) ==================== --}}
    <div id="master-tabs" class="bg-white rounded-xl border border-[#CBD5E1] shadow-sm overflow-hidden">
        <nav class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-[#E2E8F0]">
            <a href="{{ route('admin.master', ['tab' => 'unit']) }}"
               class="relative h-12 px-4 flex items-center justify-center gap-2 text-xs transition-colors {{ $activeTab === 'unit' ? 'text-[#0F172A] font-bold bg-[#F8FAFC]/60' : 'text-[#64748B] font-medium hover:text-[#0F172A] hover:bg-[#F8FAFC]' }}">
                <span class="truncate">Unit &amp; Pelanggan</span>
                <span class="px-1.5 py-0.5 rounded text-[11px] font-mono tabular-nums {{ $activeTab === 'unit' ? 'bg-[#EFF6FF] text-[#2563EB] font-bold' : 'bg-[#F1F5F9] text-[#64748B] font-semibold' }}">
                    {{ $totalUnitsCount }}
                </span>
                @if($activeTab === 'unit')
                    <span class="absolute bottom-0 inset-x-0 h-[3px] bg-[#2563EB]"></span>
                @endif
            </a>

            <a href="{{ route('admin.master', ['tab' => 'tarif']) }}"
               class="relative h-12 px-4 flex items-center justify-center gap-2 text-xs transition-colors {{ $activeTab === 'tarif' ? 'text-[#0F172A] font-bold bg-[#F8FAFC]/60' : 'text-[#64748B] font-medium hover:text-[#0F172A] hover:bg-[#F8FAFC]' }}">
                <span class="truncate">Skema Tarif Progresif</span>
                <span class="px-1.5 py-0.5 rounded text-[11px] font-mono tabular-nums {{ $activeTab === 'tarif' ? 'bg-[#EFF6FF] text-[#2563EB] font-bold' : 'bg-[#F1F5F9] text-[#64748B] font-semibold' }}">
                    {{ $totalTarifsCount }}
                </span>
                @if($activeTab === 'tarif')
                    <span class="absolute bottom-0 inset-x-0 h-[3px] bg-[#2563EB]"></span>
                @endif
            </a>

            <a href="{{ route('admin.master', ['tab' => 'petugas']) }}"
               class="relative h-12 px-4 flex items-center justify-center gap-2 text-xs transition-colors {{ $activeTab === 'petugas' ? 'text-[#0F172A] font-bold bg-[#F8FAFC]/60' : 'text-[#64748B] font-medium hover:text-[#0F172A] hover:bg-[#F8FAFC]' }}">
                <span class="truncate">Petugas Lapangan</span>
                <span class="px-1.5 py-0.5 rounded text-[11px] font-mono tabular-nums {{ $activeTab === 'petugas' ? 'bg-[#EFF6FF] text-[#2563EB] font-bold' : 'bg-[#F1F5F9] text-[#64748B] font-semibold' }}">
                    {{ $petugasList->count() }}
                </span>
                @if($activeTab === 'petugas')
                    <span class="absolute bottom-0 inset-x-0 h-[3px] bg-[#2563EB]"></span>
                @endif
            </a>

            <a href="{{ route('admin.master', ['tab' => 'blok']) }}"
               class="relative h-12 px-4 flex items-center justify-center gap-2 text-xs transition-colors {{ $activeTab === 'blok' ? 'text-[#0F172A] font-bold bg-[#F8FAFC]/60' : 'text-[#64748B] font-medium hover:text-[#0F172A] hover:bg-[#F8FAFC]' }}">
                <span class="truncate">Blok Hunian</span>
                <span class="px-1.5 py-0.5 rounded text-[11px] font-mono tabular-nums {{ $activeTab === 'blok' ? 'bg-[#EFF6FF] text-[#2563EB] font-bold' : 'bg-[#F1F5F9] text-[#64748B] font-semibold' }}">
                    {{ $totalBloksCount }}
                </span>
                @if($activeTab === 'blok')
                    <span class="absolute bottom-0 inset-x-0 h-[3px] bg-[#2563EB]"></span>
                @endif
            </a>
        </nav>
    </div>

    {{-- ==================== KONTEN TAB AKTIF (EFEK NAIK / SLIDE-UP) ==================== --}}
    <div id="master-tab-panel" class="tab-slide-up">

    {{-- ==================== KONTEN TAB 1: UNIT & PELANGGAN ==================== --}}
    @if($activeTab === 'unit')
    <div class="bg-white rounded-xl border border-[#CBD5E1] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#F1F5F9] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.master') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="unit">

                <select name="blok" class="pl-3.5 pr-10 py-2 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB] shadow-xs">
                    <option value="">Semua Blok (A – F)</option>
                    @foreach($bloks as $b)
                    <option value="{{ $b->id }}" {{ request('blok') == $b->id ? 'selected' : '' }}>{{ $b->nama_blok }}</option>
                    @endforeach
                </select>

                <select name="status" class="pl-3.5 pr-10 py-2 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB] shadow-xs">
                    <option value="">Semua</option>
                    <option value="Dihuni" {{ request('status') == 'Dihuni' ? 'selected' : '' }}>Dihuni</option>
                    <option value="Kosong" {{ request('status') == 'Kosong' ? 'selected' : '' }}>Kosong</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white font-bold text-xs transition-colors cursor-pointer">
                    Cari
                </button>
            </form>

            <div class="flex items-center gap-2.5">
                <button @click="addUnitModal = true" class="px-4 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Tambah Unit Baru</span>
                </button>
                <button type="button" @click="importModal = true" class="px-4 py-2.5 rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] hover:bg-white text-[#0F172A] text-xs font-bold transition-colors flex items-center gap-2 cursor-pointer shadow-2xs">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    <span>Import Excel / CSV</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-[#CBD5E1] text-[#0F172A] font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6 whitespace-nowrap">Nomor Unit</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Blok</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Nama Pemilik / Warga</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Kontak (WhatsApp)</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Status Huni</th>
                        <th class="py-3.5 px-6 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9]">
                    @forelse($units as $u)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="py-3.5 px-6 whitespace-nowrap">
                            <span class="font-bold font-mono text-xs text-[#0F172A] tabular-nums">
                                {{ $u->nomor_rumah }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-[#0F172A] whitespace-nowrap">
                            {{ $u->blok->nama_blok ?? 'Blok A' }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-[#0F172A] whitespace-nowrap">
                            {{ $u->nama_pemilik }}
                        </td>
                        <td class="py-3.5 px-4 text-center text-[#64748B] font-mono tabular-nums whitespace-nowrap">
                            {{ $u->nomor_wa ?? '0812-xxxx-xxxx' }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            @if($u->status_huni === 'Dihuni')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] font-semibold text-[11px]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>
                                <span>Dihuni</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#64748B] font-semibold text-[11px]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#94A3B8]"></span>
                                <span>Kosong</span>
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 text-center whitespace-nowrap">
                            <button type="button" @click="openEditUnit({{ Js::from($u) }})" class="inline-flex items-center justify-center px-3 py-1 rounded-lg bg-[#EFF6FF] hover:bg-[#DBEAFE] text-xs font-bold text-[#2563EB] transition-colors cursor-pointer shadow-2xs">
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

        @if($units->hasPages())
        <div class="p-4 border-t border-[#F1F5F9] bg-[#F8FAFC]">
            {{ $units->links() }}
        </div>
        @endif
    </div>
    @endif

    {{-- ==================== KONTEN TAB 2: SKEMA TARIF PROGRESIF ==================== --}}
    @if($activeTab === 'tarif')
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-[#CBD5E1] p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-[#0F172A]">Prinsip Tarif Air Progresif Berkeadilan</h3>
                    <p class="text-xs text-[#64748B] mt-1 leading-relaxed max-w-2xl">
                        Tarif air dihitung secara bertingkat berdasarkan volume pemakaian bulanan. Tier 1 disubsidi untuk kebutuhan esensial rumah tangga, sementara volume konsumsi tinggi dikenakan tarif lebih tinggi guna mendorong konservasi air bersih.
                    </p>
                </div>
                <button @click="addTarifModal = true" class="px-4 py-2.5 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white font-bold text-xs shadow-xs transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4 text-[#60A5FA]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Tambah Skema Tarif</span>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-[#CBD5E1] shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-[#CBD5E1] text-[#0F172A] font-bold uppercase tracking-wider text-[11px]">
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
                                <span class="px-2.5 py-1 rounded-md bg-[#EFF6FF] text-[#1D4ED8] font-mono font-bold text-xs border border-[#BFDBFE]">
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
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] font-semibold text-[11px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>
                                    <span>Aktif Diterapkan</span>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button type="button" @click="openEditTarif({{ Js::from($t) }})" class="text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] cursor-pointer hover:underline">
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
    <div class="space-y-4">
        {{-- Header Bar Tab Petugas Lapangan --}}
        <div class="bg-white rounded-xl border border-[#CBD5E1] p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-[#0F172A]">Daftar Petugas Lapangan</h3>
                <p class="text-xs text-[#64748B] mt-0.5">Kelola akun kredensial, username, kata sandi, dan zona penugasan blok petugas</p>
            </div>
            <button type="button" @click="openAddPetugas()" class="px-4 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Petugas</span>
            </button>
        </div>

        {{-- Grid Card Petugas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @forelse($petugasList as $p)
            <div class="bg-white rounded-xl border border-[#CBD5E1] p-6 shadow-sm flex flex-col justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-[#0F172A] text-white font-mono font-bold flex items-center justify-center text-lg shrink-0 shadow-xs">
                        {{ strtoupper(substr($p->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="text-sm font-bold text-[#0F172A] truncate">{{ $p->name }}</h4>
                            @if($p->is_active ?? true)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#F0FDF4] border border-[#BBF7D0] text-[#166534] font-semibold text-[11px] shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>
                                <span>Aktif Siaga</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#FEF2F2] border border-[#FECACA] text-[#991B1B] font-semibold text-[11px] shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#DC2626]"></span>
                                <span>Nonaktif / Berhenti</span>
                            </span>
                            @endif
                        </div>
                        <p class="text-xs text-[#64748B] mt-0.5 font-mono truncate">{{ $p->email }}</p>

                        <div class="mt-4 pt-3 border-t border-[#F1F5F9] grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[#64748B] block text-[11px]">Role Sistem:</span>
                                <span class="font-bold text-[#0F172A]">Petugas Lapangan</span>
                            </div>
                            <div>
                                <span class="text-[#64748B] block text-[11px]">Zona Penugasan:</span>
                                <span class="font-bold text-[#2563EB] leading-tight block">
                                    {{ $p->zona_penugasan ?: 'Semua Blok (A, B, C, D, E, F)' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="pt-3 border-t border-[#F1F5F9] flex items-center justify-between">
                    <div>
                        @if($p->id !== auth()->id())
                        <form action="{{ route('admin.master.petugas.toggle-status', $p->id) }}" method="POST"
                              onsubmit="return confirm('Apakah Anda yakin ingin {{ ($p->is_active ?? true) ? 'menonaktifkan' : 'mengaktifkan kembali' }} akun petugas {{ $p->name }}?');" class="inline">
                            @csrf
                            @if($p->is_active ?? true)
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#FCA5A5] bg-[#FEF2F2] hover:bg-[#FEE2E2] text-xs font-bold text-[#DC2626] transition-colors cursor-pointer shadow-2xs" title="Nonaktifkan jika petugas sudah berhenti bertugas">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                <span>Nonaktifkan Petugas</span>
                            </button>
                            @else
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#BBF7D0] bg-[#F0FDF4] hover:bg-[#DCFCE7] text-xs font-bold text-[#166534] transition-colors cursor-pointer shadow-2xs" title="Aktifkan kembali akun petugas ini">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Aktifkan Kembali</span>
                            </button>
                            @endif
                        </form>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="openEditPetugas({{ Js::from($p) }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#EFF6FF] hover:bg-[#DBEAFE] text-xs font-bold text-[#2563EB] transition-colors cursor-pointer shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            <span>Kelola Akun & Zona</span>
                        </button>
                        @if($petugasList->count() > 1 && $p->id !== auth()->id())
                        <form action="{{ route('admin.master.petugas.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus petugas {{ $p->name }}?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center p-1.5 rounded-lg text-[#DC2626] hover:bg-[#FEF2F2] hover:text-[#B91C1C] transition-colors cursor-pointer" title="Hapus Petugas">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-2 bg-white rounded-xl border border-[#CBD5E1] p-8 text-center text-[#64748B]">
                Tidak ada data petugas lapangan yang terdaftar.
            </div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- ==================== KONTEN TAB 4: BLOK HUNIAN ==================== --}}
    @if($activeTab === 'blok')
    <div class="space-y-4">
        {{-- Header Bar Tab Blok Hunian --}}
        <div class="bg-white rounded-xl border border-[#CBD5E1] p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-[#0F172A]">Daftar Blok Hunian</h3>
                <p class="text-xs text-[#64748B] mt-0.5">Kelola zonasi kawasan hunian dan pemantauan distribusi pencatatan meter</p>
            </div>
            <button type="button" @click="addBlokModal = true" class="px-4 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Blok</span>
            </button>
        </div>

        <div class="bg-white rounded-xl border border-[#CBD5E1] shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-[#CBD5E1] text-[#0F172A] font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3.5 px-6">Nama Blok</th>
                            <th class="py-3.5 px-4 text-center">Jumlah Unit</th>
                            <th class="py-3.5 px-4">Penanggung Jawab</th>
                            <th class="py-3.5 px-4 text-center">Tercatat Bulan Ini</th>
                            <th class="py-3.5 px-6 text-right">Volume Bulan Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @foreach($bloks as $b)
                        @php
                            $bStat = $blokMasterStats[$b->id] ?? ['tercatat' => 0, 'volume' => 0];
                            $assignedPetugas = $petugasList->filter(function($p) use ($b) {
                                if (!($p->is_active ?? true)) {
                                    return false;
                                }
                                $zona = strtolower($p->zona_penugasan ?? '');
                                if (empty($zona) || str_contains($zona, 'semua')) {
                                    return true;
                                }
                                return str_contains(strtolower($p->zona_penugasan), strtolower($b->nama_blok));
                            });
                        @endphp
                        <tr class="hover:bg-[#F8FAFC] transition-colors">
                            <td class="py-4 px-6 font-bold text-sm text-[#0F172A]">
                                {{ $b->nama_blok }}
                                @if($b->keterangan_wilayah)
                                <span class="block text-[11px] font-normal text-[#64748B] mt-0.5">{{ $b->keterangan_wilayah }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center font-mono font-bold text-[#0F172A] tabular-nums">
                                {{ $b->unit_hunians_count }} Unit
                            </td>
                            <td class="py-4 px-4">
                                @if($assignedPetugas->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @foreach($assignedPetugas as $ap)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#EFF6FF] border border-[#BFDBFE] text-[#1E40AF] text-xs font-bold shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB]"></span>
                                        <span>{{ $ap->name }}</span>
                                    </span>
                                    @endforeach
                                </div>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#F8FAFC] border border-[#CBD5E1] text-[#94A3B8] text-xs font-semibold italic">
                                    Belum Ada Petugas
                                </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] font-mono font-semibold text-[11px] tabular-nums">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $bStat['tercatat'] >= $b->unit_hunians_count && $b->unit_hunians_count > 0 ? 'bg-[#16A34A]' : 'bg-[#2563EB]' }}"></span>
                                    <span>{{ $bStat['tercatat'] }} / {{ $b->unit_hunians_count }} Unit</span>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-[#2563EB] tabular-nums">
                                {{ number_format($bStat['volume'], 0, ',', '.') }} m³
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    </div>

    <template x-teleport="body">
    <div>
    {{-- ==================== MODAL TAMBAH UNIT ==================== --}}
    <div x-show="addUnitModal"
         class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 bg-[#0F172A]/60"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95">
        <div class="bg-white rounded-2xl border border-[#CBD5E1] shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8" @click.away="addUnitModal = false">
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
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Blok Hunian <span class="text-[#E11D48]">*</span></label>
                    <select name="blok_id" required class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
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
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs">
                        Simpan Unit Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== MODAL TAMBAH TARIF ==================== --}}
    <div x-show="addTarifModal"
         class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 bg-[#0F172A]/60"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95">
        <div class="bg-white rounded-2xl border border-[#CBD5E1] shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8" @click.away="addTarifModal = false">
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
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
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
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs">
                        Simpan Tarif
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== MODAL KELOLA / EDIT UNIT ==================== --}}
    <div x-show="editUnitModal"
         class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 bg-[#0F172A]/60"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95">
        <div class="bg-white rounded-2xl border border-[#CBD5E1] shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8" @click.away="editUnitModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-[#F1F5F9]">
                <div>
                    <h3 class="text-base font-bold text-[#0F172A]">Kelola & Edit Data Unit</h3>
                    <p class="text-xs text-[#64748B]">Perbarui rincian pemilik, nomor rumah, dan status hunian</p>
                </div>
                <button @click="editUnitModal = false" class="text-[#94A3B8] hover:text-[#0F172A] p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('admin/master/unit') }}/' + unitData.id" method="POST" class="mt-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Nomor Unit / Rumah <span class="text-[#E11D48]">*</span></label>
                    <input type="text" name="nomor_rumah" x-model="unitData.nomor_rumah" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Blok Hunian <span class="text-[#E11D48]">*</span></label>
                    <select name="blok_id" x-model="unitData.blok_id" required class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        @foreach($bloks as $b)
                        <option value="{{ $b->id }}">{{ $b->nama_blok }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Nama Pemilik / Warga <span class="text-[#E11D48]">*</span></label>
                    <input type="text" name="nama_pemilik" x-model="unitData.nama_pemilik" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Nomor Kontak WhatsApp</label>
                    <input type="text" name="nomor_wa" x-model="unitData.nomor_wa"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Status Huni <span class="text-[#E11D48]">*</span></label>
                    <select name="status_huni" x-model="unitData.status_huni" required class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        <option value="Dihuni">Dihuni</option>
                        <option value="Kosong">Kosong</option>
                    </select>
                </div>

                <div class="pt-4 flex items-center justify-between gap-3 border-t border-[#F1F5F9]">
                    <button type="button"
                            @click="if(confirm('Apakah Anda yakin ingin menghapus unit ini?')) { document.getElementById('delete-unit-form-' + unitData.id).submit(); }"
                            class="px-4 py-2.5 rounded-xl bg-[#FEF2F2] hover:bg-[#FEE2E2] text-[#DC2626] border border-[#FCA5A5] font-bold text-xs transition-colors cursor-pointer">
                        Hapus Unit
                    </button>
                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="editUnitModal = false" class="px-4 py-2.5 rounded-xl border border-[#CBD5E1] text-[#64748B] hover:text-[#0F172A] font-bold text-xs">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
            {{-- Hidden delete form helper --}}
            <form :id="'delete-unit-form-' + unitData.id" :action="'{{ url('admin/master/unit') }}/' + unitData.id" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    {{-- ==================== MODAL IMPORT EXCEL / CSV ==================== --}}
    <div x-show="importModal"
         class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 bg-[#0F172A]/60"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95">
        <div class="bg-white rounded-2xl border border-[#CBD5E1] shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8" @click.away="importModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-[#F1F5F9]">
                <div>
                    <h3 class="text-base font-bold text-[#0F172A]">Import Data Unit (Excel / CSV)</h3>
                    <p class="text-xs text-[#64748B]">Unggah daftar unit hunian secara massal</p>
                </div>
                <button @click="importModal = false" class="text-[#94A3B8] hover:text-[#0F172A] p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.master.unit.import') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4 text-xs">
                @csrf

                <div class="p-4 rounded-xl bg-[#F8FAFC] border border-[#CBD5E1] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-[#0F172A]">Format File Diperlukan:</span>
                        <a href="{{ route('admin.master.unit.template') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2563EB] hover:underline">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Unduh Template CSV</span>
                        </a>
                    </div>
                    <p class="text-[11px] text-[#64748B] leading-relaxed">
                        Kolom urutan berkas: <code class="px-1.5 py-0.5 rounded bg-white border border-[#CBD5E1] text-[#0F172A] font-mono">nomor_unit, blok, nama_pemilik, nomor_wa, status_huni</code>
                    </p>
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1.5">Pilih Berkas CSV / Excel <span class="text-[#E11D48]">*</span></label>
                    <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] bg-white text-xs text-[#0F172A] file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#0F172A] file:text-white hover:file:bg-[#1E293B] cursor-pointer">
                    <span class="text-[10px] text-[#64748B] mt-1 block">Mendukung format .CSV atau berkas teks dipisahkan koma/titik koma (Maks. 5 MB)</span>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#F1F5F9]">
                    <button type="button" @click="importModal = false" class="px-4 py-2.5 rounded-xl border border-[#CBD5E1] text-[#64748B] hover:text-[#0F172A] font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white font-bold text-xs shadow-xs flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-[#60A5FA]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <span>Mulai Impor Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== MODAL EDIT SKEMA TARIF ==================== --}}
    <div x-show="editTarifModal"
         class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 bg-[#0F172A]/60"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95">
        <div class="bg-white rounded-2xl border border-[#CBD5E1] shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8" @click.away="editTarifModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-[#F1F5F9]">
                <div>
                    <h3 class="text-base font-bold text-[#0F172A]">Edit Skema Tarif Progresif</h3>
                    <p class="text-xs text-[#64748B]">Perbarui rentang volume dan tarif per meter kubik</p>
                </div>
                <button @click="editTarifModal = false" class="text-[#94A3B8] hover:text-[#0F172A] p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('admin/master/tarif') }}/' + tarifData.id" method="POST" class="mt-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Label Blok Konsumsi <span class="text-[#E11D48]">*</span></label>
                    <input type="text" name="blok_konsumsi" x-model="tarifData.blok_konsumsi" placeholder="Contoh: 0 - 10 m³" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#0F172A] mb-1">Batas Bawah (m³) <span class="text-[#E11D48]">*</span></label>
                        <input type="number" step="0.1" name="batas_bawah" x-model="tarifData.batas_bawah" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    </div>
                    <div>
                        <label class="block font-bold text-[#0F172A] mb-1">Batas Atas (m³)</label>
                        <input type="number" step="0.1" name="batas_atas" x-model="tarifData.batas_atas" placeholder="Kosongkan jika tak terbatas"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Tarif per m³ (Rp) <span class="text-[#E11D48]">*</span></label>
                    <input type="number" name="tarif_per_m3" x-model="tarifData.tarif_per_m3" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-extrabold text-[#059669] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div class="pt-4 flex items-center justify-between gap-3 border-t border-[#F1F5F9]">
                    <button type="button"
                            @click="if(confirm('Apakah Anda yakin ingin menghapus skema tarif ini?')) { document.getElementById('delete-tarif-form-' + tarifData.id).submit(); }"
                            class="px-4 py-2.5 rounded-xl bg-[#FEF2F2] hover:bg-[#FEE2E2] text-[#DC2626] border border-[#FCA5A5] font-bold text-xs transition-colors cursor-pointer">
                        Hapus Tarif
                    </button>
                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="editTarifModal = false" class="px-4 py-2.5 rounded-xl border border-[#CBD5E1] text-[#64748B] hover:text-[#0F172A] font-bold text-xs">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs cursor-pointer">
                            Simpan Perubahan Tarif
                        </button>
                    </div>
                </div>
            </form>
            {{-- Hidden delete form helper --}}
            <form :id="'delete-tarif-form-' + tarifData.id" :action="'{{ url('admin/master/tarif') }}/' + tarifData.id" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    {{-- ==================== MODAL TAMBAH PETUGAS LAPANGAN ==================== --}}
    <div x-show="addPetugasModal"
         class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 bg-[#0F172A]/60"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95">
        <div class="bg-white rounded-2xl border border-[#CBD5E1] shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8" @click.away="addPetugasModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-[#F1F5F9]">
                <div>
                    <h3 class="text-base font-bold text-[#0F172A]">Tambah Petugas Lapangan Baru</h3>
                    <p class="text-xs text-[#64748B]">Buat akun login dan tentukan zona penugasan blok</p>
                </div>
                <button type="button" @click="addPetugasModal = false" class="text-[#94A3B8] hover:text-[#0F172A] p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.master.petugas.store') }}" method="POST" class="mt-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Nama Lengkap Petugas <span class="text-[#E11D48]">*</span></label>
                    <input type="text" name="name" x-model="petugasData.name" placeholder="Contoh: Budi Santoso" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Username / Email Login <span class="text-[#E11D48]">*</span></label>
                    <input type="text" name="email" x-model="petugasData.email" placeholder="Contoh: petugas2 atau budi@grandsul.web.id" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    <span class="text-[11px] text-[#64748B] mt-1 block">Bisa diisi username (misal: <code>petugas2</code>) atau alamat email lengkap.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Kata Sandi (Password) <span class="text-[#E11D48]">*</span></label>
                    <input type="password" name="password" x-model="petugasData.password" placeholder="Minimal 6 karakter" required minlength="6"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1.5">Zona Penugasan Blok</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-[#CBD5E1] cursor-pointer hover:bg-[#F8FAFC] transition-colors"
                               :class="petugasData.zona_type === 'semua' ? 'bg-[#EFF6FF] border-[#93C5FD]' : ''">
                            <input type="radio" name="zona_type" value="semua" x-model="petugasData.zona_type" class="text-[#2563EB] focus:ring-[#2563EB]">
                            <div>
                                <span class="font-bold text-[#0F172A] block text-xs">Semua Blok (A, B, C, D, E, F)</span>
                                <span class="text-[11px] text-[#64748B]">Petugas dapat mencatat meter di seluruh area perumahan</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-[#CBD5E1] cursor-pointer hover:bg-[#F8FAFC] transition-colors"
                               :class="petugasData.zona_type === 'spesifik' ? 'bg-[#EFF6FF] border-[#93C5FD]' : ''">
                            <input type="radio" name="zona_type" value="spesifik" x-model="petugasData.zona_type" class="text-[#2563EB] focus:ring-[#2563EB]">
                            <div>
                                <span class="font-bold text-[#0F172A] block text-xs">Blok Tertentu (Penugasan Khusus)</span>
                                <span class="text-[11px] text-[#64748B]">Pilih blok spesifik yang menjadi tanggung jawab petugas</span>
                            </div>
                        </label>
                    </div>

                    {{-- Pilihan Blok Spesifik --}}
                    <div x-show="petugasData.zona_type === 'spesifik'" class="mt-3 p-3.5 rounded-xl bg-[#F8FAFC] border border-[#CBD5E1] space-y-2" style="display: none;">
                        <span class="text-[11px] font-bold text-[#475569] uppercase tracking-wider block">Pilih Blok Penugasan:</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($bloks as $b)
                            <button type="button" @click="toggleBlokSelection('{{ $b->nama_blok }}')"
                                    class="px-3 py-2 rounded-lg border text-xs font-bold text-left transition-all flex items-center justify-between cursor-pointer"
                                    :class="petugasData.zona_blok.includes('{{ $b->nama_blok }}') ? 'bg-[#2563EB] text-white border-[#2563EB] shadow-xs' : 'bg-white text-[#0F172A] border-[#CBD5E1] hover:border-[#94A3B8]'">
                                <span>{{ $b->nama_blok }}</span>
                                <span x-show="petugasData.zona_blok.includes('{{ $b->nama_blok }}')" class="text-xs">✓</span>
                            </button>
                            <input type="checkbox" name="zona_blok[]" value="{{ $b->nama_blok }}" :checked="petugasData.zona_blok.includes('{{ $b->nama_blok }}')" class="hidden">
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#F1F5F9]">
                    <button type="button" @click="addPetugasModal = false" class="px-4 py-2.5 rounded-xl border border-[#CBD5E1] text-[#64748B] hover:text-[#0F172A] font-bold text-xs cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs cursor-pointer">
                        Daftarkan Petugas
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== MODAL KELOLA / EDIT PETUGAS ==================== --}}
    <div x-show="editPetugasModal"
         class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 bg-[#0F172A]/60"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95">
        <div class="bg-white rounded-2xl border border-[#CBD5E1] shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8" @click.away="editPetugasModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-[#F1F5F9]">
                <div>
                    <h3 class="text-base font-bold text-[#0F172A]">Kelola Akun & Penugasan Petugas</h3>
                    <p class="text-xs text-[#64748B]">Perbarui nama, username, kata sandi, dan blok tugas</p>
                </div>
                <button type="button" @click="editPetugasModal = false" class="text-[#94A3B8] hover:text-[#0F172A] p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('admin/master/petugas') }}/' + petugasData.id" method="POST" class="mt-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Nama Lengkap Petugas <span class="text-[#E11D48]">*</span></label>
                    <input type="text" name="name" x-model="petugasData.name" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Username / Email Login <span class="text-[#E11D48]">*</span></label>
                    <input type="text" name="email" x-model="petugasData.email" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono font-semibold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    <span class="text-[11px] text-[#64748B] mt-1 block">Bisa berupa username singkat atau email lengkap.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Status Keaktifan Petugas</label>
                    <select name="is_active" x-model="petugasData.is_active" class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-semibold text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        <option :value="true">Aktif Siaga (Bisa Login & Bertugas)</option>
                        <option :value="false">Non-Aktif / Berhenti (Akses Diblokir)</option>
                    </select>
                    <span class="text-[11px] text-[#64748B] mt-1 block">Jika dinonaktifkan, akun tidak dapat digunakan untuk login ke portal petugas.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Kata Sandi Baru (Opsional)</label>
                    <input type="password" name="password" x-model="petugasData.password" placeholder="Kosongkan jika tidak ingin mengubah password" minlength="6"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-mono text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    <span class="text-[11px] text-[#64748B] mt-1 block">Isi hanya jika ingin mengganti kata sandi akun petugas ini.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1.5">Zona Penugasan Blok</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-[#CBD5E1] cursor-pointer hover:bg-[#F8FAFC] transition-colors"
                               :class="petugasData.zona_type === 'semua' ? 'bg-[#EFF6FF] border-[#93C5FD]' : ''">
                            <input type="radio" name="zona_type" value="semua" x-model="petugasData.zona_type" class="text-[#2563EB] focus:ring-[#2563EB]">
                            <div>
                                <span class="font-bold text-[#0F172A] block text-xs">Semua Blok (A, B, C, D, E, F)</span>
                                <span class="text-[11px] text-[#64748B]">Petugas dapat mencatat meter di seluruh area perumahan</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-[#CBD5E1] cursor-pointer hover:bg-[#F8FAFC] transition-colors"
                               :class="petugasData.zona_type === 'spesifik' ? 'bg-[#EFF6FF] border-[#93C5FD]' : ''">
                            <input type="radio" name="zona_type" value="spesifik" x-model="petugasData.zona_type" class="text-[#2563EB] focus:ring-[#2563EB]">
                            <div>
                                <span class="font-bold text-[#0F172A] block text-xs">Blok Tertentu (Penugasan Khusus)</span>
                                <span class="text-[11px] text-[#64748B]">Pilih blok spesifik yang menjadi tanggung jawab petugas</span>
                            </div>
                        </label>
                    </div>

                    {{-- Pilihan Blok Spesifik --}}
                    <div x-show="petugasData.zona_type === 'spesifik'" class="mt-3 p-3.5 rounded-xl bg-[#F8FAFC] border border-[#CBD5E1] space-y-2" style="display: none;">
                        <span class="text-[11px] font-bold text-[#475569] uppercase tracking-wider block">Pilih Blok Penugasan:</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($bloks as $b)
                            <button type="button" @click="toggleBlokSelection('{{ $b->nama_blok }}')"
                                    class="px-3 py-2 rounded-lg border text-xs font-bold text-left transition-all flex items-center justify-between cursor-pointer"
                                    :class="petugasData.zona_blok.includes('{{ $b->nama_blok }}') ? 'bg-[#2563EB] text-white border-[#2563EB] shadow-xs' : 'bg-white text-[#0F172A] border-[#CBD5E1] hover:border-[#94A3B8]'">
                                <span>{{ $b->nama_blok }}</span>
                                <span x-show="petugasData.zona_blok.includes('{{ $b->nama_blok }}')" class="text-xs">✓</span>
                            </button>
                            <input type="checkbox" name="zona_blok[]" value="{{ $b->nama_blok }}" :checked="petugasData.zona_blok.includes('{{ $b->nama_blok }}')" class="hidden">
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-between gap-3 border-t border-[#F1F5F9]">
                    <button type="button"
                            @click="if(confirm('Apakah Anda yakin ingin menghapus petugas ini?')) { document.getElementById('delete-petugas-form-' + petugasData.id).submit(); }"
                            class="px-4 py-2.5 rounded-xl bg-[#FEF2F2] hover:bg-[#FEE2E2] text-[#DC2626] border border-[#FCA5A5] font-bold text-xs transition-colors cursor-pointer">
                        Hapus Petugas
                    </button>
                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="editPetugasModal = false" class="px-4 py-2.5 rounded-xl border border-[#CBD5E1] text-[#64748B] hover:text-[#0F172A] font-bold text-xs cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
            {{-- Hidden delete form helper --}}
            <form :id="'delete-petugas-form-' + petugasData.id" :action="'{{ url('admin/master/petugas') }}/' + petugasData.id" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    {{-- ==================== MODAL TAMBAH BLOK HUNIAN ==================== --}}
    <div x-show="addBlokModal"
         class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 bg-[#0F172A]/60"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95">
        <div class="bg-white rounded-2xl border border-[#CBD5E1] shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8" @click.away="addBlokModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-[#F1F5F9]">
                <div>
                    <h3 class="text-base font-bold text-[#0F172A]">Tambah Blok Hunian Baru</h3>
                    <p class="text-xs text-[#64748B]">Registrasi klaster/blok baru dalam kawasan perumahan</p>
                </div>
                <button type="button" @click="addBlokModal = false" class="text-[#94A3B8] hover:text-[#0F172A] p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.master.blok.store') }}" method="POST" class="mt-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Nama Blok Hunian <span class="text-[#E11D48]">*</span></label>
                    <input type="text" name="nama_blok" placeholder="Contoh: Blok G, Blok Anggrek" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs font-bold text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    <span class="text-[11px] text-[#64748B] mt-1 block">Contoh format: <code>Blok G</code>. Pastikan nama blok belum pernah ada.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#0F172A] mb-1">Keterangan / Deskripsi Wilayah (Opsional)</label>
                    <input type="text" name="keterangan_wilayah" placeholder="Contoh: Sektor Timur tahap perluasan baru"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD5E1] text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#F1F5F9]">
                    <button type="button" @click="addBlokModal = false" class="px-4 py-2.5 rounded-xl border border-[#CBD5E1] text-[#64748B] hover:text-[#0F172A] font-bold text-xs cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs cursor-pointer">
                        Simpan Blok Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
    </template>

</div>
@endsection
