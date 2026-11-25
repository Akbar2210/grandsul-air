@extends('layouts.admin')

@section('title', 'Audit Log Aktivitas — Pengelolaan Air Terintegrasi Grand Sulawesi')
@section('page-title', 'Audit Log Sistem')
@section('page-subtitle', 'Pencatatan riwayat aktivitas pengguna yang bersifat kekal (immutable ledger) untuk keamanan data')

@section('content')
<div class="space-y-5">

    {{-- ==================== BANNER KEAMANAN & FILTER TOOLBAR ==================== --}}
    <div class="bg-white rounded-xl border border-[#CBD5E1] p-5 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-[#0F172A]">Audit Trail Terproteksi</span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-[#F8FAFC] border border-[#CBD5E1] text-[#0F172A] font-semibold text-[10px] uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>
                        <span>Read-Only Immutable</span>
                    </span>
                </div>
                <p class="text-xs text-[#64748B] mt-0.5">Seluruh modifikasi data, kalkulasi tagihan, dan pembayaran dicatat otomatis dengan stempel waktu terverifikasi</p>
            </div>
        </div>

        @if(! empty($dateError))
            <div role="alert" class="px-4 py-3 rounded-xl bg-[#FEF2F2] border border-[#FCA5A5] text-xs font-semibold text-[#B91C1C] flex items-center gap-2.5">
                <svg class="w-4 h-4 shrink-0 text-[#DC2626]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                <span>{{ $dateError }}</span>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.audit') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-2">
                <label for="tanggal_awal" class="block text-[11px] font-bold text-[#334155] mb-1">Tanggal Awal</label>
                <input id="tanggal_awal" type="date" name="tanggal_awal" value="{{ $tanggalAwal ?? '' }}"
                       class="w-full px-3 py-2 rounded-xl border border-[#CBD5E1] text-xs font-medium text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
            </div>

            <div class="lg:col-span-2">
                <label for="tanggal_akhir" class="block text-[11px] font-bold text-[#334155] mb-1">Tanggal Akhir</label>
                <input id="tanggal_akhir" type="date" name="tanggal_akhir" value="{{ $tanggalAkhir ?? '' }}"
                       class="w-full px-3 py-2 rounded-xl border border-[#CBD5E1] text-xs font-medium text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
            </div>

            <div class="lg:col-span-3">
                <label for="user_id" class="block text-[11px] font-bold text-[#334155] mb-1">User</label>
                <select id="user_id" name="user_id"
                        class="w-full px-3 py-2 rounded-xl border border-[#CBD5E1] text-xs font-medium text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    <option value="">Semua User</option>
                    @foreach($users ?? [] as $u)
                        @php $uRole = $u->hasRole('petugas') ? 'Petugas' : 'Admin'; @endphp
                        <option value="{{ $u->id }}" {{ (string) ($selectedUserId ?? '') === (string) $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $uRole }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label for="jenis_aktivitas" class="block text-[11px] font-bold text-[#334155] mb-1">Aktivitas</label>
                <select id="jenis_aktivitas" name="jenis_aktivitas"
                        class="w-full px-3 py-2 rounded-xl border border-[#CBD5E1] text-xs font-medium text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    <option value="">Semua Aktivitas</option>
                    @foreach($activityOptions ?? [] as $opt)
                        <option value="{{ $opt }}" {{ ($selectedJenisAktivitas ?? '') === $opt ? 'selected' : '' }}>
                            {{ $opt }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white font-bold text-xs transition-colors cursor-pointer">
                    Filter
                </button>
                @if(($tanggalAwal ?? '') !== '' || ($tanggalAkhir ?? '') !== '' || ($selectedUserId ?? '') !== '' || ($selectedJenisAktivitas ?? '') !== '' || ($keyword ?? '') !== '')
                    <a href="{{ route('admin.audit') }}" class="px-3 py-2 rounded-xl border border-[#CBD5E1] text-[#64748B] hover:text-[#0F172A] text-xs font-semibold">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ==================== TABEL AUDIT LOG AKTIVITAS ==================== --}}
    <div class="bg-white rounded-xl border border-[#CBD5E1] shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[#F1F5F9] flex items-center justify-between">
            <h3 class="text-xs font-bold text-[#0F172A] uppercase tracking-wider">JURNAL AKTIVITAS OPERASIONAL</h3>
            <span class="text-xs text-[#64748B] font-mono tabular-nums">
                Total: {{ $logs->total() }} Catatan Aktivitas
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-[#CBD5E1] text-[#0F172A] font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6">Stempel Waktu</th>
                        <th class="py-3.5 px-4">Pengguna / Aktor</th>
                        <th class="py-3.5 px-4 text-center">Peran</th>
                        <th class="py-3.5 px-4 font-mono">Alamat IP</th>
                        <th class="py-3.5 px-6">Deskripsi Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9]">
                    @forelse($logs as $log)
                    @php
                        $roleName = $log->user && $log->user->hasRole('petugas') ? 'Petugas' : 'Admin';
                    @endphp
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="py-3.5 px-6 whitespace-nowrap">
                            <span class="font-bold text-[#0F172A] font-mono tabular-nums block">
                                {{ \Carbon\Carbon::parse($log->timestamp)->translatedFormat('d M Y, H:i:s') }}
                            </span>
                            <span class="text-[11px] text-[#64748B]">
                                {{ \Carbon\Carbon::parse($log->timestamp)->diffForHumans() }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-[#0F172A] text-white font-mono font-bold text-[10px] flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($log->user->name ?? 'AU', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-[#0F172A] block truncate">{{ $log->user->name ?? 'Admin Utama' }}</span>
                                    <span class="text-[10px] text-[#64748B] font-mono block">{{ $log->user->email ?? 'admin@grandsulawesi.com' }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            @if($roleName === 'Petugas')
                            <span class="px-2.5 py-0.5 rounded-md bg-[#F8FAFC] text-[#334155] font-semibold text-[11px] border border-[#CBD5E1]">
                                Petugas
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-md bg-[#0F172A] text-white font-semibold text-[11px]">
                                Administrator
                            </span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 font-mono text-[#64748B] text-[11px] whitespace-nowrap tabular-nums">
                            <span class="px-2 py-0.5 rounded-md bg-[#F8FAFC] border border-[#CBD5E1]">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </span>
                        </td>

                        <td class="py-3.5 px-6 text-[#0F172A]">
                            <p class="leading-relaxed font-medium">
                                {{ $log->aktivitas }}
                            </p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-[#64748B]">
                            Belum ada catatan aktivitas dalam audit log.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-[#F1F5F9] bg-[#F8FAFC]">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
