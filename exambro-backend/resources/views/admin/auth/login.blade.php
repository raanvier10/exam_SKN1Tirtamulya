<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Guru &amp; Admin — ExaSatrya</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 14px;
            line-height: 1.55;
            background-color: #FFFFFF;
            color: #0F172A;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        h1, h2, h3 {
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .flex > *, .grid > * {
            min-width: 0;
        }
        .card {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        svg[stroke="currentColor"]:not([data-decorative]) {
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }
        input[type="password"]::-webkit-credentials-auto-fill-button {
            visibility: hidden !important;
            position: absolute !important;
            right: 0 !important;
        }
    </style>
</head>
<body class="min-h-[100svh] lg:h-[100dvh] lg:overflow-hidden antialiased flex flex-col bg-white overflow-x-hidden selection:bg-[#142C5C] selection:text-white">

    <!-- Top Navbar -->
    <header class="border-b border-slate-200 bg-white sticky top-0 z-30 shrink-0">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-8 h-14 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('images/logo.webp') }}" alt="Logo SMKN 1 Tirtamulya" class="w-8 h-8 object-contain shrink-0">
                <span class="text-[18px] sm:text-[20px] font-extrabold text-[#142C5C] tracking-tight leading-none">ExaSatrya</span>
            </div>

            <div class="flex items-center gap-3.5 shrink-0">
                <div class="text-right min-w-0">
                    <div class="text-[12.5px] font-bold text-[#142C5C] leading-tight">Panel Guru &amp; Admin</div>
                    <div class="text-[11px] text-slate-500 font-medium leading-tight truncate">SMKN 1 Tirtamulya</div>
                </div>
            </div>
        </div>
    </header>

    @php
        $adminHighlights = [
            [
                'title' => 'Manajemen Jadwal Ujian',
                'desc' => 'Atur waktu pelaksanaan, durasi, kelas peserta, dan tautan ujian secara terpusat.',
                'icon' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
            ],
            [
                'title' => 'Pemantauan Real-Time',
                'desc' => 'Awasi status peserta, pelanggaran, serta buka kunci sesi ujian dari satu panel.',
                'icon' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
            ],
            [
                'title' => 'Akses Berbasis Peran',
                'desc' => 'Terpisah aman untuk Administrator Kurikulum, PIC Jurusan, dan Guru Pengampu.',
                'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>',
            ],
        ];
    @endphp

    <!-- Main Content -->
    <main class="max-w-[1040px] mx-auto px-4 sm:px-8 py-2 sm:py-4 lg:overflow-y-auto w-full flex-1 flex flex-col justify-center">
        <!-- Hero -->
        <div class="text-center mb-3.5 sm:mb-5">
            <div class="text-[11px] sm:text-[13px] font-bold uppercase tracking-[0.16em] text-[#142C5C]/75 mb-1">
                Panel Guru &amp; Administrator
            </div>
            <h1 class="text-[32px] sm:text-[44px] font-extrabold text-[#142C5C] tracking-tight leading-[1.04] mb-1.5 sm:mb-2">
                ExaSatrya
            </h1>
            <p class="text-[13px] sm:text-[15px] text-slate-500 max-w-lg mx-auto leading-snug sm:leading-relaxed">
                Kelola jadwal asesmen, data peserta, dan pemantauan integritas ujian sekolah secara terpusat.
            </p>
            <div class="w-10 sm:w-12 h-[3px] sm:h-[3.5px] bg-[#142C5C] rounded-full mx-auto mt-2.5 sm:mt-3"></div>
        </div>

        <div class="flex flex-col lg:flex-row items-center justify-center gap-6 lg:gap-9 max-w-[860px] mx-auto w-full">
            <!-- Left Highlights (Desktop only; accessible via floating button on mobile) -->
            <div class="hidden lg:block w-full lg:w-[360px] shrink-0 space-y-5">
                @foreach($adminHighlights as $item)
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-[10px] bg-slate-100 border border-slate-200/80 text-[#142C5C] flex items-center justify-center shrink-0">
                            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $item['icon'] !!}</svg>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-[14.5px] font-bold text-[#142C5C] tracking-[-0.01em]">{{ $item['title'] }}</h2>
                            <p class="text-[12.5px] text-slate-500 mt-0.5 leading-snug">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Centered Vertical Divider -->
            <div class="hidden lg:block w-px self-stretch my-4 bg-slate-200/90 shrink-0" aria-hidden="true"></div>

            <!-- Right Login Form Card -->
            <div class="w-full lg:w-[390px] shrink-0">
                <div class="card p-5 sm:p-6 max-w-[420px] lg:max-w-none w-full mx-auto">
                    <div class="mb-3.5 sm:mb-4">
                        <h2 class="text-[17px] sm:text-[18px] font-extrabold text-[#142C5C] tracking-tight leading-snug">Masuk Guru &amp; Admin</h2>
                        <p class="text-[12px] sm:text-[12.5px] text-slate-500 mt-0.5">
                            Gunakan Username / NIP, password, dan kode verifikasi.
                        </p>
                    </div>

                    @if($errors->any())
                        <div class="mb-3.5 p-3 rounded-[8px] bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-start gap-2">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <div>
                                @foreach($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-2.5 sm:space-y-3">
                        @csrf
                        <div>
                            <label for="username" class="block text-xs font-bold text-[#142C5C] mb-1">Username / NIP</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </span>
                                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                                       placeholder="Masukkan Username / NIP"
                                       class="w-full h-10 pl-10 pr-3.5 rounded-[8px] border border-slate-200 bg-slate-50/70 focus:bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="password" class="block text-xs font-bold text-[#142C5C]">Password</label>
                                <button type="button" onclick="showForgotPasswordModal()" class="text-[11.5px] font-semibold text-[#142C5C] hover:underline cursor-pointer">
                                    Lupa Password?
                                </button>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </span>
                                <input type="password" id="password" name="password" required autocomplete="current-password"
                                       placeholder="Masukkan password"
                                       class="w-full h-10 pl-10 pr-11 rounded-[8px] border border-slate-200 bg-slate-50/70 focus:bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10">
                                <button type="button" onclick="togglePassword('password', 'password_icon')" class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer" title="Lihat/Sembunyikan Password" aria-label="Lihat atau sembunyikan password">
                                    <svg id="password_icon" class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="captcha" class="block text-xs font-bold text-[#142C5C] mb-1">Kode Verifikasi</label>
                            <div class="flex items-stretch gap-2 mb-2">
                                <div class="flex-1 h-10 rounded-[8px] border border-slate-200 bg-slate-50/70 flex items-center justify-center overflow-hidden">
                                    <img id="captchaImg" src="{{ route('admin.captcha') }}" alt="Kode Verifikasi" class="h-full w-full object-contain select-none">
                                </div>
                                <button type="button" onclick="refreshCaptcha()" class="inline-flex items-center justify-center gap-1.5 px-3.5 h-10 rounded-[8px] border border-slate-200 bg-white hover:border-[#142C5C] text-xs font-semibold text-[#142C5C] transition shrink-0 cursor-pointer" title="Muat ulang kode">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                                    </svg>
                                    <span>Ganti</span>
                                </button>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>
                                    </svg>
                                </span>
                                <input id="captcha" type="text" name="captcha" maxlength="5" required autocomplete="off"
                                       placeholder="Masukkan 5 karakter"
                                       class="w-full h-10 pl-10 pr-3.5 rounded-[8px] border border-slate-200 bg-slate-50/70 focus:bg-white text-sm tabular-nums uppercase tracking-[0.14em] font-bold text-[#142C5C] placeholder:normal-case placeholder:tracking-normal placeholder:font-normal placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10">
                            </div>
                        </div>

                        <div class="pt-1">
                            <button type="submit" class="w-full h-10 bg-[#142C5C] hover:bg-[#142C5C] text-white rounded-[8px] text-[13.5px] font-bold inline-flex items-center justify-center transition active:scale-[0.99] cursor-pointer">
                                <span>Masuk</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Mobile Floating Info Button & Modal -->
    <button type="button"
            id="adminInfoFab"
            onclick="document.getElementById('adminInfoModal').classList.remove('hidden')"
            aria-label="Informasi Panel Admin"
            title="Informasi Panel Admin"
            class="lg:hidden fixed bottom-12 right-4 z-40 w-11 h-11 rounded-full bg-[#142C5C] hover:bg-[#142C5C] text-white shadow-lg border border-white/25 flex items-center justify-center transition active:scale-95 cursor-pointer">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
    </button>

    <div id="adminInfoModal" class="hidden lg:hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-[2px] flex items-center justify-center p-4" onclick="if (event.target === this) this.classList.add('hidden')">
        <div class="w-full max-w-md max-h-[85dvh] overflow-y-auto bg-white border border-slate-200 rounded-[16px] p-5 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-[15px] font-bold text-[#142C5C] tracking-[-0.01em]">Informasi Panel Admin</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Akses pengelolaan ujian sekolah</p>
                </div>
                <button type="button" onclick="document.getElementById('adminInfoModal').classList.add('hidden')" aria-label="Tutup informasi" class="w-8 h-8 rounded-[8px] border border-slate-200 text-slate-500 hover:text-[#142C5C] flex items-center justify-center cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                @foreach($adminHighlights as $item)
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-[10px] bg-slate-100 border border-slate-200/80 text-[#142C5C] flex items-center justify-center shrink-0">
                            <svg class="w-[17px] h-[17px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $item['icon'] !!}</svg>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-[14px] font-bold text-[#142C5C]">{{ $item['title'] }}</h4>
                            <p class="text-[12.5px] text-slate-500 mt-0.5 leading-snug">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" onclick="document.getElementById('adminInfoModal').classList.add('hidden')" class="w-full h-10 bg-[#142C5C] hover:bg-[#142C5C] text-white rounded-[8px] text-xs font-bold transition cursor-pointer">
                Mengerti
            </button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="hidden sm:block bg-[#142C5C] text-blue-100/90 py-2.5 px-4 text-center text-[11.5px] sm:text-xs font-medium shrink-0">
        &copy; {{ date('Y') }} SMKN 1 Tirtamulya &nbsp;|&nbsp; ExaSatrya &ndash; Sistem Ujian Terproteksi
    </footer>

    <!-- Modal Dialog Bantuan Lupa Password -->
    <div id="forgotPasswordModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-[2px] hidden items-center justify-center p-4" onclick="if (event.target === this) closeForgotPasswordModal()">
        <div class="bg-white rounded-[16px] max-w-sm w-full p-6 shadow-xl border border-slate-200 space-y-4">
            <h3 class="text-[15px] font-bold text-[#142C5C]">Bantuan Lupa Password</h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                Hubungi <strong>Tim Kurikulum</strong> atau <strong>Administrator IT SMKN 1 Tirtamulya</strong> dengan menyebutkan Username/NIP Anda untuk reset password.
            </p>
            <button type="button" onclick="closeForgotPasswordModal()" class="w-full h-10 bg-[#142C5C] hover:bg-[#142C5C] text-white rounded-[8px] text-xs font-bold transition cursor-pointer">
                Mengerti
            </button>
        </div>
    </div>

    <script>
        function refreshCaptcha() {
            const img = document.getElementById('captchaImg');
            img.src = '{{ route('admin.captcha') }}?t=' + Date.now();
        }

        function showForgotPasswordModal() {
            const modal = document.getElementById('forgotPasswordModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeForgotPasswordModal() {
            const modal = document.getElementById('forgotPasswordModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;
            } else {
                input.type = 'password';
                icon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
            }
        }

        (function () {
            const fab = document.getElementById('adminInfoFab');
            if (!fab) return;
            const toggleFabForKeyboard = () => {
                const activeTag = document.activeElement ? document.activeElement.tagName : '';
                const isTyping = activeTag === 'INPUT' || activeTag === 'TEXTAREA' || activeTag === 'SELECT';
                fab.classList.toggle('hidden', isTyping);
            };
            document.addEventListener('focusin', toggleFabForKeyboard);
            document.addEventListener('focusout', () => setTimeout(toggleFabForKeyboard, 120));
        })();
    </script>
</body>
</html>
