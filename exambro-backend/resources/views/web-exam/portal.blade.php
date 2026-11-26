<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Ujian Darurat — ExaSatrya</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F5F8FF;
            color: #0F172A;
        }
        .bg-geo-grid {
            background-image:
                linear-gradient(to right, rgba(36, 87, 232, 0.08) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(36, 87, 232, 0.08) 1px, transparent 1px);
            background-size: 72px 72px;
        }
    </style>
</head>
<body class="min-h-[100dvh] antialiased flex flex-col relative overflow-x-hidden selection:bg-[#2457E8] selection:text-white">

    <!-- Subtle System Geometric Background (matching portal/index.blade.php) -->
    <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden">
        <div class="bg-geo-grid absolute inset-0 opacity-80"
             style="-webkit-mask-image: linear-gradient(to right, black 0%, rgba(0,0,0,0.2) 30%, rgba(0,0,0,0.2) 70%, black 100%); mask-image: linear-gradient(to right, black 0%, rgba(0,0,0,0.2) 30%, rgba(0,0,0,0.2) 70%, black 100%);"></div>
        <svg class="absolute -top-12 -right-12 w-64 h-64 sm:w-[380px] sm:h-[380px]" viewBox="0 0 420 420" fill="none" aria-hidden="true">
            <circle cx="310" cy="110" r="185" stroke="#2457E8" stroke-opacity="0.14" stroke-width="2"/>
            <circle cx="310" cy="110" r="135" stroke="#142C5C" stroke-opacity="0.12" stroke-width="2"/>
            <circle cx="310" cy="110" r="85" fill="#DCE9FF" fill-opacity="0.65"/>
        </svg>
        <svg class="absolute -bottom-16 -left-16 w-64 h-64 sm:w-[380px] sm:h-[380px]" viewBox="0 0 420 420" fill="none" aria-hidden="true">
            <circle cx="110" cy="310" r="185" stroke="#142C5C" stroke-opacity="0.12" stroke-width="2"/>
            <circle cx="110" cy="310" r="130" stroke="#2457E8" stroke-opacity="0.15" stroke-width="2"/>
            <circle cx="110" cy="310" r="80" fill="#DCE9FF" fill-opacity="0.65"/>
        </svg>
    </div>

    <!-- Header -->
    <header class="border-b border-slate-200/80 bg-white/95 backdrop-blur-md sticky top-0 z-30">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('images/logo.webp') }}" alt="Logo SMKN 1 Tirtamulya" class="w-9 h-9 object-contain shrink-0">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h1 class="text-base font-extrabold text-[#142C5C] tracking-tight leading-none">ExaSatrya</h1>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#DCE9FF] text-[#142C5C] border border-[#2457E8]/20">
                            WEB DARURAT
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium truncate">SMKN 1 Tirtamulya</p>
                </div>
            </div>

            @if($verified)
                <form action="{{ route('web-exam.logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:border-rose-300 text-xs font-semibold text-slate-700 hover:text-rose-600 shadow-2xs transition cursor-pointer">
                        Keluar
                    </button>
                </form>
            @else
                <a href="{{ route('student.portal') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:border-[#2457E8] text-xs font-semibold text-slate-700 hover:text-[#2457E8] shadow-2xs transition shrink-0">
                    &larr; Portal Utama
                </a>
            @endif
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-10 w-full flex-1 flex flex-col justify-center">
        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm font-medium flex items-center gap-3 shadow-2xs max-w-3xl mx-auto w-full">
                <span class="w-2 h-2 rounded-full bg-rose-500 ring-4 ring-rose-100 shrink-0"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(!$enabled)
            <!-- State 1: Mode Darurat Nonaktif -->
            <div class="max-w-md mx-auto w-full bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 text-center space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-[#F5F8FF] border border-[#DCE9FF] text-[#2457E8] flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div class="space-y-1.5">
                    <h2 class="text-lg sm:text-xl font-extrabold text-[#142C5C] tracking-tight">Mode Web Ujian Darurat Tidak Aktif</h2>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Akses ujian melalui browser saat ini ditutup. Siswa wajib menggunakan aplikasi resmi <strong>ExaSatrya</strong>, atau melapor ke Pengawas Ruang / PIC Jurusan bila perangkat bermasalah.
                    </p>
                </div>
                <div class="pt-1">
                    <a href="{{ route('student.portal') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98]">
                        Kembali ke Portal Unduhan
                    </a>
                </div>
            </div>

        @elseif(!$verified)
            <!-- State 2: Login Web Darurat (Matched ExaSatrya Navy + White Card System) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
                <!-- Left Briefing Card (matching dashboard hero gradient) -->
                <div class="lg:col-span-5 relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0D2149] via-[#142C5C] to-[#2457E8] p-6 sm:p-7 text-white shadow-sm flex flex-col justify-between gap-6">
                    <svg class="hidden sm:block absolute -right-10 -top-10 w-56 h-56 pointer-events-none" viewBox="0 0 300 300" fill="none" aria-hidden="true">
                        <circle cx="190" cy="110" r="115" stroke="#DCE9FF" stroke-opacity="0.14" stroke-width="1.5"/>
                        <circle cx="190" cy="110" r="80" stroke="#DCE9FF" stroke-opacity="0.18" stroke-width="1.5"/>
                    </svg>

                    <div class="relative z-10 space-y-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/10 border border-white/15 text-blue-100 text-[11px] font-bold">
                            Akses Khusus Kendala Perangkat
                        </span>
                        <h2 class="text-2xl font-extrabold tracking-tight leading-snug">
                            Ujian Web Darurat Terproteksi
                        </h2>
                        <p class="text-xs sm:text-sm text-blue-100/85 leading-relaxed">
                            Hanya untuk siswa yang tidak dapat menjalankan aplikasi ujian. Masuk memerlukan verifikasi akun siswa dan PIN Pengawas Ruang.
                        </p>
                    </div>

                    <div class="relative z-10 space-y-2.5">
                        <div class="rounded-xl bg-white/10 border border-white/15 px-3.5 py-2.5 text-xs text-blue-50 flex items-center gap-2.5">
                            <span class="font-bold text-amber-300 tabular-nums">01</span>
                            <span>Mode layar penuh wajib aktif saat mengerjakan</span>
                        </div>
                        <div class="rounded-xl bg-white/10 border border-white/15 px-3.5 py-2.5 text-xs text-blue-50 flex items-center gap-2.5">
                            <span class="font-bold text-amber-300 tabular-nums">02</span>
                            <span>Pindah tab tercatat otomatis sebagai pelanggaran</span>
                        </div>
                        <div class="rounded-xl bg-white/10 border border-white/15 px-3.5 py-2.5 text-xs text-blue-50 flex items-center gap-2.5">
                            <span class="font-bold text-amber-300 tabular-nums">03</span>
                            <span>Buka kunci sesi hanya melalui Pengawas / PIC Jurusan</span>
                        </div>
                    </div>
                </div>

                <!-- Right Login Form Card -->
                <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 flex flex-col justify-center">
                    <div class="mb-5 pb-4 border-b border-slate-100">
                        <h3 class="text-lg sm:text-xl font-extrabold text-[#142C5C] tracking-tight">Masuk Peserta Ujian</h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Gunakan NIS, Password siswa, dan PIN dari pengawas ruang.</p>
                    </div>

                    <form action="{{ route('web-exam.login') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="username" class="block text-xs font-bold text-slate-700 mb-1.5">NIS / Username Siswa</label>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                                   placeholder="Masukkan NIS siswa"
                                   class="w-full h-11 px-4 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15 tabular-nums">
                            @error('username')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Password Siswa</label>
                            <input type="password" id="password" name="password" required autocomplete="current-password"
                                   placeholder="Masukkan password akun"
                                   class="w-full h-11 px-4 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15">
                            @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="pin" class="block text-xs font-bold text-[#142C5C]">Kode PIN Pengawas</label>
                                <span class="text-[11px] text-slate-400 font-medium">6 karakter aktif</span>
                            </div>
                            <input type="text" id="pin" name="pin" required autocomplete="off"
                                   placeholder="Contoh: K7M9P2"
                                   class="w-full h-11 px-4 rounded-xl border border-[#2457E8]/35 bg-[#F5F8FF] focus:bg-white text-sm tabular-nums font-bold tracking-[0.2em] uppercase text-[#142C5C] placeholder:text-slate-400 placeholder:tracking-normal focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15">
                            @error('pin')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="pt-1">
                            <button type="submit" class="w-full h-11 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-xs transition active:scale-[0.98] cursor-pointer">
                                Masuk &amp; Lihat Jadwal Ujian
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        @else
            <!-- State 3: Verified Student Lobby -->
            <div class="space-y-5 max-w-3xl mx-auto w-full">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0D2149] via-[#142C5C] to-[#2457E8] p-5 sm:p-6 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-white/15 border border-white/20 text-white flex items-center justify-center font-extrabold text-sm shrink-0">
                            {{ strtoupper(substr($user->name ?? 'S', 0, 2)) }}
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-amber-300">Peserta Terverifikasi &bull; Mode Web Darurat</div>
                            <h2 class="text-lg sm:text-xl font-extrabold tracking-tight">{{ $user->name }}</h2>
                            <div class="text-xs text-blue-100/85 tabular-nums mt-0.5">
                                NIS: {{ $user->username }} &bull; Kelas: {{ $user->class?->name ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="px-3.5 py-2 rounded-xl bg-white/10 border border-white/15 text-blue-50 text-xs font-medium max-w-xs">
                        Pindah tab atau keluar layar penuh tercatat otomatis ke pengawas.
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm sm:text-base font-bold text-[#142C5C]">Jadwal Ujian Kelas Anda</h3>
                        <span class="text-xs font-semibold text-slate-500">{{ $exams->count() }} Ujian</span>
                    </div>

                    @if($exams->isNotEmpty())
                        <div class="divide-y divide-slate-100">
                            @foreach($exams as $exam)
                                <div class="p-5 hover:bg-[#F5F8FF]/60 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="text-base font-bold text-slate-900">{{ $exam->title }}</h4>
                                            <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-[#DCE9FF] text-[#142C5C]">
                                                {{ $exam->duration }} Menit
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 tabular-nums">
                                            Mulai: {{ \Carbon\Carbon::parse($exam->start_at)->format('d M Y, H:i') }} WIB
                                            @if($exam->end_at)
                                                &bull; Batas: {{ \Carbon\Carbon::parse($exam->end_at)->format('H:i') }} WIB
                                            @endif
                                        </p>
                                    </div>

                                    <div class="shrink-0">
                                        @if($exam->participant_status === 'locked')
                                            <span class="inline-flex items-center px-3.5 py-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold">
                                                Terkunci &mdash; Hubungi PIC / Pengawas
                                            </span>
                                        @elseif($exam->participant_status === 'finished')
                                            <span class="inline-flex items-center px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold">
                                                Sudah Selesai
                                            </span>
                                        @elseif(now()->lt($exam->start_at))
                                            <span class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold">
                                                Belum Dimulai
                                            </span>
                                        @else
                                            <a href="{{ route('web-exam.exam', $exam) }}" class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98]">
                                                {{ $exam->participant_status === 'working' ? 'Lanjutkan Ujian' : 'Mulai Ujian' }} &rarr;
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-10 text-center text-sm text-slate-500">
                            Tidak ada jadwal ujian aktif untuk kelas Anda saat ini.
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </main>

    <footer class="border-t border-slate-200/80 bg-white/90 py-3.5 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} SMKN 1 Tirtamulya &bull; Sistem Ujian Terproteksi ExaSatrya
    </footer>
</body>
</html>
