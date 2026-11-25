<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Nota — Pengelolaan Air Terintegrasi Grand Sulawesi</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Poppins', sans-serif !important; }</style>
</head>
<body class="bg-[#0B132B] min-h-screen flex justify-center antialiased">
    <div class="w-full max-w-[430px] min-h-screen bg-[#F1F5F9] p-4 flex flex-col gap-4">

        <header class="bg-[#0F172A] text-white rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#1E293B] p-1.5 shrink-0">
                <x-brand-logo class="w-full h-full object-contain" />
            </div>
            <div>
                <p class="text-xs font-extrabold tracking-wide">GRAND SULAWESI AIR</p>
                <p class="text-[10px] text-[#00F2FE] font-semibold">Verifikasi Keaslian Nota Digital</p>
            </div>
        </header>

        @php $ok = $result['is_authentic']; @endphp

        <div class="rounded-2xl p-5 text-center border-2 {{ $ok ? 'bg-[#DCFCE7] border-[#16A34A]' : 'bg-[#FEE2E2] border-[#DC2626]' }}">
            <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center {{ $ok ? 'bg-[#16A34A]' : 'bg-[#DC2626]' }}">
                @if($ok)
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                @else
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                @endif
            </div>
            <h1 class="mt-3 text-base font-extrabold {{ $ok ? 'text-[#166534]' : 'text-[#991B1B]' }}">{{ $result['status_label'] }}</h1>
            <p class="text-xs mt-1 {{ $ok ? 'text-[#166534]' : 'text-[#991B1B]' }}">{{ $result['reason'] }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#CBD5E1] p-4 space-y-2 text-xs">
            <p class="font-bold text-[#0F172A] mb-1">Hasil Pemeriksaan Kriptografi</p>
            @foreach([
                'terdaftar_di_sistem' => 'Nota terdaftar di sistem',
                'tanda_tangan_cocok' => 'Tanda tangan RSA-2048 cocok',
                'integritas_hash_cocok' => 'Integritas hash SHA-256 cocok',
            ] as $key => $label)
                <div class="flex items-center justify-between">
                    <span class="text-[#475569]">{{ $label }}</span>
                    <span class="font-bold {{ $result['checks'][$key] ? 'text-[#16A34A]' : 'text-[#DC2626]' }}">
                        {{ $result['checks'][$key] ? '✓ Ya' : '✕ Tidak' }}
                    </span>
                </div>
            @endforeach
        </div>

        @if($result['data_nota'])
            @php $d = $result['data_nota']; @endphp
            <div class="bg-white rounded-2xl border border-[#CBD5E1] p-4 space-y-2 text-xs">
                <p class="font-bold text-[#0F172A] mb-1">Detail Nota</p>
                <div class="flex justify-between"><span class="text-[#64748B]">Nomor Nota</span><span class="font-bold font-mono">{{ $d['nomor_nota'] }}</span></div>
                <div class="flex justify-between"><span class="text-[#64748B]">Unit / Penghuni</span><span class="font-bold">{{ $d['unit_penghuni'] }}</span></div>
                <div class="flex justify-between"><span class="text-[#64748B]">Periode</span><span class="font-bold">{{ $d['periode'] }}</span></div>
                <div class="flex justify-between"><span class="text-[#64748B]">Volume</span><span class="font-bold">{{ $d['volume_m3'] }} m³</span></div>
                <div class="flex justify-between"><span class="text-[#64748B]">Total Bayar</span><span class="font-extrabold">Rp {{ number_format($d['total_bayar'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-[#64748B]">Algoritma</span><span class="font-bold">{{ $d['algoritma'] }}</span></div>
                <div class="flex justify-between"><span class="text-[#64748B]">Sidik Jari</span><span class="font-mono">{{ $d['fingerprint_sha256'] }}</span></div>
            </div>
        @endif

        <p class="text-center text-[10px] text-[#94A3B8] mt-auto">Diverifikasi otomatis oleh server pengelola • {{ now()->translatedFormat('d M Y H:i') }}</p>
    </div>
</body>
</html>

