<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExaSatrya Portal — SMKN 1 Tirtamulya</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
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
        h1, h2, h3, h4 {
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
    </style>
</head>
<body class="text-slate-900 min-h-screen antialiased flex flex-col relative overflow-x-clip selection:bg-blue-600 selection:text-white">

    <!-- Bold Blue Geometric Pattern Background -->
    <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden">
        <!-- Large Geometric Grid (stronger at left/right edges, clean in center) -->
        <div class="bg-geo-grid absolute inset-0 opacity-80"
             style="-webkit-mask-image: linear-gradient(to right, black 0%, rgba(0,0,0,0.22) 32%, rgba(0,0,0,0.22) 68%, black 100%); mask-image: linear-gradient(to right, black 0%, rgba(0,0,0,0.22) 32%, rgba(0,0,0,0.22) 68%, black 100%);"></div>

        <!-- Top-Left: Layered Diagonal-Cut Panel + Bold Diagonal Stripes -->
        <div class="hidden sm:block absolute top-0 left-0 w-72 h-72 bg-[#DCE9FF]/75"
             style="clip-path: polygon(0 0, 100% 0, 0 100%);"></div>
        <div class="hidden sm:block bg-diagonal-stripes absolute top-0 left-0 w-52 h-52"
             style="clip-path: polygon(0 0, 100% 0, 0 100%);"></div>
        <div class="hidden sm:block absolute top-0 left-0 w-64 h-64 border-r-2 border-[#2457E8]/25 origin-top-left"
             style="clip-path: polygon(96% 0, 100% 0, 0 100%, 0 96%); background-color: rgba(36, 87, 232, 0.25);"></div>

        <!-- Top-Right: Concentric Circles + Large Halftone Dots -->
        <svg class="absolute -top-10 -right-12 w-64 h-64 sm:w-[420px] sm:h-[420px]" viewBox="0 0 420 420" fill="none" aria-hidden="true">
            <circle cx="310" cy="110" r="190" stroke="#2457E8" stroke-opacity="0.24" stroke-width="2"/>
            <circle cx="310" cy="110" r="145" stroke="#142C5C" stroke-opacity="0.22" stroke-width="2.5"/>
            <circle cx="310" cy="110" r="95" fill="#DCE9FF" fill-opacity="0.85"/>
            <circle cx="310" cy="110" r="55" fill="#2457E8" fill-opacity="0.16"/>
            <!-- Varying-size Halftone Dots -->
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
            <!-- Varying-size Halftone Dots -->
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
    <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.webp') }}" alt="Logo SMKN 1 Tirtamulya" class="w-9 h-9 object-contain shrink-0">
                <div>
                    <h1 class="text-base font-extrabold text-slate-900 tracking-tight leading-none">ExaSatrya</h1>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">SMKN 1 Tirtamulya</p>
                </div>
            </div>
            <a href="{{ route('admin.login') }}" class="text-xs font-semibold px-3.5 py-2 rounded-lg border border-slate-200 bg-white hover:border-blue-600 text-slate-700 hover:text-blue-700 transition whitespace-nowrap">
                Login Guru / Admin
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-3xl mx-auto px-4 sm:px-6 py-6 sm:py-7 w-full flex-grow flex flex-col justify-center">
        <!-- Hero -->
        <div class="text-center mb-5">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Portal Ujian <span class="text-blue-600">Siswa</span>
            </h2>
            <p class="text-sm text-slate-600 mt-1.5 max-w-lg mx-auto">
                Pilih tipe perangkat Anda untuk mengunduh aplikasi ujian atau mengakses jadwal ujian hari ini.
            </p>
        </div>

        <!-- Platform Tabs -->
        <div class="flex rounded-xl bg-white p-1 border border-slate-200 shadow-xs mb-5 max-w-sm mx-auto w-full">
            <button id="tab-android-btn" onclick="switchTab('android')" class="flex-1 py-2.5 px-4 rounded-lg text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 bg-blue-600 text-white">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4482.9993.9993.0001.5511-.4483.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4482.9993.9993 0 .5511-.4482.9997-.9993.9997m11.4045-6.02l1.9973-3.4592a.416.416 0 00-.1521-.5676.416.416 0 00-.5676.1521l-2.0223 3.503C15.5802 8.423 13.8443 8.167 12 8.167s-3.5802.256-5.1368.7827L4.8409 5.4467a.4161.4161 0 00-.5677-.1521.4157.4157 0 00-.1521.5676l1.9973 3.4592C2.6889 11.1867.3432 14.6589 0 18.761h24c-.3432-4.1021-2.6889-7.5743-6.1185-9.4396"/></svg>
                Android (APK)
            </button>
            <button id="tab-ios-btn" onclick="switchTab('ios')" class="flex-1 py-2.5 px-4 rounded-lg text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-2 .6-2.65 1.35-.58.67-1.09 1.74-.95 2.77.99.08 2.05-.52 2.68-1.27z"/></svg>
                iOS (iPhone / iPad)
            </button>
        </div>

        <!-- ================= SECTION 1: ANDROID ================= -->
        <div id="content-android" class="space-y-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-sm text-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="space-y-2">
                        <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Aplikasi Ujian ExaSatrya (Android)</h3>
                        <p class="text-sm text-slate-600 leading-relaxed max-w-lg mx-auto">
                            Aplikasi resmi ujian sekolah dengan proteksi layar, anti kecurangan, dan integrasi Google Form.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs text-slate-600">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Versi <strong class="text-slate-900">1.0.0</strong>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                            Ukuran <strong class="text-slate-900">~51 MB</strong>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Android <strong class="text-slate-900">5.0 – 15 (32/64-bit)</strong>
                        </span>
                    </div>

                    <a href="{{ route('download.apk') }}" class="inline-flex items-center justify-center gap-2.5 w-full sm:w-auto px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition active:scale-[0.98] mt-1">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        Download APK Resmi
                    </a>
                </div>
            </div>

            <!-- Panduan Mengatasi Kendala Instalasi -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <h4 class="text-sm font-bold text-slate-900 mb-3">Panduan &amp; Solusi Kendala Instalasi</h4>
                <div class="grid sm:grid-cols-3 gap-4 text-xs">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                        <div class="font-bold text-slate-900 mb-1.5">1. Aplikasi Tidak Terinstal</div>
                        <p class="text-slate-600 leading-relaxed text-justify">
                            Hapus / uninstall aplikasi Exambro versi lama di HP terlebih dahulu, lalu pasang APK baru ini.
                        </p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                        <div class="font-bold text-slate-900 mb-1.5">2. Diblokir Play Protect</div>
                        <p class="text-slate-600 leading-relaxed text-justify">
                            Klik tulisan <strong>Detail selengkapnya</strong> pada peringatan Play Protect, lalu pilih <strong>Tetap instal</strong>.
                        </p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                        <div class="font-bold text-slate-900 mb-1.5">3. Installer Terhenti</div>
                        <p class="text-slate-600 leading-relaxed text-justify">
                            Pastikan ukuran file di File Manager utuh (~51 MB) dan unduhan selesai 100% sebelum dibuka.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= SECTION 2: iOS (iPhone / iPad) ================= -->
        <div id="content-ios" class="hidden space-y-4">
            <!-- Setup iOS -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-sm text-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="space-y-2">
                        <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Aplikasi Ujian ExaSatrya (iPhone / iPad)</h3>
                        <p class="text-sm text-slate-600 leading-relaxed max-w-lg mx-auto">
                            Perangkat Apple iOS tidak menggunakan file .APK. Unduh Safe Exam Browser resmi dari App Store untuk mengikuti ujian.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs text-slate-600">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Aplikasi <strong class="text-slate-900">Safe Exam Browser</strong>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Platform <strong class="text-slate-900">iOS / iPadOS</strong>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                            Browser <strong class="text-slate-900">Safari</strong>
                        </span>
                    </div>

                    <a href="https://apps.apple.com/us/app/safeexambrowser/id1155002964" target="_blank" class="inline-flex items-center justify-center gap-2.5 w-full sm:w-auto px-8 py-3.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-xl shadow-sm transition active:scale-[0.98] mt-1">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-2 .6-2.65 1.35-.58.67-1.09 1.74-.95 2.77.99.08 2.05-.52 2.68-1.27z"/></svg>
                        Unduh di App Store
                    </a>
                </div>
            </div>

            <!-- Panduan Ujian iOS -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <h4 class="text-sm font-bold text-slate-900 mb-3">Panduan Mengikuti Ujian di iPhone / iPad</h4>
                <div class="grid sm:grid-cols-3 gap-4 text-xs">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                        <div class="font-bold text-slate-900 mb-1.5">1. Install Safe Exam Browser</div>
                        <p class="text-slate-600 leading-relaxed text-justify">
                            Unduh dan pasang aplikasi resmi <strong>Safe Exam Browser</strong> dari App Store melalui tombol di atas.
                        </p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                        <div class="font-bold text-slate-900 mb-1.5">2. Scan QR / Link dari Pengawas</div>
                        <p class="text-slate-600 leading-relaxed text-justify">
                            Buka link ujian atau scan QR Code yang diberikan pengawas menggunakan browser <strong>Safari</strong> agar otomatis masuk ke SEB.
                        </p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                        <div class="font-bold text-slate-900 mb-1.5">3. Login &amp; Kerjakan Ujian</div>
                        <p class="text-slate-600 leading-relaxed text-justify">
                            Masuk menggunakan NIS dan kata sandi siswa, lalu kerjakan soal sampai menekan tombol <strong>Kirim</strong> dan <strong>Selesai</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200/80 bg-white/90 py-3.5 text-center text-xs text-slate-500 relative z-10">
        &copy; {{ date('Y') }} SMKN 1 Tirtamulya &bull; Sistem Ujian Terproteksi ExaSatrya
    </footer>

    <script>
        function detectDevice() {
            const ua = navigator.userAgent || navigator.vendor || window.opera;
            if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) {
                switchTab('ios');
            } else {
                switchTab('android');
            }
        }

        function switchTab(platform) {
            const btnAndroid = document.getElementById('tab-android-btn');
            const btnIos = document.getElementById('tab-ios-btn');
            const contentAndroid = document.getElementById('content-android');
            const contentIos = document.getElementById('content-ios');

            const activeClass = 'flex-1 py-2.5 px-4 rounded-lg text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 bg-blue-600 text-white';
            const inactiveClass = 'flex-1 py-2.5 px-4 rounded-lg text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900';

            if (platform === 'ios') {
                btnIos.className = activeClass;
                btnAndroid.className = inactiveClass;
                contentIos.classList.remove('hidden');
                contentAndroid.classList.add('hidden');
            } else {
                btnAndroid.className = activeClass;
                btnIos.className = inactiveClass;
                contentAndroid.classList.remove('hidden');
                contentIos.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', detectDevice);
    </script>
</body>
</html>
