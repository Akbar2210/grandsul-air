@extends('layouts.admin')

@section('title', 'Audit Log Aktivitas — Grand Sulawesi Air')
@section('page-title', 'Audit Log Sistem')
@section('page-subtitle', 'Pencatatan riwayat aktivitas pengguna yang bersifat kekal (immutable ledger) untuk keamanan data')

@section('content')
<div class="space-y-6">

    {{-- ==================== BANNER KEAMANAN & FILTER TOOLBAR ==================== --}}
    <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            {{-- Status Keamanan --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#0F172A] text-[#00F2FE] flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-[#0F172A]">Audit Trail Terproteksi</span>
                        <span class="px-2 py-0.5 rounded-full bg-[#ECFDF5] text-[#059669] font-bold text-[10px] uppercase tracking-wider">
                            ● Read-Only Immutable
                        </span>
                    </div>
                    <p class="text-xs text-[#64748B] mt-0.5">Seluruh modifikasi data, kalkulasi tagihan, dan pembayaran dicatat otomatis dengan stempel waktu terverifikasi</p>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.audit') }}" class="flex flex-wrap items-center gap-2.5">
                <div class="relative min-w-[240px]">
                    <svg class="w-4 h-4 text-[#94A3B8] absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" name="aktivitas" value="{{ request('aktivitas') }}" placeholder="Cari aktivitas atau unit..."
                           class="w-full pl-10 pr-3.5 py-2 rounded-xl border border-[#CBD5E1] text-xs font-medium text-[#0F172A] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <button type="submit" class="px-4 py-2 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white font-bold text-xs transition-colors cursor-pointer">
                    Cari Log
                </button>
                @if(request('aktivitas'))
                <a href="{{ route('admin.audit') }}" class="px-3 py-2 rounded-xl border border-[#CBD5E1] text-[#64748B] hover:text-[#0F172A] text-xs font-semibold">
                    Reset
                </a>
                @endif
            </form>
        </div>
    </div>

    {{-- ==================== TABEL AUDIT LOG AKTIVITAS ==================== --}}
    <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-[#F1F5F9] flex items-center justify-between">
            <h3 class="text-xs font-bold text-[#64748B] uppercase tracking-wider">JURNAL AKTIVITAS OPERASIONAL</h3>
            <span class="text-xs text-[#64748B] font-mono">
                Total: {{ $logs->total() }} Catatan Aktivitas
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] font-bold uppercase tracking-wider text-[11px]">
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
                        {{-- Timestamp --}}
                        <td class="py-3.5 px-6 whitespace-nowrap">
                            <span class="font-bold text-[#0F172A] tabular-nums block">
                                {{ \Carbon\Carbon::parse($log->timestamp)->translatedFormat('d M Y, H:i:s') }}
                            </span>
                            <span class="text-[11px] text-[#64748B]">
                                {{ \Carbon\Carbon::parse($log->timestamp)->diffForHumans() }}
                            </span>
                        </td>

                        {{-- Pengguna --}}
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-[#0F172A] text-[#00F2FE] font-bold text-[10px] flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($log->user->name ?? 'AU', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-[#0F172A] block truncate">{{ $log->user->name ?? 'Admin Utama' }}</span>
                                    <span class="text-[10px] text-[#64748B] font-mono block">{{ $log->user->email ?? 'admin@grandsulawesi.com' }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Role Badge --}}
                        <td class="py-3.5 px-4 text-center">
                            @if($roleName === 'Petugas')
                            <span class="px-2.5 py-0.5 rounded-full bg-[#EFF6FF] text-[#1D4ED8] font-bold text-[10px] border border-[#BFDBFE]">
                                Petugas
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full bg-[#0F172A] text-white font-bold text-[10px]">
                                Administrator
                            </span>
                            @endif
                        </td>

                        {{-- IP Address --}}
                        <td class="py-3.5 px-4 font-mono text-[#64748B] text-[11px] whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-md bg-[#F1F5F9] border border-[#E2E8F0]">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </span>
                        </td>

                        {{-- Aktivitas --}}
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

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="p-4 border-t border-[#F1F5F9] bg-[#F8FAFC]">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

