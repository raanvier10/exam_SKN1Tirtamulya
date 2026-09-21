<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Ujian Siswa — SMKN 1 Tirtamulya</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen antialiased flex flex-col justify-between selection:bg-indigo-500 selection:text-white">

    <!-- Navbar Header -->
    <header class="border-b border-slate-800/80 bg-slate-900/90 backdrop-blur sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-sky-500 flex items-center justify-center font-black text-white text-lg shadow-lg shadow-indigo-500/20">
                    ES
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-white tracking-tight leading-none">ExaSatrya Portal</h1>
                    <p class="text-xs text-slate-400 mt-0.5">SMKN 1 Tirtamulya</p>
                </div>
            </div>
            <a href="{{ route('admin.login') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-700 hover:border-indigo-500 text-slate-300 hover:text-white transition">
                Login Guru / Admin &rarr;
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12 w-full flex-grow">
        <!-- Hero Section -->
        <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs font-semibold mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Pusat Akses & Instalasi Aplikasi Ujian
            </div>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                Pilih Tipe Perangkat Anda
            </h2>
            <p class="text-sm sm:text-base text-slate-400 mt-2">
                Pastikan mengunduh dan mengakses panduan yang sesuai dengan HP yang Anda gunakan.
            </p>
        </div>

        <!-- Platform Tabs -->
        <div class="flex rounded-2xl bg-slate-800/90 p-1.5 border border-slate-700/80 mb-8 max-w-md mx-auto">
            <button id="tab-android-btn" onclick="switchTab('android')" class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 bg-indigo-600 text-white shadow-md">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4482.9993.9993.0001.5511-.4483.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993 0 .5511-.4482.9997-.9993.9997m11.4045-6.02l1.9973-3.4592a.416.416 0 00-.1521-.5676.416.416 0 00-.5676.1521l-2.0223 3.503C15.5802 8.423 13.8443 8.167 12 8.167s-3.5802.256-5.1368.7827L4.8409 5.4467a.4161.4161 0 00-.5677-.1521.4157.4157 0 00-.1521.5676l1.9973 3.4592C2.6889 11.1867.3432 14.6589 0 18.761h24c-.3432-4.1021-2.6889-7.5743-6.1185-9.4396"/></svg>
                Android (APK)
            </button>
            <button id="tab-ios-btn" onclick="switchTab('ios')" class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 text-slate-400 hover:text-white">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-2 .6-2.65 1.35-.58.67-1.09 1.74-.95 2.77.99.08 2.05-.52 2.68-1.27z"/></svg>
                iOS (iPhone / iPad)
            </button>
        </div>

        <!-- ================= SECTION 1: ANDROID ================= -->
        <div id="content-android" class="space-y-6">
            <div class="bg-gradient-to-b from-slate-800/90 to-slate-800/50 border border-slate-700 rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
                <div class="sm:flex items-start justify-between gap-6">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                            Universal Build (Mendukung Semua HP Android 32-bit & 64-bit)
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-white">Aplikasi Ujian ExaSatrya (Android)</h3>
                        <p class="text-sm text-slate-300 leading-relaxed max-w-xl">
                            Aplikasi resmi ujian sekolah dengan perlindungan layar, anti kecurangan, dan integrasi Google Form.
                        </p>
                        <div class="flex flex-wrap gap-4 text-xs text-slate-400 pt-2">
                            <span>📦 Versi: <strong>1.0.0</strong></span>
                            <span>⚖️ Ukuran: <strong>~51 MB</strong></span>
                            <span>📱 Min. Android: <strong>5.0 (Lollipop) s/d Android 15</strong></span>
                        </div>
                    </div>

                    <div class="mt-6 sm:mt-0 flex-shrink-0">
                        <a href="{{ route('download.apk') }}" class="inline-flex items-center justify-center gap-3 w-full sm:w-auto px-6 py-3.5 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/30 hover:scale-[1.02] active:scale-[0.98] transition">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            Download APK Resmi
                        </a>
                    </div>
                </div>
            </div>

            <!-- Panduan Mengatasi Kendala Instalasi Android -->
            <div class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-5 sm:p-6">
                <h4 class="text-sm font-bold text-slate-200 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Solusi Jika Mengalami Kendala Instalasi:
                </h4>
                <div class="grid sm:grid-cols-3 gap-4 text-xs">
                    <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700">
                        <div class="font-bold text-amber-400 mb-1">1. "Aplikasi Tidak Terinstal"</div>
                        <p class="text-slate-300">
                            Wajib <strong>hapus / uninstall</strong> aplikasi Exambro versi lama yang ada di HP terlebih dahulu, lalu pasang APK baru ini.
                        </p>
                    </div>
                    <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700">
                        <div class="font-bold text-amber-400 mb-1">2. "Diblokir Play Protect"</div>
                        <p class="text-slate-300">
                            Klik tulisan <strong>"Detail selengkapnya"</strong> lalu pilih <strong>"Tetap instal (tidak aman)"</strong>.
                        </p>
                    </div>
                    <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700">
                        <div class="font-bold text-amber-400 mb-1">3. "Installer Terhenti"</div>
                        <p class="text-slate-300">
                            Cek ukuran file di File Manager. Pastikan unduhan selesai 100% (ukuran file harus pas ~51 MB). Jangan buka file yang terputus.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= SECTION 2: iOS (iPhone / iPad) ================= -->
        <div id="content-ios" class="hidden space-y-6">
            <!-- Peringatan Penting iOS -->
            <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5">
                <svg class="w-6 h-6 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="text-xs sm:text-sm text-amber-200">
                    <strong class="font-bold text-amber-300 block mb-1">PENTING BAGI PENGGUNA iPHONE / iPAD:</strong>
                    Sistem operasi Apple iOS <strong>TIDAK MENGGUNAKAN FILE .APK</strong> (karena file APK hanya untuk Android). Jangan mendownload file APK. Siswa iOS cukup mengikuti 2 langkah di bawah:
                </div>
            </div>

            <!-- 2 Langkah Akses iOS -->
            <div class="grid sm:grid-cols-2 gap-6">
                <!-- Langkah 1 -->
                <div class="bg-slate-800/90 border border-slate-700 rounded-3xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-xs mb-4">1</div>
                        <h4 class="text-lg font-bold text-white mb-2">Install Safe Exam Browser</h4>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Unduh aplikasi resmi <strong>Safe Exam Browser</strong> gratis di App Store pada iPhone atau iPad Anda.
                        </p>
                    </div>
                    <a href="https://apps.apple.com/app/safe-exam-browser/id1138834203" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-white hover:bg-slate-100 text-slate-900 text-xs font-bold rounded-xl transition">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-2 .6-2.65 1.35-.58.67-1.09 1.74-.95 2.77.99.08 2.05-.52 2.68-1.27z"/></svg>
                        Buka di App Store &rarr;
                    </a>
                </div>

                <!-- Langkah 2 -->
                <div class="bg-slate-800/90 border border-slate-700 rounded-3xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-xs mb-4">2</div>
                        <h4 class="text-lg font-bold text-white mb-2">Buka Ujian di SEB</h4>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Pilih jadwal ujian Anda di bawah ini dan tekan tombol peluncur untuk mengunci layar iPhone ke mode ujian.
                        </p>
                    </div>
                    <div class="text-xs text-slate-400 bg-slate-900/60 p-3 rounded-xl border border-slate-700/60">
                        Pastikan Anda membukanya menggunakan browser <strong>Safari</strong> pada iPhone/iPad.
                    </div>
                </div>
            </div>

            <!-- Daftar Ujian Hari Ini untuk Siswa iOS -->
            <div class="bg-slate-800/90 border border-slate-700 rounded-3xl p-6">
                <h4 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    Jadwal Ujian Aktif (Akses Langsung iOS)
                </h4>
                @if(isset($todayExams) && count($todayExams) > 0)
                    <div class="divide-y divide-slate-700/80">
                        @foreach($todayExams as $exam)
                            <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h5 class="text-sm font-bold text-white">{{ $exam->title }}</h5>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        Durasi: {{ $exam->duration }} Menit &bull; Mulai: {{ \Carbon\Carbon::parse($exam->start_at)->format('H:i') }} WIB
                                    </p>
                                </div>
                                <a href="{{ route('seb.exam.show', $exam->id) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition">
                                    Mulai di Safe Exam Browser &rarr;
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-xs text-slate-400">
                        Tidak ada ujian aktif yang dijadwalkan hari ini.
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} SMKN 1 Tirtamulya &bull; Sistem Ujian Terproteksi ExaSatrya
    </footer>

    <script>
        // Deteksi Otomatis OS Pengunjung
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

            if (platform === 'ios') {
                btnIos.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 bg-indigo-600 text-white shadow-md';
                btnAndroid.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 text-slate-400 hover:text-white';
                contentIos.classList.remove('hidden');
                contentAndroid.classList.add('hidden');
            } else {
                btnAndroid.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 bg-indigo-600 text-white shadow-md';
                btnIos.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 text-slate-400 hover:text-white';
                contentAndroid.classList.remove('hidden');
                contentIos.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', detectDevice);
    </script>
</body>
</html>
