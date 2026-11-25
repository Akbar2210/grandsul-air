<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Riwayat Pencatatan — Pengelolaan Air Terintegrasi Grand Sulawesi</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Poppins', sans-serif !important; }
    </style>
</head>
<body class="bg-[#E2E8F0] text-[#0F172A] antialiased min-h-screen flex justify-center petugas-app">

    <div class="w-full max-w-[430px] min-h-screen bg-[#F1F5F9] flex flex-col border-x border-[#CBD5E1]">

        @include('petugas.partials.topbar', ['title' => 'Riwayat Pencatatan', 'active' => 'riwayat'])

        <main class="flex-1 px-4 py-4 space-y-4">

            {{-- FILTER --}}
            <form method="GET" action="{{ route('petugas.riwayat') }}" class="flex items-end gap-2">
                <div class="flex-1 min-w-0">
                    <label class="text-[10px] font-medium text-[#64748B] block mb-1">Bulan</label>
                    <select name="bulan" class="w-full h-10 px-2.5 border border-[#CBD5E1] rounded-xl text-xs text-[#0F172A] bg-white focus:outline-none focus:border-black focus:ring-1 focus:ring-black shadow-xs">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $filterBulan == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="flex-1 min-w-0">
                    <label class="text-[10px] font-medium text-[#64748B] block mb-1">Status</label>
                    <select name="status" class="w-full h-10 px-2.5 border border-[#CBD5E1] rounded-xl text-xs text-[#0F172A] bg-white focus:outline-none focus:border-black focus:ring-1 focus:ring-black shadow-xs">
                        <option value="semua" {{ $filterStatus === 'semua' ? 'selected' : '' }}>Semua</option>
                        <option value="valid" {{ $filterStatus === 'valid' ? 'selected' : '' }}>Valid</option>
                        <option value="ter_flag" {{ $filterStatus === 'ter_flag' ? 'selected' : '' }}>Ter-flag</option>
                        <option value="ditolak" {{ $filterStatus === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <input type="hidden" name="tahun" value="{{ $filterTahun }}">
                <button type="submit" class="h-10 px-4 bg-[#0F172A] hover:bg-[#1E293B] active:scale-[0.98] text-white text-xs font-semibold rounded-xl shadow-xs transition-all shrink-0 cursor-pointer">
                    Terapkan
                </button>
            </form>

            {{-- DAFTAR RIWAYAT --}}
            <section>
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xs font-semibold text-[#0F172A]">{{ $riwayat->total() }} pencatatan</h2>
                    <a href="{{ route('petugas.pindai') }}" class="text-[11px] font-semibold text-[#2563EB] hover:underline">Pindai baru</a>
                </div>

                <div class="space-y-2.5">
                    @forelse ($riwayat as $r)
                        @php
                            $pemakaian = $r->angka_meter - $r->angka_meter_bulanlalu;
                            $statusMeta = match($r->status_verifikasi) {
                                'valid' => ['label' => 'Valid', 'dot' => 'bg-[#16A34A]'],
                                'ter_flag' => ['label' => 'Menunggu verifikasi', 'dot' => 'bg-[#D97706]'],
                                'ditolak' => ['label' => 'Ditolak', 'dot' => 'bg-[#DC2626]'],
                                default => ['label' => 'Belum dicatat', 'dot' => 'bg-[#94A3B8]'],
                            };
                        @endphp
                        <article class="bg-white border border-[#CBD5E1] rounded-xl shadow-xs overflow-hidden">
                            <div class="px-3.5 pt-3 pb-2.5 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="font-mono text-[11px] font-semibold bg-[#F1F5F9] text-[#0F172A] border border-[#CBD5E1] rounded-md px-2 py-0.5 whitespace-nowrap shrink-0">{{ optional($r->unit)->nomor_rumah ?? '—' }}</span>
                                        <span class="text-[13px] font-medium text-[#0F172A] truncate">{{ optional($r->unit)->nama_pemilik ?? '—' }}</span>
                                    </div>
                                    <p class="text-[11px] text-[#64748B] font-mono mt-1">{{ $r->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 text-[11px] text-[#334155] whitespace-nowrap shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusMeta['dot'] }}"></span>
                                    {{ $statusMeta['label'] }}
                                </span>
                            </div>

                            <div class="grid grid-cols-3 border-t border-[#F1F5F9] text-center bg-[#FAFCFF]/50">
                                <div class="py-2">
                                    <span class="text-[10px] text-[#64748B] block">Lalu</span>
                                    <span class="text-xs font-semibold font-mono text-[#0F172A]">{{ number_format($r->angka_meter_bulanlalu, 0, ',', '.') }}</span>
                                </div>
                                <div class="py-2 border-x border-[#F1F5F9]">
                                    <span class="text-[10px] text-[#64748B] block">Kini</span>
                                    <span class="text-xs font-semibold font-mono text-[#0F172A]">{{ number_format($r->angka_meter, 0, ',', '.') }}</span>
                                </div>
                                <div class="py-2">
                                    <span class="text-[10px] text-[#64748B] block">Pemakaian</span>
                                    <span class="text-xs font-semibold font-mono {{ $pemakaian < 0 ? 'text-[#DC2626]' : 'text-[#0F172A]' }}">
                                        {{ $pemakaian < 0 ? '' : '+' }}{{ number_format($pemakaian, 0, ',', '.') }} m³
                                    </span>
                                </div>
                            </div>

                            @if($r->catatan_anomali)
                                <p class="px-3.5 py-2 border-t border-[#F1F5F9] text-[11px] text-[#475569] leading-snug">
                                    {{ $r->catatan_anomali }}
                                </p>
                            @endif
                        </article>
                    @empty
                        <div class="bg-white border border-[#CBD5E1] rounded-xl shadow-xs p-8 text-center text-xs text-[#64748B]">
                            Belum ada data pencatatan untuk filter ini.
                        </div>
                    @endforelse
                </div>

                @if ($riwayat->hasPages())
                    <div class="pt-3">
                        {{ $riwayat->appends(request()->query())->links('partials.petugas-pagination') }}
                    </div>
                @endif
            </section>

        </main>
    </div>

</body>
</html>
