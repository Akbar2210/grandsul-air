@if ($paginator->hasPages())
    <div class="py-3 px-1 w-full max-w-full">
        {{-- Ringkasan Jumlah Data --}}
        <div class="text-center text-[11px] text-[#64748B] font-medium mb-2.5">
            Menampilkan <span class="font-bold font-mono text-[#0F172A]">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span> dari <span class="font-bold font-mono text-[#0F172A]">{{ $paginator->total() }}</span> unit
        </div>

        {{-- Kontrol Navigasi Halaman Mobile Friendly --}}
        <div class="flex items-center justify-between gap-1 w-full">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="h-9 px-2.5 sm:px-3 rounded-xl bg-white/60 text-[#94A3B8] border border-[#E2E8F0] text-xs font-semibold flex items-center gap-1 cursor-not-allowed select-none shadow-2xs shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    <span>Prev</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="h-9 px-2.5 sm:px-3 rounded-xl bg-white hover:bg-[#F8FAFC] active:scale-95 text-[#0F172A] border border-[#CBD5E1] text-xs font-semibold flex items-center gap-1 transition-all shadow-xs shrink-0">
                    <svg class="w-4 h-4 text-[#2563EB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    <span>Prev</span>
                </a>
            @endif

            {{-- Angka Halaman Ringkas (Maksimal 3-5 Pill Pas di Mobile) --}}
            <div class="flex items-center gap-1 justify-center shrink min-w-0">
                @php
                    $currentPage = $paginator->currentPage();
                    $lastPage = $paginator->lastPage();
                    $start = max(1, $currentPage - 1);
                    $end = min($lastPage, $currentPage + 1);
                    if ($currentPage <= 2) {
                        $start = 1;
                        $end = min($lastPage, 3);
                    }
                    if ($currentPage >= $lastPage - 1) {
                        $start = max(1, $lastPage - 2);
                        $end = $lastPage;
                    }
                @endphp

                @if ($start > 1)
                    <a href="{{ $paginator->url(1) }}" class="w-8 h-9 rounded-xl bg-white border border-[#CBD5E1] hover:bg-[#F8FAFC] text-xs font-bold font-mono text-[#0F172A] flex items-center justify-center transition-all shadow-xs shrink-0">
                        1
                    </a>
                    @if ($start > 2)
                        <span class="w-4 text-center text-[#94A3B8] text-xs font-bold select-none">…</span>
                    @endif
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $currentPage)
                        <span class="w-8 h-9 rounded-xl bg-[#2563EB] text-white text-xs font-bold font-mono flex items-center justify-center shadow-xs shrink-0">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="w-8 h-9 rounded-xl bg-white border border-[#CBD5E1] hover:bg-[#F8FAFC] text-xs font-bold font-mono text-[#0F172A] flex items-center justify-center transition-all shadow-xs shrink-0">
                            {{ $page }}
                        </a>
                    @endif
                @endfor

                @if ($end < $lastPage)
                    @if ($end < $lastPage - 1)
                        <span class="w-4 text-center text-[#94A3B8] text-xs font-bold select-none">…</span>
                    @endif
                    <a href="{{ $paginator->url($lastPage) }}" class="w-8 h-9 rounded-xl bg-white border border-[#CBD5E1] hover:bg-[#F8FAFC] text-xs font-bold font-mono text-[#0F172A] flex items-center justify-center transition-all shadow-xs shrink-0">
                        {{ $lastPage }}
                    </a>
                @endif
            </div>

            {{-- Tombol Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="h-9 px-2.5 sm:px-3 rounded-xl bg-white hover:bg-[#F8FAFC] active:scale-95 text-[#0F172A] border border-[#CBD5E1] text-xs font-semibold flex items-center gap-1 transition-all shadow-xs shrink-0">
                    <span>Next</span>
                    <svg class="w-4 h-4 text-[#2563EB]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            @else
                <span class="h-9 px-2.5 sm:px-3 rounded-xl bg-white/60 text-[#94A3B8] border border-[#E2E8F0] text-xs font-semibold flex items-center gap-1 cursor-not-allowed select-none shadow-2xs shrink-0">
                    <span>Next</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </span>
            @endif
        </div>
    </div>
@endif
