<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Exambro</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.webp') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }

        /* Hilangkan mata password bawaan Microsoft Edge & WebKit agar tidak dobel/picek */
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
<body class="antialiased min-h-screen flex items-center justify-center p-4 selection:bg-indigo-500 selection:text-white">

    <div class="w-full max-w-md">
        <!-- Logo Area -->
        <div class="text-center mb-6 sm:mb-8">
            <img src="{{ asset('images/logo.webp') }}" class="w-14 h-14 sm:w-16 sm:h-16 object-contain mx-auto mb-3" alt="Logo">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Portal Exambro</h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">Masuk ke portal guru & admin</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl sm:rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/80">
            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50/80 border border-rose-200 text-rose-700 text-sm font-medium flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <input type="text" name="username" value="{{ old('username') }}" required class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all shadow-2xs" placeholder="Masukkan username admin / guru">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-semibold text-slate-800">Password</label>
                        <button type="button" onclick="showForgotPasswordModal()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">
                            Lupa Password?
                        </button>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input type="password" id="password" name="password" required class="w-full pl-11 pr-11 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all shadow-2xs" placeholder="Masukkan password akun">
                        <button type="button" onclick="togglePassword('password', 'password_icon')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition-colors" title="Lihat/Sembunyikan Password">
                            <svg id="password_icon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-indigo-500/25 transition-all duration-150 active:scale-[0.98] focus:outline-none focus:ring-3 focus:ring-indigo-500/30">
                    Masuk ke Dashboard
                </button>
            </form>
        </div>
        
        <div class="text-center mt-8">
            <p class="text-xs font-medium text-slate-400">Protected by Exambro Engine</p>
        </div>
    </div>

    <!-- Modal Dialog Bantuan Lupa Password -->
    <div id="forgotPasswordModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl sm:rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200/80 space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Bantuan Lupa Password</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Untuk menjaga integritas dan keamanan pelaksanaan ujian, reset kata sandi akun Guru dan Siswa dikelola secara terpusat oleh pihak sekolah.
                </p>
                <div class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 space-y-1">
                    <p class="font-semibold text-slate-800">Langkah Pemulihan:</p>
                    <p>• Silakan hubungi <strong>Tim Kurikulum</strong> atau <strong>Administrator IT SMKN 1 Tirtamulya</strong>.</p>
                    <p>• Sampaikan Username/NIP Anda untuk dilakukan reset password default.</p>
                </div>
            </div>
            <button type="button" onclick="closeForgotPasswordModal()" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-colors">
                Saya Mengerti
            </button>
        </div>
    </div>

    <script>
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
