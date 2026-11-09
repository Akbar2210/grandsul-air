<x-guest-layout>
    <div class="min-h-screen w-full flex flex-col lg:flex-row bg-[#F1F5F9]" x-data="{ showPassword: false }">

        {{-- ==================== SISI KIRI: 30% SEKUNDER (NAVY DEEP BLUE #0B132B + AKSEN CYAN TIPIS) ==================== --}}
        <div
            class="hidden lg:flex lg:w-1/2 min-h-screen relative flex-col items-center justify-center p-12 xl:p-16 overflow-hidden select-none border-r border-[#1C2849]"
            style="background: radial-gradient(80% 60% at 20% 10%, rgba(0, 242, 254, 0.16) 0%, rgba(0, 242, 254, 0) 60%), radial-gradient(70% 60% at 90% 100%, rgba(0, 242, 254, 0.10) 0%, rgba(0, 242, 254, 0) 60%), #0B132B;"
        >
            {{-- Foto Perumahan Grand Sulawesi (Samar-samar di Belakang Logo Sesuai Permintaan) --}}
            <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
                <img
                    src="{{ asset('images/bg-perumahan.jpg') }}"
                    alt="Perumahan Grand Sulawesi"
                    class="w-full h-full object-cover object-center opacity-20 filter brightness-75 contrast-105"
                />
                {{-- Lapisan Gradien Navy agar foto menyatu halus dengan tema #0B132B dan tidak terlalu kentara --}}
                <div class="absolute inset-0 bg-gradient-to-b from-[#0B132B]/85 via-[#0B132B]/60 to-[#0B132B]/90"></div>
            </div>

            {{-- Detail Grafis Tipis Aksen Cyan (#00F2FE) Sesuai Panduan --}}
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-[#00F2FE]/10 rounded-full blur-3xl pointer-events-none z-1"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-[#00F2FE]/8 rounded-full blur-3xl pointer-events-none z-1"></div>

            {{-- Logo Grand Sulawesi Group Besar --}}
            <div class="w-full max-w-lg xl:max-w-xl flex items-center justify-center p-6 relative z-10 transition-transform duration-500 hover:scale-[1.02]">
                <x-login-logo-white class="w-full h-auto max-h-[74vh] object-contain drop-shadow-[0_20px_40px_rgba(0,0,0,0.6)]" />
            </div>
        </div>

        {{-- ==================== SISI KANAN: 60% DOMINAN (ICE WHITE #F1F5F9 DENGAN KARTU PUTIH #FFFFFF) ==================== --}}
        <div class="w-full lg:w-1/2 min-h-screen bg-[#F1F5F9] flex flex-col justify-center items-center px-6 py-10 sm:px-12 lg:px-16 xl:px-20">

            {{-- TAMPILAN MOBILE: Logo Grand Sulawesi di Atas --}}
            <div class="lg:hidden flex flex-col items-center mb-6">
                <div class="w-48 sm:w-56 mb-2">
                    <x-login-logo-dark class="w-full h-auto object-contain" />
                </div>
            </div>

            {{-- KARTU FORM LOGIN (PUTIH BERSIH #FFFFFF DI ATAS KANVAS ICE WHITE #F1F5F9) --}}
            <div class="w-full max-w-[420px] bg-white border border-[#CBD5E1] rounded-xl p-6 sm:p-9 shadow-sm">

                {{-- Header Form (Teks Judul Navy #0B132B) --}}
                <div class="text-left mb-6 sm:mb-7">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#0B132B]">
                        Login
                    </h1>
                    <p class="text-xs sm:text-sm text-[#5B6785] mt-1.5 font-medium">
                        Masuk ke sistem pengelolaan air
                    </p>
                </div>

                {{-- Alert Error Global --}}
                @if (isset($errors) && $errors->any())
                    <div class="mb-5 p-3.5 rounded-lg text-xs bg-[#FEE2E2] text-[#DC2626] border border-[#DC2626]/20" role="alert">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                {{-- Session Status --}}
                <x-auth-session-status class="mb-5" :status="session('status')" />

                {{-- Form Login Tunggal (Admin & Petugas) --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5">
                    @csrf

                    {{-- Username / Email (Label Navy #0B132B, Border Normal #CBD5E1, Fokus Aksen Cyan #00F2FE) --}}
                    <div>
                        <label for="email" class="block text-xs sm:text-sm font-semibold text-[#0B132B] mb-1.5">
                            Username / Email
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-[#5B6785]">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            </span>
                            <input
                                id="email"
                                type="text"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full h-11 sm:h-12 pl-10 sm:pl-11 pr-4 text-sm bg-white border border-[#CBD5E1] rounded-lg text-[#0B132B] placeholder-[#5B6785] focus:outline-none focus:border-[#00F2FE] focus:ring-2 focus:ring-[#00F2FE]/30 transition-all {{ (isset($errors) && $errors->has('email')) ? '!border-[#DC2626]' : '' }}"
                                placeholder="Masukkan username atau email"
                            />
                        </div>
                    </div>

                    {{-- Password (Label Navy #0B132B, Border Normal #CBD5E1, Fokus Aksen Cyan #00F2FE) --}}
                    <div>
                        <label for="password" class="block text-xs sm:text-sm font-semibold text-[#0B132B] mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-[#5B6785]">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                            </span>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="current-password"
                                class="w-full h-11 sm:h-12 pl-10 sm:pl-11 pr-10 text-sm bg-white border border-[#CBD5E1] rounded-lg text-[#0B132B] placeholder-[#5B6785] focus:outline-none focus:border-[#00F2FE] focus:ring-2 focus:ring-[#00F2FE]/30 transition-all {{ (isset($errors) && $errors->has('password')) ? '!border-[#DC2626]' : '' }}"
                                placeholder="Masukkan password"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-[#5B6785] hover:text-[#0B132B] transition-colors"
                                tabindex="-1"
                                aria-label="Toggle password visibility"
                            >
                                <svg x-show="!showPassword" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <svg x-show="showPassword" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="remember"
                                class="w-4 h-4 rounded border-[#CBD5E1] text-[#00C4CE] focus:ring-[#00F2FE]"
                            />
                            <span class="text-xs sm:text-sm text-[#5B6785]">Ingat saya</span>
                        </label>
                    </div>

                    {{-- Tombol Masuk: 10% Aksen (Neon Cyan #00F2FE, Teks Navy #0B132B Wajib) --}}
                    <button
                        type="submit"
                        class="w-full h-11 sm:h-12 rounded-lg bg-[#00F2FE] hover:bg-[#00C4CE] active:scale-[0.99] text-[#0B132B] text-sm sm:text-base font-bold shadow-md shadow-[#00F2FE]/25 transition-all flex items-center justify-center gap-2 mt-2"
                    >
                        <span>Masuk</span>
                    </button>
                </form>

            </div>

        </div>

    </div>
</x-guest-layout>
