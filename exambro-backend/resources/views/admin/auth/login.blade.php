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
            background-color: #F5F8FF;
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
        svg[stroke="currentColor"]:not([data-decorative]) {
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }
        .bg-geo-grid {
            background-image:
                linear-gradient(to right, rgba(36, 87, 232, 0.12) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(36, 87, 232, 0.12) 1px, transparent 1px);
            background-size: 72px 72px;
        }
        .bg-diagonal-stripes {
            background-image: repeating-linear-gradient(
                -45deg,
                rgba(20, 44, 92, 0.18) 0px,
                rgba(20, 44, 92, 0.18) 8px,
                rgba(36, 87, 232, 0.12) 8px,
                rgba(36, 87, 232, 0.12) 16px,
                transparent 16px,
                transparent 28px
            );
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
<body class="text-slate-900 antialiased min-h-screen flex flex-col justify-between relative overflow-x-hidden selection:bg-blue-600 selection:text-white">

    <!-- Bold Blue Geometric Pattern Background -->
    <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden">
        <div class="bg-geo-grid absolute inset-0 opacity-80"
             style="-webkit-mask-image: linear-gradient(to right, black 0%, rgba(0,0,0,0.22) 32%, rgba(0,0,0,0.22) 68%, black 100%); mask-image: linear-gradient(to right, black 0%, rgba(0,0,0,0.22) 32%, rgba(0,0,0,0.22) 68%, black 100%);"></div>

        <!-- Top-Left: Layered Diagonal-Cut Panel + Bold Diagonal Stripes -->
        <div class="hidden sm:block absolute top-0 left-0 w-72 h-72 bg-[#DCE9FF]/75"
             style="clip-path: polygon(0 0, 100% 0, 0 100%);"></div>
        <div class="hidden sm:block bg-diagonal-stripes absolute top-0 left-0 w-52 h-52"
             style="clip-path: polygon(0 0, 100% 0, 0 100%);"></div>
        <div class="hidden sm:block absolute top-0 left-0 w-64 h-64 origin-top-left"
             style="clip-path: polygon(96% 0, 100% 0, 0 100%, 0 96%); background-color: rgba(36, 87, 232, 0.25);"></div>

        <!-- Top-Right: Concentric Circles + Large Halftone Dots -->
        <svg class="absolute -top-10 -right-12 w-64 h-64 sm:w-[420px] sm:h-[420px]" viewBox="0 0 420 420" fill="none" aria-hidden="true">
            <circle cx="310" cy="110" r="190" stroke="#2457E8" stroke-opacity="0.24" stroke-width="2"/>
            <circle cx="310" cy="110" r="145" stroke="#142C5C" stroke-opacity="0.22" stroke-width="2.5"/>
            <circle cx="310" cy="110" r="95" fill="#DCE9FF" fill-opacity="0.85"/>
            <circle cx="310" cy="110" r="55" fill="#2457E8" fill-opacity="0.16"/>
            <circle cx="150" cy="150" r="7" fill="#2457E8" fill-opacity="0.26"/>
            <circle cx="180" cy="150" r="5.5" fill="#2457E8" fill-opacity="0.22"/>
            <circle cx="210" cy="150" r="4" fill="#142C5C" fill-opacity="0.20"/>
            <circle cx="150" cy="180" r="6" fill="#142C5C" fill-opacity="0.22"/>
            <circle cx="180" cy="180" r="4.5" fill="#2457E8" fill-opacity="0.20"/>
            <circle cx="210" cy="180" r="3" fill="#2457E8" fill-opacity="0.18"/>
            <circle cx="150" cy="210" r="4.5" fill="#2457E8" fill-opacity="0.20"/>
            <circle cx="180" cy="210" r="3.5" fill="#142C5C" fill-opacity="0.18"/>
        </svg>

        <!-- Bottom-Left: Concentric Arcs + Halftone Dots -->
        <svg class="absolute -bottom-12 -left-12 w-64 h-64 sm:w-[420px] sm:h-[420px]" viewBox="0 0 420 420" fill="none" aria-hidden="true">
            <circle cx="110" cy="310" r="190" stroke="#142C5C" stroke-opacity="0.22" stroke-width="2.5"/>
            <circle cx="110" cy="310" r="140" stroke="#2457E8" stroke-opacity="0.25" stroke-width="2"/>
            <circle cx="110" cy="310" r="92" fill="#DCE9FF" fill-opacity="0.85"/>
            <path d="M20 185L185 350" stroke="#142C5C" stroke-opacity="0.22" stroke-width="3"/>
            <circle cx="210" cy="240" r="3.5" fill="#142C5C" fill-opacity="0.20"/>
            <circle cx="240" cy="240" r="5" fill="#2457E8" fill-opacity="0.22"/>
            <circle cx="270" cy="240" r="6.5" fill="#2457E8" fill-opacity="0.26"/>
            <circle cx="210" cy="270" r="4.5" fill="#2457E8" fill-opacity="0.22"/>
            <circle cx="240" cy="270" r="6" fill="#142C5C" fill-opacity="0.22"/>
            <circle cx="270" cy="270" r="7.5" fill="#2457E8" fill-opacity="0.28"/>
        </svg>

        <!-- Bottom-Right: Layered Diagonal-Cut Panel + Bold Diagonal Stripes -->
        <div class="hidden sm:block absolute bottom-0 right-0 w-72 h-72 bg-[#DCE9FF]/80"
             style="clip-path: polygon(100% 0, 100% 100%, 0 100%);"></div>
        <div class="hidden sm:block bg-diagonal-stripes absolute bottom-0 right-0 w-52 h-52"
             style="clip-path: polygon(100% 0, 100% 100%, 0 100%);"></div>
        <div class="hidden sm:block absolute bottom-0 right-0 w-64 h-64"
             style="clip-path: polygon(100% 0, 100% 4%, 4% 100%, 0 100%); background-color: rgba(20, 44, 92, 0.25);"></div>
    </div>

    <!-- Navbar Header -->
    <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between gap-3">
            <a href="{{ route('student.portal') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.webp') }}" alt="Logo SMKN 1 Tirtamulya" class="w-9 h-9 object-contain shrink-0">
                <div>
                    <div class="text-base font-extrabold text-slate-900 tracking-tight leading-none">ExaSatrya</div>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">SMKN 1 Tirtamulya</p>
                </div>
            </a>
            <a href="{{ route('student.portal') }}" class="text-xs font-semibold px-3.5 py-2 rounded-lg border border-slate-200 bg-white hover:border-blue-600 text-slate-700 hover:text-blue-700 transition whitespace-nowrap">
                Portal Siswa
            </a>
        </div>
    </header>

    <!-- Main Login Content -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Login <span class="text-blue-600">Guru &amp; Admin</span>
                </h1>
                <p class="text-slate-600 text-sm mt-1">
                    Masuk untuk mengelola jadwal dan sesi ujian.
                </p>
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm font-medium flex items-start gap-2.5">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:border-blue-600 focus:ring-3 focus:ring-blue-600/15 transition" placeholder="Username / NIP">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
                            <button type="button" onclick="showForgotPasswordModal()" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                                Lupa Password?
                            </button>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" required autocomplete="current-password" class="w-full pl-11 pr-11 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:border-blue-600 focus:ring-3 focus:ring-blue-600/15 transition" placeholder="Masukkan password">
                            <button type="button" onclick="togglePassword('password', 'password_icon')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition" title="Lihat/Sembunyikan Password">
                                <svg id="password_icon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Kode Verifikasi CAPTCHA -->
                    <div>
                        <label for="captcha" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kode Verifikasi</label>
                        <div class="flex items-stretch gap-2.5 mb-2.5">
                            <div class="flex-1 h-12 rounded-xl border border-slate-200 bg-[#F5F8FF] flex items-center justify-center overflow-hidden">
                                <img id="captchaImg" src="{{ route('admin.captcha') }}" alt="Kode Verifikasi" class="h-full w-full object-contain select-none">
                            </div>
                            <button type="button" onclick="refreshCaptcha()" class="inline-flex items-center justify-center gap-1.5 px-4 h-12 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-700 transition shrink-0" title="Muat ulang kode">
                                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Ganti
                            </button>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <input id="captcha" type="text" name="captcha" maxlength="5" required autocomplete="off" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm uppercase tracking-widest font-semibold focus:bg-white focus:outline-none focus:border-blue-600 focus:ring-3 focus:ring-blue-600/15 transition" placeholder="Masukkan 5 karakter">
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-xs transition active:scale-[0.98] focus:outline-none focus:ring-3 focus:ring-blue-600/25">
                            Masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200/80 bg-white/80 py-4 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} SMKN 1 Tirtamulya &bull; Sistem Ujian Terproteksi ExaSatrya
    </footer>

    <!-- Modal Dialog Bantuan Lupa Password -->
    <div id="forgotPasswordModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-slate-900">Bantuan Lupa Password</h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                Hubungi <strong>Tim Kurikulum</strong> atau <strong>Administrator IT SMKN 1 Tirtamulya</strong> dengan menyebutkan Username/NIP Anda untuk reset password.
            </p>
            <button type="button" onclick="closeForgotPasswordModal()" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">
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
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />`;
            } else {
                input.type = 'password';
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
        }
    </script>
</body>
</html>
