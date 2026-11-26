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
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 14px;
            line-height: 1.55;
            background-color: #F5F8FF;
            color: #0F172A;
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
        .card {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.03);
            transition: box-shadow 150ms ease, border-color 150ms ease;
        }
        .card-header {
            padding: 18px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #E2E8F0;
        }
        .card-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        svg[stroke="currentColor"]:not([data-decorative]) {
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }
        .bg-geo-grid {
            background-image:
                linear-gradient(to right, rgba(36, 87, 232, 0.08) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(36, 87, 232, 0.08) 1px, transparent 1px);
            background-size: 72px 72px;
        }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }
    </style>
</head>
<body class="min-h-[100dvh] antialiased flex flex-col relative overflow-x-hidden selection:bg-[#2457E8] selection:text-white">

    <!-- Subtle System Geometric Background -->
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
        <div class="max-w-[1040px] mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('images/logo.webp') }}" alt="Logo SMKN 1 Tirtamulya" class="w-9 h-9 object-contain shrink-0">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h1 class="text-base font-extrabold text-[#142C5C] tracking-tight leading-none">ExaSatrya</h1>
                        <span class="text-xs font-semibold text-[#2457E8]">• Web Darurat</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium truncate">SMKN 1 Tirtamulya</p>
                </div>
            </div>

            @if($verified)
                <form action="{{ route('web-exam.logout') }}" method="POST" class="m-0 shrink-0">
                    @csrf
                    <button type="submit" class="h-9 px-3.5 inline-flex items-center gap-[6px] rounded-[8px] border border-slate-200 bg-white hover:border-rose-300 text-xs font-semibold text-slate-700 hover:text-rose-600 shadow-2xs transition cursor-pointer whitespace-nowrap">
                        <svg class="w-[15px] h-[15px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            @else
                <a href="{{ route('student.portal') }}" class="h-9 px-3.5 inline-flex items-center gap-[6px] rounded-[8px] border border-slate-200 bg-white hover:border-[#2457E8] text-xs font-semibold text-slate-700 hover:text-[#2457E8] shadow-2xs transition shrink-0 whitespace-nowrap">
                    <svg class="w-[15px] h-[15px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Portal Utama</span>
                </a>
            @endif
        </div>
    </header>

    <main class="max-w-[1040px] mx-auto px-4 sm:px-6 lg:px-8 py-[30px] w-full flex-1 flex flex-col justify-center">
        @if(session('error'))
            <div class="mb-[22px] p-4 rounded-[12px] bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm font-medium flex items-center gap-3 shadow-2xs max-w-4xl mx-auto w-full">
                <span class="w-2 h-2 rounded-full bg-rose-500 ring-4 ring-rose-100 shrink-0"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(!$enabled)
            <!-- State 1: Mode Darurat Nonaktif -->
            <div class="max-w-md mx-auto w-full card p-7 text-center space-y-4">
                <div class="w-12 h-12 rounded-[8px] bg-[#F5F8FF] border border-[#DCE9FF] text-[#2457E8] flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div class="space-y-1.5">
                    <h2 class="text-lg font-extrabold text-[#142C5C] tracking-tight">Mode Web Ujian Darurat Tidak Aktif</h2>
                    <p class="text-[13.5px] text-slate-600 leading-relaxed">
                        Akses ujian melalui browser saat ini ditutup. Gunakan aplikasi resmi <strong>ExaSatrya</strong>, atau lapor ke Pengawas Ruang / PIC Jurusan bila perangkat bermasalah.
                    </p>
                </div>
                <div class="pt-1">
                    <a href="{{ route('student.portal') }}" class="h-10 px-5 inline-flex items-center justify-center gap-[6px] bg-[#2457E8] hover:bg-blue-700 text-white rounded-[8px] text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98]">
                        <span>Kembali ke Portal Unduhan</span>
                    </a>
                </div>
            </div>

        @elseif(!$verified)
            <!-- State 2: Login Web Darurat -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-[22px] items-stretch">
                <!-- Left Briefing Card -->
                <div class="lg:col-span-5 relative overflow-hidden rounded-[12px] bg-gradient-to-br from-[#0D2149] via-[#142C5C] to-[#2457E8] p-6 sm:p-7 text-white shadow-sm flex flex-col justify-between gap-6">
                    <svg class="hidden sm:block absolute -right-10 -top-10 w-56 h-56 pointer-events-none" viewBox="0 0 300 300" fill="none" aria-hidden="true">
                        <circle cx="190" cy="110" r="115" stroke="#DCE9FF" stroke-opacity="0.14" stroke-width="1.5"/>
                        <circle cx="190" cy="110" r="80" stroke="#DCE9FF" stroke-opacity="0.18" stroke-width="1.5"/>
                    </svg>

                    <div class="relative z-10 space-y-2.5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.06em] text-amber-300">
                            Akses Khusus Kendala Perangkat
                        </div>
                        <h2 class="text-2xl font-extrabold tracking-tight leading-snug">
                            Ujian Web Darurat Terproteksi
                        </h2>
                        <p class="text-[13.5px] text-blue-100/85 leading-relaxed">
                            Khusus siswa yang tidak dapat menjalankan aplikasi ujian. Masuk memerlukan akun siswa aktif dan PIN Pengawas Ruang.
                        </p>
                    </div>

                    <div class="relative z-10 space-y-2.5">
                        <div class="rounded-[8px] bg-white/10 border border-white/15 px-3.5 py-2.5 text-xs text-blue-50 flex items-center gap-2.5">
                            <span class="font-bold text-amber-300 tabular-nums shrink-0">01</span>
                            <span>Mode layar penuh wajib aktif saat mengerjakan</span>
                        </div>
                        <div class="rounded-[8px] bg-white/10 border border-white/15 px-3.5 py-2.5 text-xs text-blue-50 flex items-center gap-2.5">
                            <span class="font-bold text-amber-300 tabular-nums shrink-0">02</span>
                            <span>Pindah tab tercatat otomatis sebagai pelanggaran</span>
                        </div>
                        <div class="rounded-[8px] bg-white/10 border border-white/15 px-3.5 py-2.5 text-xs text-blue-50 flex items-center gap-2.5">
                            <span class="font-bold text-amber-300 tabular-nums shrink-0">03</span>
                            <span>Buka kunci sesi hanya melalui Pengawas / PIC Jurusan</span>
                        </div>
                    </div>
                </div>

                <!-- Right Login Form Card -->
                <div class="lg:col-span-7 card p-6 sm:p-7 flex flex-col justify-center">
                    <div class="mb-5 pb-4 border-b border-slate-100">
                        <h3 class="text-lg font-extrabold text-[#142C5C] tracking-tight">Masuk Peserta Ujian</h3>
                        <p class="text-[13px] text-slate-500 mt-1">Gunakan NIS, Password siswa, dan PIN aktif dari pengawas ruang.</p>
                    </div>

                    <form action="{{ route('web-exam.login') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="username" class="block text-xs font-bold text-slate-700 mb-1.5">NIS / Username Siswa</label>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                                   placeholder="Masukkan NIS siswa"
                                   class="w-full h-11 px-3.5 rounded-[8px] border border-slate-200 bg-slate-50 focus:bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15 tabular-nums">
                            @error('username')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Password Siswa</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required autocomplete="current-password"
                                       placeholder="Masukkan password akun"
                                       class="w-full h-11 pl-3.5 pr-11 rounded-[8px] border border-slate-200 bg-slate-50 focus:bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15">
                                <button type="button" onclick="const i = document.getElementById('password'); i.type = i.type === 'password' ? 'text' : 'password';" class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer" title="Lihat/Sembunyikan Password" aria-label="Lihat atau sembunyikan password">
                                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="pin" class="block text-xs font-bold text-[#142C5C]">Kode PIN Pengawas</label>
                                <span class="text-[11px] text-slate-400 font-medium">6 karakter aktif</span>
                            </div>
                            <input type="text" id="pin" name="pin" required maxlength="20" autocomplete="off"
                                   placeholder="Contoh: BEFD5Z"
                                   class="w-full h-11 px-3.5 rounded-[8px] border border-[#2457E8]/35 bg-[#F5F8FF] focus:bg-white text-sm tabular-nums font-bold tracking-[0.12em] uppercase text-[#142C5C] placeholder:text-slate-400 placeholder:tracking-normal placeholder:font-normal focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15">
                            @error('pin')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="pt-1">
                            <button type="submit" class="w-full h-11 bg-[#2457E8] hover:bg-blue-700 text-white rounded-[8px] text-sm font-bold shadow-xs transition active:scale-[0.98] cursor-pointer">
                                Masuk &amp; Lihat Jadwal Ujian
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        @else
            <!-- State 3: Verified Student Lobby -->
            <div class="space-y-[22px] max-w-4xl mx-auto w-full">
                <!-- Student Profile & Rule Summary -->
                <div class="relative overflow-hidden rounded-[12px] bg-gradient-to-r from-[#0D2149] via-[#142C5C] to-[#2457E8] p-6 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-[8px] bg-white/15 border border-white/20 text-white flex items-center justify-center font-extrabold text-sm shrink-0">
                            {{ strtoupper(substr($user->name ?? 'S', 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-[11px] font-bold uppercase tracking-[0.05em] text-amber-300">Peserta Terverifikasi &bull; Mode Web Darurat</div>
                            <h2 class="text-lg sm:text-xl font-extrabold tracking-tight mt-0.5">{{ $user->name }}</h2>
                            <div class="text-xs text-blue-100/90 tabular-nums mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                <span>NIS: {{ $user->username }}</span>
                                <span class="text-blue-200/50">&bull;</span>
                                <span>Kelas: {{ $user->class?->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-3.5 py-2.5 rounded-[8px] bg-white/10 border border-white/15 text-blue-50 text-xs leading-snug md:max-w-xs">
                        Pindah tab atau keluar layar penuh tercatat otomatis ke pengawas ruang.
                    </div>
                </div>

                <!-- Exam List Card -->
                <div class="card overflow-hidden">
                    <div class="card-header bg-white border-slate-200">
                        <div>
                            <h3 class="card-title text-[#142C5C]">Jadwal Ujian Kelas Anda</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Pilih ujian aktif di bawah ini untuk mulai mengerjakan soal.</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-600 tabular-nums shrink-0">{{ $exams->count() }} Ujian</span>
                    </div>

                    @if($exams->isNotEmpty())
                        <div class="divide-y divide-slate-100">
                            @foreach($exams as $exam)
                                <div class="p-[22px] hover:bg-[#F5F8FF]/60 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="space-y-1.5 min-w-0">
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            <h4 class="text-[15px] font-bold text-slate-900">{{ $exam->title }}</h4>
                                            <span class="px-2 py-0.5 rounded-[6px] text-xs font-semibold bg-[#DCE9FF] text-[#142C5C] tabular-nums">
                                                {{ $exam->duration }} Menit
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-500 tabular-nums flex flex-wrap items-center gap-x-2.5 gap-y-1">
                                            <span>Mulai: {{ \Carbon\Carbon::parse($exam->start_at)->format('d M Y, H:i') }} WIB</span>
                                            @if($exam->end_at)
                                                <span class="text-slate-300">&bull;</span>
                                                <span>Batas: {{ \Carbon\Carbon::parse($exam->end_at)->format('d M Y, H:i') }} WIB</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="shrink-0">
                                        @if($exam->participant_status === 'locked')
                                            <span class="inline-flex items-center gap-[6px] px-3 py-2 rounded-[8px] bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold whitespace-nowrap">
                                                <svg class="w-[14px] h-[14px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                                <span>Terkunci — Hubungi PIC</span>
                                            </span>
                                        @elseif($exam->participant_status === 'finished')
                                            <span class="inline-flex items-center gap-[6px] px-3 py-2 rounded-[8px] bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold whitespace-nowrap">
                                                <svg class="w-[14px] h-[14px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span>Sudah Selesai</span>
                                            </span>
                                        @elseif(now()->lt($exam->start_at))
                                            <span class="inline-flex items-center px-3 py-2 rounded-[8px] bg-slate-100 text-slate-600 text-xs font-semibold whitespace-nowrap">
                                                Belum Dimulai
                                            </span>
                                        @else
                                            <a href="{{ route('web-exam.exam', $exam) }}" class="h-10 px-5 inline-flex items-center justify-center gap-[6px] bg-[#2457E8] hover:bg-blue-700 text-white rounded-[8px] text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98] whitespace-nowrap">
                                                <span>{{ $exam->participant_status === 'working' ? 'Lanjutkan Ujian' : 'Mulai Ujian' }}</span>
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
