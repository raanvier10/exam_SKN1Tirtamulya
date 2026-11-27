<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Ujian Web — ExaSatrya</title>
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
            background-color: #FFFFFF;
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
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05), 0 1px 2px rgba(15, 23, 42, 0.03);
            transition: box-shadow 150ms cubic-bezier(0.22, 1, 0.36, 1), border-color 150ms ease;
        }
        .card:hover {
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06), 0 1px 3px rgba(15, 23, 42, 0.04);
        }
        .card-header {
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #E2E8F0;
        }
        .card-body {
            padding: 16px;
        }
        .stat-card {
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05), 0 1px 2px rgba(15, 23, 42, 0.03);
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }
        @media (min-width: 640px) {
            .card-header { padding: 16px 24px; }
            .card-body { padding: 24px; }
            .stat-card { padding: 16px; }
        }
        .card-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .table-kv table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-kv td {
            padding: 12px 16px;
            font-size: 13px;
            vertical-align: top;
        }
        @media (max-width: 639px) {
            .table-kv tr {
                display: flex;
                flex-direction: column;
                gap: 2px;
                padding: 12px 16px;
            }
            .table-kv td {
                padding: 0;
                width: auto !important;
            }
        }
        @media (min-width: 640px) {
            .table-kv td {
                padding: 14px 22px;
            }
        }
        @keyframes panelEnter {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .panel-animate {
            animation: panelEnter 180ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        @media (prefers-reduced-motion: reduce) {
            .panel-animate { animation: none; }
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
        @media (max-width: 1023px) {
            body.keyboard-open #portalFooter,
            body.keyboard-open #portalInfoFab,
            body.keyboard-open #studentMobileNav {
                display: none !important;
            }
        }
    </style>
</head>
<body class="min-h-[100dvh] {{ !$verified ? 'lg:h-[100dvh] lg:overflow-hidden bg-white' : 'bg-[#F8FAFC]' }} antialiased flex flex-col overflow-x-hidden selection:bg-[#142C5C] selection:text-white">

    <!-- Top Navbar -->
    <header class="{{ $verified ? 'bg-[#142C5C] text-white border-b border-white/10' : 'bg-white border-b border-slate-200' }} sticky top-0 z-30 shrink-0">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-8 h-14 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('images/logo.webp') }}" alt="Logo SMKN 1 Tirtamulya" class="w-8 h-8 object-contain shrink-0 {{ $verified ? 'rounded-full bg-white p-0.5' : '' }}">
                <span class="text-[18px] sm:text-[20px] font-extrabold {{ $verified ? 'text-white' : 'text-[#142C5C]' }} tracking-tight leading-none">ExaSatrya</span>
            </div>

            @if($verified)
                <!-- Desktop Navigation Tabs -->
                <nav class="hidden sm:flex items-center gap-1 p-1 rounded-[10px] bg-white/10 border border-white/15" aria-label="Navigasi Portal Siswa">
                    <button type="button" onclick="switchStudentTab('dashboard')" data-student-tab="dashboard" class="student-nav-tab px-4 py-1.5 rounded-[7px] text-xs font-bold transition cursor-pointer bg-white text-[#142C5C]">
                        Dashboard
                    </button>
                    <button type="button" onclick="switchStudentTab('jadwal')" data-student-tab="jadwal" class="student-nav-tab px-4 py-1.5 rounded-[7px] text-xs font-semibold transition cursor-pointer text-slate-200 hover:text-white">
                        Jadwal
                    </button>
                    <button type="button" onclick="switchStudentTab('profile')" data-student-tab="profile" class="student-nav-tab px-4 py-1.5 rounded-[7px] text-xs font-semibold transition cursor-pointer text-slate-200 hover:text-white">
                        Profile
                    </button>
                </nav>
            @endif

            <div class="flex items-center gap-3.5 shrink-0">
                <div class="{{ $verified ? 'hidden md:block' : 'block' }} text-right min-w-0">
                    <div class="text-[12.5px] font-bold {{ $verified ? 'text-white' : 'text-[#142C5C]' }} leading-tight">Portal Ujian Web</div>
                    <div class="text-[11px] {{ $verified ? 'text-slate-300' : 'text-slate-500' }} font-medium leading-tight truncate">SMKN 1 Tirtamulya</div>
                </div>

                @if($verified)
                    <span class="hidden md:block h-6 w-px bg-white/15 shrink-0" aria-hidden="true"></span>
                    <form id="portalLogoutForm" action="{{ route('portal.logout') }}" method="POST" class="m-0 shrink-0">
                        @csrf
                        <button type="button" onclick="openLogoutModal()" class="min-h-[40px] sm:min-h-[44px] px-3.5 inline-flex items-center gap-2 rounded-[10px] border border-white/15 bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition cursor-pointer whitespace-nowrap">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </header>

    @if($verified)
        <!-- Unified Native Navy Header Band -->
        <div class="bg-[#142C5C] text-white pt-3 pb-14 sm:pt-4 sm:pb-16 rounded-b-[24px] shrink-0">
            <div class="max-w-[1280px] mx-auto px-4 sm:px-8 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-[12px] bg-white/12 border border-white/20 text-white flex items-center justify-center font-extrabold text-sm shrink-0">
                        {{ strtoupper(substr($user->name ?? 'S', 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-lg sm:text-xl font-extrabold tracking-tight leading-snug text-white truncate">{{ $user->name }}</h1>
                        <div class="text-xs text-slate-200 tabular-nums mt-0.5 truncate">
                            NIS {{ $user->username }} &mdash; {{ $user->class?->name ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="text-right shrink-0">
                    <div id="dashboardClock" class="text-base sm:text-xl font-extrabold text-white tabular-nums leading-tight">
                        {{ \Carbon\Carbon::now()->format('H:i:s') }} WIB
                    </div>
                    <div class="text-[11px] font-medium text-slate-300 tabular-nums mt-0.5">
                        {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d M Y') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <main class="{{ $verified ? 'max-w-[1280px] -mt-9 sm:-mt-10 pb-24 sm:pb-10 relative z-10' : 'max-w-[1040px] py-2 sm:py-4 lg:overflow-y-auto justify-center' }} mx-auto px-4 sm:px-8 w-full flex-1 flex flex-col">
        @if(session('success'))
            <div class="mb-4 sm:mb-[22px] px-4 py-3.5 rounded-[12px] bg-white border border-slate-200 border-l-4 border-l-[#142C5C] text-slate-800 text-xs sm:text-sm font-medium max-w-4xl mx-auto w-full">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 sm:mb-[22px] px-4 py-3.5 rounded-[12px] bg-white border border-rose-200 border-l-4 border-l-rose-600 text-rose-800 text-xs sm:text-sm font-medium max-w-4xl mx-auto w-full">
                {{ session('error') }}
            </div>
        @endif

        @if(!$verified)
            @php
                $portalHighlights = [
                    [
                        'title' => 'Ujian Online Terproteksi',
                        'desc' => 'Pengerjaan ujian melalui browser tanpa instalasi aplikasi tambahan.',
                        'icon' => '<rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
                    ],
                    [
                        'title' => 'Aman dan Terkontrol',
                        'desc' => 'Mode layar penuh dan sesi ujian diawasi oleh pengawas.',
                        'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>',
                    ],
                    [
                        'title' => 'Jadwal Terkini',
                        'desc' => 'Lihat jadwal dan informasi ujian langsung melalui portal ini.',
                        'icon' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
                    ],
                ];
            @endphp

            <!-- State 1: Centered Hero + Symmetric Split Portal Login -->
            <div class="text-center mb-4 sm:mb-6">
                <div class="text-[11px] sm:text-[13px] font-bold uppercase tracking-[0.16em] text-[#142C5C]/75 mb-1 sm:mb-1.5">
                    Portal Ujian Resmi Siswa
                </div>
                <h1 class="text-[34px] sm:text-[48px] font-extrabold text-[#142C5C] tracking-tight leading-[1.04] mb-1.5 sm:mb-2.5">
                    ExaSatrya
                </h1>
                <p class="text-[13px] sm:text-[15.5px] text-slate-500 max-w-lg mx-auto leading-snug sm:leading-relaxed">
                    Sistem ujian berbasis web untuk pelaksanaan asesmen secara aman, tertib, dan terstandar.
                </p>
                <div class="w-10 sm:w-12 h-[3px] sm:h-[3.5px] bg-[#142C5C] rounded-full mx-auto mt-2.5 sm:mt-3.5"></div>
            </div>

            <div class="flex flex-col lg:flex-row items-center justify-center gap-6 lg:gap-9 max-w-[860px] mx-auto w-full">
                <!-- Left Highlights (Desktop only; accessible via floating button on mobile) -->
                <div class="hidden lg:block w-full lg:w-[360px] shrink-0 space-y-5">
                    @foreach($portalHighlights as $item)
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
                            <h3 class="text-[17px] sm:text-[18px] font-extrabold text-[#142C5C] tracking-tight leading-snug">Masuk Peserta Ujian</h3>
                            <p class="text-[12px] sm:text-[12.5px] text-slate-500 mt-0.5">
                                Gunakan NIS dan password siswa Anda untuk melihat jadwal ujian.
                            </p>
                        </div>

                        <form action="{{ route('portal.login') }}" method="POST" class="space-y-2.5 sm:space-y-3">
                            @csrf
                            <div>
                                <label for="username" class="block text-xs font-bold text-[#142C5C] mb-1">NIS / Username Siswa</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                        </svg>
                                    </span>
                                    <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                                           placeholder="Masukkan NIS siswa"
                                           class="w-full h-10 pl-10 pr-3.5 rounded-[8px] border border-slate-200 bg-slate-50/70 focus:bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10 tabular-nums">
                                </div>
                                @error('username')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="password" class="block text-xs font-bold text-[#142C5C] mb-1">Password Siswa</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                    </span>
                                    <input type="password" id="password" name="password" required autocomplete="current-password"
                                           placeholder="Masukkan password akun"
                                           class="w-full h-10 pl-10 pr-11 rounded-[8px] border border-slate-200 bg-slate-50/70 focus:bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10">
                                    <button type="button" onclick="const i = document.getElementById('password'); i.type = i.type === 'password' ? 'text' : 'password';" class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer" title="Lihat/Sembunyikan Password" aria-label="Lihat atau sembunyikan password">
                                        <svg class="w-[16px] h-[16px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div class="pt-1">
                                <button type="submit" class="w-full h-10 bg-[#142C5C] hover:bg-[#0D2149] text-white rounded-[8px] text-[13.5px] font-bold inline-flex items-center justify-center transition active:scale-[0.99] cursor-pointer">
                                    <span>Masuk &amp; Lihat Jadwal Ujian</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        @else
            @php
                $hasPwErrors = $errors->has('current_password') || $errors->has('password');
                $dashboardExams = $todayExams->isNotEmpty() ? $todayExams : $exams;
                $showingToday = $todayExams->isNotEmpty();
                $nextUpcomingExam = $exams->filter(fn ($e) => $e->start_at && $e->start_at->gt(now()) && !$dashboardExams->contains('id', $e->id))->first();
            @endphp

            <!-- PANEL 1: DASHBOARD -->
            <section id="studentPanel-dashboard" class="panel-animate space-y-4 sm:space-y-6 w-full">
                <!-- Unified 3-Column Summary Strip -->
                <div class="card bg-white grid grid-cols-3 divide-x divide-slate-200/80 shadow-[0_6px_20px_rgba(15,23,42,0.06)]">
                    <div class="py-3.5 px-2 sm:py-4 sm:px-5 text-center">
                        <div class="text-xl sm:text-2xl font-extrabold text-[#142C5C] tabular-nums leading-none">{{ $stats['today'] }}</div>
                        <div class="mt-1.5 text-[10.5px] sm:text-xs font-semibold text-slate-600 leading-tight">Ujian Hari Ini</div>
                    </div>
                    <div class="py-3.5 px-2 sm:py-4 sm:px-5 text-center">
                        <div class="text-xl sm:text-2xl font-extrabold text-[#142C5C] tabular-nums leading-none">{{ $stats['upcoming'] }}</div>
                        <div class="mt-1.5 text-[10.5px] sm:text-xs font-semibold text-slate-600 leading-tight">Belum Selesai</div>
                    </div>
                    <div class="py-3.5 px-2 sm:py-4 sm:px-5 text-center">
                        <div class="text-xl sm:text-2xl font-extrabold text-[#142C5C] tabular-nums leading-none">{{ $stats['finished'] }}</div>
                        <div class="mt-1.5 text-[10.5px] sm:text-xs font-semibold text-slate-600 leading-tight">Sudah Selesai</div>
                    </div>
                </div>

                <!-- Today / Next Active Exams -->
                <div class="card overflow-hidden">
                    <div class="p-4 sm:px-6 sm:py-4 border-b border-slate-200 bg-white">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-[15px] font-bold text-[#142C5C] tracking-[-0.01em]">
                                {{ $showingToday ? 'Ujian Hari Ini' : 'Ujian Aktif & Terdekat' }}
                            </h3>
                            <button type="button" onclick="switchStudentTab('jadwal')" class="min-h-[44px] inline-flex items-center text-xs font-bold text-[#142C5C] hover:underline cursor-pointer shrink-0">
                                Semua Jadwal &rarr;
                            </button>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5 leading-snug">
                            {{ $showingToday ? 'Daftar ujian yang dijadwalkan atau sedang berlangsung hari ini.' : 'Tidak ada jadwal khusus hari ini; berikut jadwal aktif terdekat kelas Anda.' }}
                        </p>
                    </div>

                    @php
                        $runningExams = $dashboardExams->filter(function ($e) {
                            $expired = $e->end_at && now()->gt($e->end_at);
                            $upcoming = now()->lt($e->start_at);
                            return !in_array($e->participant_status, ['locked', 'finished'], true) && !$expired && !$upcoming;
                        })->values();

                        $upcomingExams = $dashboardExams->filter(function ($e) {
                            $expired = $e->end_at && now()->gt($e->end_at);
                            return !in_array($e->participant_status, ['locked', 'finished'], true) && !$expired && now()->lt($e->start_at);
                        })->values();

                        $doneExams = $dashboardExams->filter(function ($e) {
                            $expired = $e->end_at && now()->gt($e->end_at);
                            return in_array($e->participant_status, ['locked', 'finished'], true) || $expired;
                        })->values();
                    @endphp

                    @if($dashboardExams->isNotEmpty())
                        <div class="p-4 sm:p-6 space-y-5">
                            @if($runningExams->isEmpty())
                                <p class="text-[13px] text-slate-500">Belum ada ujian yang berlangsung</p>
                            @else
                                <div class="space-y-2.5">
                                    <div class="text-[11px] font-bold uppercase tracking-[0.06em] text-slate-500">Sedang Berlangsung</div>
                                    <div class="space-y-3">
                                        @foreach($runningExams as $exam)
                                            <x-portal.exam-item :exam="$exam" :time-only="$showingToday" />
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($upcomingExams->isNotEmpty())
                                <div class="space-y-2.5">
                                    <div class="text-[11px] font-bold uppercase tracking-[0.06em] text-slate-500">Akan Datang</div>
                                    <div class="space-y-3">
                                        @foreach($upcomingExams as $exam)
                                            <x-portal.exam-item :exam="$exam" :time-only="$showingToday" />
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($doneExams->isNotEmpty())
                                <div class="space-y-2.5">
                                    <div class="text-[11px] font-bold uppercase tracking-[0.06em] text-slate-500">Selesai</div>
                                    <div class="space-y-3">
                                        @foreach($doneExams as $exam)
                                            <x-portal.exam-item :exam="$exam" :time-only="$showingToday" />
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-6 text-center space-y-1.5">
                            <p class="text-[13px] text-slate-500">Belum ada ujian yang berlangsung</p>
                            <p class="text-sm font-bold text-[#142C5C]">Tidak ada ujian dijadwalkan hari ini</p>
                            <p class="text-xs text-slate-600">Semua agenda minggu ini tersedia pada halaman Jadwal.</p>
                        </div>
                    @endif

                    @if($dashboardExams->count() <= 1)
                        <div class="px-4 py-3 sm:px-6 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between gap-3 text-xs text-slate-600">
                            @if($nextUpcomingExam)
                                <span class="min-w-0 truncate">
                                    <span class="font-semibold text-[#142C5C]">Ujian berikutnya:</span>
                                    {{ $nextUpcomingExam->title }}
                                </span>
                                <span class="tabular-nums shrink-0 font-medium text-slate-600">
                                    {{ \Carbon\Carbon::parse($nextUpcomingExam->start_at)->locale('id')->translatedFormat('d M, H:i') }} WIB
                                </span>
                            @else
                                <span>Tidak ada ujian lain hari ini.</span>
                                <span class="tabular-nums shrink-0 font-medium text-slate-500">{{ $weekRangeLabel }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </section>

            <!-- PANEL 2: JADWAL (SENIN - JUMAT) -->
            <section id="studentPanel-jadwal" class="panel-animate hidden space-y-4 sm:space-y-[22px] w-full">
                <div class="card card-body flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-[#142C5C] tracking-tight">Jadwal Ujian Mingguan (Senin &ndash; Jumat)</h2>
                        <p class="text-xs text-slate-500 mt-0.5 tabular-nums">Periode minggu ini: {{ $weekRangeLabel }}</p>
                    </div>
                    <span class="text-xs font-bold text-[#142C5C] shrink-0">
                        Kelas {{ $user->class?->name ?? '-' }}
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach($weeklyDays as $day)
                        @php
                            $dayId = 'jadwal-day-' . $day['iso_day'];
                            $isOpen = (bool) $day['is_today'];
                        @endphp
                        <div class="card overflow-hidden {{ $day['is_today'] ? 'border-[#142C5C]/40' : '' }}">
                            <button type="button"
                                    onclick="toggleJadwalAccordion('{{ $dayId }}')"
                                    aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                                    aria-controls="{{ $dayId }}"
                                    data-accordion-trigger="{{ $dayId }}"
                                    class="w-full min-h-[48px] text-left p-4 sm:px-6 sm:py-4 flex items-center justify-between gap-3 cursor-pointer transition-colors {{ $day['is_today'] ? 'bg-[#142C5C] text-white' : 'bg-white hover:bg-slate-50/80 text-[#142C5C]' }} {{ $isOpen ? ($day['is_today'] ? 'border-b border-[#0D2149]' : 'border-b border-slate-200') : '' }}">
                                <div class="flex items-baseline gap-2.5 flex-wrap min-w-0">
                                    <h3 class="card-title {{ $day['is_today'] ? 'text-white' : 'text-[#142C5C]' }}">{{ $day['label'] }}</h3>
                                    @if($day['is_today'])
                                        <span class="text-xs font-medium text-slate-200">Hari Ini</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2.5 sm:gap-3 shrink-0 tabular-nums">
                                    <span class="text-xs font-semibold {{ $day['is_today'] ? 'text-slate-200' : 'text-slate-500' }}">
                                        {{ $day['date']->copy()->locale('id')->translatedFormat('d M Y') }}
                                    </span>
                                    <span class="{{ $day['is_today'] ? 'text-white/30' : 'text-slate-300' }} text-xs" aria-hidden="true">|</span>
                                    <span class="text-xs font-semibold {{ $day['is_today'] ? 'text-white' : 'text-[#142C5C]' }}">
                                        {{ $day['exams']->count() }} Ujian
                                    </span>
                                    <svg data-accordion-icon="{{ $dayId }}" class="w-4 h-4 shrink-0 transition-transform duration-200 {{ $day['is_today'] ? 'text-white' : 'text-slate-500' }} {{ $isOpen ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <polyline points="6 9 12 15 18 9"/>
                                    </svg>
                                </div>
                            </button>

                            <div id="{{ $dayId }}" class="{{ $isOpen ? '' : 'hidden' }}">
                                @if($day['exams']->isNotEmpty())
                                    <div class="p-4 sm:p-6 space-y-3">
                                        @foreach($day['exams'] as $exam)
                                            <x-portal.exam-item :exam="$exam" :time-only="true" />
                                        @endforeach
                                    </div>
                                @else
                                    <div class="px-4 sm:px-6 py-4 text-xs text-slate-500 font-medium">
                                        Tidak ada jadwal ujian pada hari {{ $day['label'] }}.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if($otherExams->isNotEmpty())
                        <div class="card overflow-hidden">
                            <button type="button"
                                    onclick="toggleJadwalAccordion('jadwal-day-other')"
                                    aria-expanded="false"
                                    aria-controls="jadwal-day-other"
                                    data-accordion-trigger="jadwal-day-other"
                                    class="w-full min-h-[48px] text-left p-4 sm:px-6 sm:py-4 flex items-center justify-between gap-3 cursor-pointer bg-white hover:bg-slate-50/80 transition-colors">
                                <h3 class="card-title text-[#142C5C]">Jadwal Lainnya / Minggu Mendatang</h3>
                                <div class="flex items-center gap-2.5 sm:gap-3 shrink-0 tabular-nums">
                                    <span class="text-xs font-semibold text-[#142C5C]">{{ $otherExams->count() }} Ujian</span>
                                    <svg data-accordion-icon="jadwal-day-other" class="w-4 h-4 shrink-0 transition-transform duration-200 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <polyline points="6 9 12 15 18 9"/>
                                    </svg>
                                </div>
                            </button>
                            <div id="jadwal-day-other" class="hidden p-4 sm:p-6 space-y-3">
                                @foreach($otherExams as $exam)
                                    <x-portal.exam-item :exam="$exam" />
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <!-- PANEL 3: PROFILE -->
            <section id="studentPanel-profile" class="panel-animate hidden w-full">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-[22px] items-start w-full">
                    <!-- Student Identity Card -->
                    <div class="card overflow-hidden lg:col-span-5">
                    <div class="card-header bg-white border-slate-200">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-[10px] bg-[#142C5C] text-white flex items-center justify-center font-extrabold text-sm shrink-0">
                                {{ strtoupper(substr($user->name ?? 'S', 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <h2 class="card-title text-[#142C5C]">Profil Peserta Ujian</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Informasi akun siswa terdaftar pada sistem ujian.</p>
                            </div>
                        </div>
                    </div>

                    <div class="table-wrap table-kv">
                        <table>
                            <tbody class="divide-y divide-slate-100">
                                <tr>
                                    <td class="font-semibold text-slate-500 w-44">Nama Lengkap</td>
                                    <td class="font-bold text-[#142C5C]">{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-slate-500">NIS / Username</td>
                                    <td class="font-bold text-slate-900 tabular-nums">{{ $user->username }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-slate-500">Kelas</td>
                                    <td class="font-semibold text-slate-800">{{ $user->class?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-slate-500">Aturan Sesi</td>
                                    <td class="text-slate-600">Mode layar penuh wajib aktif; pindah tab atau keluar layar penuh tercatat otomatis ke pengawas ruang.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                    <!-- Change Password Card -->
                    <div class="card overflow-hidden lg:col-span-7">
                        <div class="card-header bg-white border-slate-200">
                            <div>
                                <h3 class="card-title text-[#142C5C]">Ganti Password Akun</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Gunakan password baru minimal 6 karakter tanpa spasi.</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('portal.change-password') }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                                    <div>
                                        <label for="current_password" class="block text-xs font-bold text-[#142C5C] mb-1.5">Password Lama</label>
                                        <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                                               class="w-full h-10 px-3 rounded-[8px] border border-slate-200 bg-slate-50/70 focus:bg-white text-sm text-slate-900 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10">
                                        @error('current_password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="new_password" class="block text-xs font-bold text-[#142C5C] mb-1.5">Password Baru</label>
                                        <input type="password" id="new_password" name="password" required minlength="6" autocomplete="new-password"
                                               class="w-full h-10 px-3 rounded-[8px] border border-slate-200 bg-slate-50/70 focus:bg-white text-sm text-slate-900 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10">
                                        @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="new_password_confirmation" class="block text-xs font-bold text-[#142C5C] mb-1.5">Konfirmasi Password Baru</label>
                                        <input type="password" id="new_password_confirmation" name="password_confirmation" required minlength="6" autocomplete="new-password"
                                               class="w-full h-10 px-3 rounded-[8px] border border-slate-200 bg-slate-50/70 focus:bg-white text-sm text-slate-900 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10">
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="w-full sm:w-auto h-11 sm:h-10 px-5 bg-[#142C5C] hover:bg-[#0D2149] text-white rounded-[8px] text-xs font-bold transition active:scale-[0.98] cursor-pointer">
                                        Simpan Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Mobile Bottom Navigation Bar -->
            <nav id="studentMobileNav" class="sm:hidden fixed bottom-0 inset-x-0 z-30 bg-white/95 backdrop-blur-md border-t border-slate-200 grid grid-cols-3 h-16 pb-[env(safe-area-inset-bottom)]" aria-label="Navigasi Mobile Siswa">
                <button type="button" onclick="switchStudentTab('dashboard')" data-student-tab-mobile="dashboard" class="student-mobile-tab min-h-[44px] relative flex flex-col items-center justify-center gap-1 text-[11px] font-bold text-[#142C5C] cursor-pointer transition-colors">
                    <span class="mobile-tab-bar absolute top-0 w-10 h-[3px] bg-[#142C5C]"></span>
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <span>Dashboard</span>
                </button>
                <button type="button" onclick="switchStudentTab('jadwal')" data-student-tab-mobile="jadwal" class="student-mobile-tab min-h-[44px] relative flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-600 cursor-pointer transition-colors">
                    <span class="mobile-tab-bar hidden absolute top-0 w-10 h-[3px] bg-[#142C5C]"></span>
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span>Jadwal</span>
                </button>
                <button type="button" onclick="switchStudentTab('profile')" data-student-tab-mobile="profile" class="student-mobile-tab min-h-[44px] relative flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-600 cursor-pointer transition-colors">
                    <span class="mobile-tab-bar hidden absolute top-0 w-10 h-[3px] bg-[#142C5C]"></span>
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Profile</span>
                </button>
            </nav>

            <!-- Logout Confirmation Modal -->
            <div id="portalLogoutModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-[2px] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle" onclick="if (event.target === this) closeLogoutModal()">
                <div class="w-full max-w-sm bg-white border border-slate-200 rounded-[16px] p-5 shadow-xl space-y-4">
                    <div class="space-y-1.5">
                        <h3 id="logoutModalTitle" class="text-base font-extrabold text-[#142C5C] tracking-tight">Keluar dari Portal?</h3>
                        <p class="text-xs sm:text-[13px] text-slate-600 leading-relaxed">
                            Sesi akun Anda akan diakhiri. Anda perlu login kembali menggunakan NIS dan password untuk membuka jadwal ujian.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <button type="button" onclick="closeLogoutModal()" class="min-h-[44px] px-4 rounded-[12px] border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="button" onclick="document.getElementById('portalLogoutForm').submit()" class="min-h-[44px] px-4 rounded-[12px] bg-[#142C5C] hover:bg-[#0D2149] text-white text-xs font-bold transition active:scale-[0.98] cursor-pointer">
                            Ya, Keluar
                        </button>
                    </div>
                </div>
            </div>

            <script>
                function toggleJadwalAccordion(id) {
                    const body = document.getElementById(id);
                    const trigger = document.querySelector('[data-accordion-trigger="' + id + '"]');
                    const icon = document.querySelector('[data-accordion-icon="' + id + '"]');
                    if (!body || !trigger) return;

                    const willOpen = body.classList.contains('hidden');
                    body.classList.toggle('hidden', !willOpen);
                    trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

                    const isDark = trigger.classList.contains('bg-[#142C5C]');
                    trigger.classList.toggle('border-b', willOpen);
                    trigger.classList.toggle(isDark ? 'border-[#0D2149]' : 'border-slate-200', willOpen);
                    if (icon) icon.classList.toggle('rotate-180', willOpen);
                }

                function openLogoutModal() {
                    const modal = document.getElementById('portalLogoutModal');
                    if (modal) modal.classList.remove('hidden');
                }

                function closeLogoutModal() {
                    const modal = document.getElementById('portalLogoutModal');
                    if (modal) modal.classList.add('hidden');
                }

                function updateDashboardClock() {
                    const el = document.getElementById('dashboardClock');
                    if (!el) return;
                    const now = new Date();
                    const parts = new Intl.DateTimeFormat('en-GB', {
                        timeZone: 'Asia/Jakarta',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false
                    }).format(now);
                    el.textContent = parts + ' WIB';
                }

                function switchStudentTab(tab) {
                    const valid = ['dashboard', 'jadwal', 'profile'];
                    const target = valid.includes(tab) ? tab : 'dashboard';

                    valid.forEach(function (name) {
                        const panel = document.getElementById('studentPanel-' + name);
                        if (panel) panel.classList.toggle('hidden', name !== target);
                    });

                    document.querySelectorAll('.student-nav-tab').forEach(function (btn) {
                        const active = btn.getAttribute('data-student-tab') === target;
                        btn.className = active
                            ? 'student-nav-tab px-4 py-1.5 rounded-[7px] text-xs font-bold transition cursor-pointer bg-white text-[#142C5C]'
                            : 'student-nav-tab px-4 py-1.5 rounded-[7px] text-xs font-semibold transition cursor-pointer text-slate-200 hover:text-white';
                    });

                    document.querySelectorAll('.student-mobile-tab').forEach(function (btn) {
                        const active = btn.getAttribute('data-student-tab-mobile') === target;
                        btn.className = active
                            ? 'student-mobile-tab min-h-[44px] relative flex flex-col items-center justify-center gap-1 text-[11px] font-bold text-[#142C5C] cursor-pointer transition-colors'
                            : 'student-mobile-tab min-h-[44px] relative flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-600 cursor-pointer transition-colors';

                        const bar = btn.querySelector('.mobile-tab-bar');
                        if (bar) bar.classList.toggle('hidden', !active);
                    });

                    if (history.replaceState) {
                        history.replaceState(null, '', '#' + target);
                    }
                }

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') closeLogoutModal();
                });

                document.addEventListener('DOMContentLoaded', function () {
                    updateDashboardClock();
                    setInterval(updateDashboardClock, 1000);

                    const hasPwErrors = @json($hasPwErrors);
                    const hash = (window.location.hash || '').replace('#', '');
                    if (hasPwErrors) {
                        switchStudentTab('profile');
                    } else if (['dashboard', 'jadwal', 'profile'].includes(hash)) {
                        switchStudentTab(hash);
                    } else {
                        switchStudentTab('dashboard');
                    }
                });
            </script>
        @endif
    </main>

    @if(!$verified)
        <!-- Mobile Floating Info Button & Modal -->
        <button type="button"
                id="portalInfoFab"
                onclick="document.getElementById('portalInfoModal').classList.remove('hidden')"
                aria-label="Informasi Portal Ujian"
                title="Informasi Portal Ujian"
                class="lg:hidden fixed bottom-12 right-4 z-40 w-11 h-11 rounded-full bg-[#142C5C] hover:bg-[#0D2149] text-white shadow-lg border border-white/25 flex items-center justify-center transition active:scale-95 cursor-pointer">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
        </button>

        <script>
            (function () {
                const fab = document.getElementById('portalInfoFab');
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

        <div id="portalInfoModal" class="hidden lg:hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-[2px] flex items-center justify-center p-4" onclick="if (event.target === this) this.classList.add('hidden')">
            <div class="w-full max-w-md max-h-[85dvh] overflow-y-auto bg-white border border-slate-200 rounded-[16px] p-5 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-[15px] font-bold text-[#142C5C] tracking-[-0.01em]">Informasi Portal Ujian</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Ketentuan pelaksanaan ujian berbasis web</p>
                    </div>
                    <button type="button" onclick="document.getElementById('portalInfoModal').classList.add('hidden')" aria-label="Tutup informasi" class="w-8 h-8 rounded-[8px] border border-slate-200 text-slate-500 hover:text-[#142C5C] flex items-center justify-center cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    @foreach($portalHighlights as $item)
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

                <button type="button" onclick="document.getElementById('portalInfoModal').classList.add('hidden')" class="w-full h-10 bg-[#142C5C] hover:bg-[#0D2149] text-white rounded-[8px] text-xs font-bold transition cursor-pointer">
                    Mengerti
                </button>
            </div>
        </div>
    @endif

    <footer id="portalFooter" class="hidden sm:block bg-[#142C5C] text-slate-200 py-2.5 px-4 text-center text-[11.5px] sm:text-xs font-medium shrink-0">
        &copy; {{ date('Y') }} SMKN 1 Tirtamulya &nbsp;|&nbsp; ExaSatrya &ndash; Sistem Ujian Terproteksi
    </footer>

    <script>
        (function () {
            const isFormField = (el) => !!el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT');
            const syncKeyboardState = (e) => {
                const viewportShrunk = window.visualViewport ? window.visualViewport.height < window.innerHeight * 0.78 : false;
                const activeTyping = isFormField(e && e.type === 'focusin' ? e.target : document.activeElement);
                document.body.classList.toggle('keyboard-open', activeTyping || viewportShrunk);
            };
            document.addEventListener('DOMContentLoaded', syncKeyboardState);
            document.addEventListener('focusin', syncKeyboardState);
            document.addEventListener('focusout', () => setTimeout(() => syncKeyboardState(), 120));
            if (window.visualViewport) {
                window.visualViewport.addEventListener('resize', () => syncKeyboardState());
            }
        })();
    </script>
</body>
</html>
