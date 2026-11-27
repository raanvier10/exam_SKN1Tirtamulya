<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ExaSatrya Admin')</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 14px;
            line-height: 1.55;
            background-color: #F8FAFC;
            color: #0F172A;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            letter-spacing: -0.02em;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        code, pre {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
            font-variant-numeric: tabular-nums;
        }

        /* Flex & Grid overflow guard */
        .flex > *,
        .grid > * {
            min-width: 0;
        }

        /* Card & Stat Card Design System */
        .card {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.03);
            margin-bottom: 22px;
            transition: box-shadow 150ms ease, border-color 150ms ease;
        }
        .card:hover {
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06), 0 2px 4px rgba(15, 23, 42, 0.04);
        }
        .card-header {
            padding: 18px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #E2E8F0;
        }
        .card-header h3,
        .card-header h4,
        .card-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.01em;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .card-body {
            padding: 22px;
        }
        .card-body-flush {
            padding: 0;
        }
        .card-footer {
            padding: 14px 22px;
            border-top: 1px solid #E2E8F0;
        }
        .stat-card {
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 20px 22px;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.03);
            transition: box-shadow 150ms ease, border-color 150ms ease;
        }
        .stat-card:hover {
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06), 0 2px 4px rgba(15, 23, 42, 0.04);
        }

        /* Table Standardization */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }
        .table-wrap table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .table-wrap th {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 13px 18px;
            white-space: nowrap;
            border-bottom: 1px solid #E2E8F0;
        }
        .table-wrap td {
            font-size: 13.5px;
            padding: 15px 18px;
            vertical-align: middle;
            border-bottom: 1px solid #F1F5F9;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .table-wrap.table-kv td {
            font-size: 13px;
            line-height: 1.5;
        }

        /* Consistent Outline SVG Icons */
        svg[stroke="currentColor"]:not([data-decorative]) {
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }

        dialog[open] {
            animation: modalScaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        dialog::backdrop {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            animation: backdropFadeIn 0.2s ease-out forwards;
        }
        @keyframes modalScaleIn {
            from { opacity: 0; transform: scale(0.96) translateY(4px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        @keyframes backdropFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Smooth Accordion in Sidebar */
        .accordion-menu {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease-out;
            opacity: 0;
        }
        .accordion-menu.expanded {
            grid-template-rows: 1fr;
            opacity: 1;
        }
        .accordion-menu-inner {
            overflow: hidden;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex selection:bg-[#142C5C] selection:text-white bg-[#F8FAFC]">

    <!-- Sidebar Backdrop for Mobile -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 lg:hidden hidden transition-opacity duration-300" onclick="toggleSidebar(false)"></div>

    <!-- Navy Sidebar matching Portal Brand -->
    <aside id="adminSidebar" class="w-[270px] bg-[#142C5C] text-white flex flex-col fixed inset-y-0 left-0 h-[100dvh] lg:sticky lg:top-0 lg:w-[270px] lg:h-[100dvh] lg:shrink-0 z-50 border-r border-[#0D2149] transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0">
        <div class="px-5 py-5 border-b border-white/10 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-[8px] bg-white p-1 flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/logo.webp') }}" class="w-full h-full object-contain" alt="Logo">
                </div>
                <div class="min-w-0">
                    <h1 class="text-base font-extrabold tracking-tight text-white leading-none">ExaSatrya</h1>
                    <span class="text-[11px] font-medium text-blue-200">SMKN 1 Tirtamulya</span>
                </div>
            </div>
            <!-- Mobile Close Button -->
            <button type="button" onclick="toggleSidebar(false)" class="lg:hidden p-1.5 rounded-[8px] text-blue-200 hover:text-white hover:bg-white/10 transition-colors shrink-0" aria-label="Tutup Menu">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <div class="px-3 pb-2 text-[11px] font-medium uppercase tracking-[0.08em] text-white/50">Utama</div>

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-[8px] text-sm transition-colors duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-white text-[#142C5C] font-bold shadow-xs' : 'font-medium text-blue-100/85 hover:text-white hover:bg-white/10' }}">
                <svg class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-[#142C5C]' : 'text-blue-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.classes.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-[8px] text-sm transition-colors duration-150 {{ request()->routeIs('admin.classes.*') ? 'bg-white text-[#142C5C] font-bold shadow-xs' : 'font-medium text-blue-100/85 hover:text-white hover:bg-white/10' }}">
                <svg class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('admin.classes.*') ? 'text-[#142C5C]' : 'text-blue-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Master Kelas
            </a>

            <a href="{{ route('admin.students.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-[8px] text-sm transition-colors duration-150 {{ request()->routeIs('admin.students.*') ? 'bg-white text-[#142C5C] font-bold shadow-xs' : 'font-medium text-blue-100/85 hover:text-white hover:bg-white/10' }}">
                <svg class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('admin.students.*') ? 'text-[#142C5C]' : 'text-blue-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Master Siswa
            </a>

            <a href="{{ route('admin.teachers.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-[8px] text-sm transition-colors duration-150 {{ request()->routeIs('admin.teachers.*') ? 'bg-white text-[#142C5C] font-bold shadow-xs' : 'font-medium text-blue-100/85 hover:text-white hover:bg-white/10' }}">
                <svg class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('admin.teachers.*') ? 'text-[#142C5C]' : 'text-blue-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6" />
                </svg>
                Master Guru
            </a>
            @endif

            <!-- Accordion Menu: Manajemen Ujian -->
            <div class="space-y-1">
                <button type="button"
                        id="examMenuTrigger"
                        onclick="toggleExamAccordion()"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-[8px] text-sm transition-colors duration-150 group {{ request()->routeIs('admin.exams.*') ? 'bg-white text-[#142C5C] font-bold shadow-xs' : 'font-medium text-blue-100/85 hover:text-white hover:bg-white/10' }}"
                        aria-expanded="{{ request()->routeIs('admin.exams.*') ? 'true' : 'false' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('admin.exams.*') ? 'text-[#142C5C]' : 'text-blue-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span>Manajemen Ujian</span>
                    </div>
                    <svg id="examMenuChevron" class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('admin.exams.*') ? 'rotate-180 text-[#142C5C]' : 'text-blue-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Submenu Items (UTS & UAS) -->
                <div id="examSubmenu" class="accordion-menu {{ request()->routeIs('admin.exams.*') ? 'expanded' : '' }} pl-4 pr-1">
                    <div class="accordion-menu-inner space-y-1">
                        <div class="pl-3 border-l border-white/15 space-y-1 py-1">
                            @php
                                $userIsTeacher = auth()->user()?->isTeacher();
                                $defaultSubmenuMode = $userIsTeacher ? 'uts' : 'uas';
                                $routeExamType = isset($exam) ? ($exam->type ?? null) : null;
                                $currentSubmenuMode = request('mode', request('category', request('type', $routeExamType ?? $defaultSubmenuMode)));
                                $isUtsActive = request()->routeIs('admin.exams.*') && ($currentSubmenuMode === 'uts');
                                $isUasActive = request()->routeIs('admin.exams.*') && ($currentSubmenuMode === 'uas');
                            @endphp
                            <!-- UTS Submenu -->
                            <a href="{{ route('admin.exams.index', ['mode' => 'uts']) }}"
                               id="cardCategoryUts"
                               class="sidebar-exam-sublink flex items-center gap-2.5 px-3 py-2 rounded-[8px] text-[13px] font-medium transition-colors duration-150 {{ $isUtsActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isUtsActive ? 'bg-white' : 'bg-blue-200/50' }}"></span>
                                <span>UTS</span>
                            </a>

                            <!-- UAS Submenu -->
                            <a href="{{ route('admin.exams.index', ['mode' => 'uas']) }}"
                               id="cardCategoryUas"
                               class="sidebar-exam-sublink flex items-center gap-2.5 px-3 py-2 rounded-[8px] text-[13px] font-medium transition-colors duration-150 {{ $isUasActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isUasActive ? 'bg-white' : 'bg-blue-200/50' }}"></span>
                                <span>UAS</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.unlock.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-[8px] text-sm transition-colors duration-150 {{ request()->routeIs('admin.unlock.*') ? 'bg-white text-[#142C5C] font-bold shadow-xs' : 'font-medium text-blue-100/85 hover:text-white hover:bg-white/10' }}">
                <svg class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('admin.unlock.*') ? 'text-[#142C5C]' : 'text-blue-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                </svg>
                Buka Kunci
            </a>

            <a href="{{ route('admin.change-password') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-[8px] text-sm transition-colors duration-150 {{ request()->routeIs('admin.change-password*') ? 'bg-white text-[#142C5C] font-bold shadow-xs' : 'font-medium text-blue-100/85 hover:text-white hover:bg-white/10' }}">
                <svg class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('admin.change-password*') ? 'text-[#142C5C]' : 'text-blue-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Ganti Password
            </a>
        </nav>

        <!-- Bottom Profile + Logout -->
        <div class="p-3.5 border-t border-white/10">
            <div class="px-3 py-2.5 rounded-[8px] bg-white/[0.08] border border-white/10 flex items-center justify-between gap-2.5">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-white/15 border border-white/25 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] font-medium text-blue-200">
                            {{ auth()->user()->isDepartmentPic() ? 'PIC ' . auth()->user()->departmentCode() : (auth()->user()->isAdmin() ? 'Administrator' : 'Guru Pengampu') }}
                        </div>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" title="Logout" class="p-1.5 rounded-lg text-blue-200 hover:text-rose-300 hover:bg-white/10 transition-colors">
                        <svg class="w-4.5 h-4.5 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 min-w-0 min-h-screen flex flex-col">
        <!-- Top Header Bar -->
        <header class="sticky top-0 z-30 flex items-center justify-between bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-6 lg:px-[32px] py-3.5">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" onclick="toggleSidebar(true)" class="lg:hidden p-2 -ml-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-[8px] transition-colors active:scale-95 shrink-0" aria-label="Buka Menu">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h2 class="text-base sm:text-lg font-extrabold text-[#142C5C] tracking-tight min-w-0">
                    @yield('header_title', View::hasSection('title') ? html_entity_decode(trim(View::yieldContent('title')), ENT_QUOTES, 'UTF-8') : 'Dashboard Overview')
                </h2>
            </div>
        </header>

        <!-- Floating Top Toast Notifications (Zero Layout Shift) -->
        @if(session('success') || session('error'))
            <div id="adminToastStack" class="fixed top-3.5 right-4 sm:right-6 z-50 w-[calc(100%-2rem)] max-w-md space-y-2.5 pointer-events-none">
                @if(session('success'))
                    <div role="status" class="admin-toast pointer-events-auto bg-white/95 backdrop-blur-md border border-emerald-200/90 rounded-[12px] p-3.5 pl-4 shadow-[0_12px_32px_-8px_rgba(13,33,73,0.18)] flex items-start gap-3 transition-all duration-300">
                        <div class="w-8 h-8 rounded-[8px] bg-emerald-50 border border-emerald-200/80 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0 pt-0.5">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Berhasil</div>
                            <p class="text-xs sm:text-sm font-medium text-slate-800 leading-snug mt-0.5">{{ session('success') }}</p>
                        </div>
                        <button type="button" onclick="dismissAdminToast(this.closest('.admin-toast'))" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer shrink-0" aria-label="Tutup notifikasi">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div role="alert" class="admin-toast pointer-events-auto bg-white/95 backdrop-blur-md border border-rose-200/90 rounded-[12px] p-3.5 pl-4 shadow-[0_12px_32px_-8px_rgba(13,33,73,0.18)] flex items-start gap-3 transition-all duration-300">
                        <div class="w-8 h-8 rounded-[8px] bg-rose-50 border border-rose-200/80 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.65 18.5h16.7a1 1 0 00.86-1.5l-7.5-13a1 1 0 00-1.72 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0 pt-0.5">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Perhatian</div>
                            <p class="text-xs sm:text-sm font-medium text-slate-800 leading-snug mt-0.5">{{ session('error') }}</p>
                        </div>
                        <button type="button" onclick="dismissAdminToast(this.closest('.admin-toast'))" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer shrink-0" aria-label="Tutup notifikasi">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
            <script>
                function dismissAdminToast(el) {
                    if (!el) return;
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-8px)';
                    setTimeout(() => el.remove(), 250);
                }
                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('.admin-toast').forEach((toast, idx) => {
                        setTimeout(() => dismissAdminToast(toast), 5000 + idx * 600);
                    });
                });
            </script>
        @endif

        <!-- Content Area -->
        <div class="px-4 py-5 sm:px-6 sm:py-6 lg:px-[32px] lg:py-[30px] flex-1 w-full min-w-0">
            <div class="w-full max-w-[1280px] mx-auto min-w-0">
                @if($errors->any())
                    <div class="mb-[22px] p-4 rounded-[12px] bg-rose-50 border border-rose-200 text-rose-900 text-sm shadow-2xs">
                        <div class="flex items-center gap-2 mb-1.5 font-bold text-rose-950">
                            <div class="w-2 h-2 rounded-full bg-rose-500 ring-4 ring-rose-100 shrink-0"></div>
                            <span>Peringatan Validasi:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1 ml-4 text-rose-800">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </main>

    <script>
        function toggleSidebar(open) {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (!sidebar || !backdrop) return;

            if (open) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        function toggleExamAccordion() {
            const submenu = document.getElementById('examSubmenu');
            const chevron = document.getElementById('examMenuChevron');
            const trigger = document.getElementById('examMenuTrigger');
            if (!submenu) return;

            const isExpanded = submenu.classList.toggle('expanded');
            if (chevron) {
                chevron.classList.toggle('rotate-180', isExpanded);
            }
            if (trigger) {
                trigger.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
            }
        }

        function closeAllSelects(except = null) {
            document.querySelectorAll('.custom-select').forEach(function(el) {
                if (el !== except) {
                    el.querySelector('.select-options')?.classList.add('hidden');
                    el.querySelector('.chevron')?.classList.remove('rotate-180');
                }
            });
        }

        function filterSelectOptions(parent, query) {
            const q = (query || '').toLowerCase().trim();
            let visibleCount = 0;
            parent.querySelectorAll('.option-item').forEach(function(opt) {
                const match = opt.textContent.toLowerCase().includes(q);
                opt.classList.toggle('hidden', !match);
                if (match) visibleCount++;
            });
            const emptyMsg = parent.querySelector('.select-empty');
            if (emptyMsg) emptyMsg.classList.toggle('hidden', visibleCount > 0);
        }

        function attachSelectSearch(parent) {
            const optionsBox = parent.querySelector('.select-options');
            if (!optionsBox || optionsBox.querySelector('.select-search-wrap')) return;
            if (parent.querySelectorAll('.option-item').length <= 4 && parent.dataset.searchable !== 'true') return;

            const wrap = document.createElement('div');
            wrap.className = 'select-search-wrap p-2 border-b border-slate-100 bg-slate-50/80 sticky top-0 z-10';
            wrap.innerHTML = `
                <div class="relative">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" class="select-search w-full bg-white border border-slate-200 rounded-lg pl-8 pr-2.5 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C]" placeholder="Cari..." autocomplete="off">
                </div>
            `;
            optionsBox.prepend(wrap);

            const empty = document.createElement('div');
            empty.className = 'select-empty hidden px-3 py-3 text-center text-xs text-slate-400';
            empty.textContent = 'Tidak ada hasil.';
            optionsBox.appendChild(empty);

            wrap.querySelector('.select-search').addEventListener('input', function() {
                filterSelectOptions(parent, this.value);
            });
        }

        function initSearchableSelects() {
            document.querySelectorAll('main select').forEach(function(select) {
                if (select.dataset.enhanced || select.multiple || select.hasAttribute('data-native') || select.classList.contains('bg-transparent')) {
                    return;
                }
                select.dataset.enhanced = 'true';

                const wrapper = document.createElement('div');
                wrapper.className = 'relative custom-select w-full';

                const trigger = document.createElement('button');
                trigger.type = 'button';
                trigger.className = 'select-trigger w-full flex items-center justify-between gap-2 bg-white border border-slate-200 rounded-[8px] px-3.5 py-2.5 text-sm text-slate-800 hover:border-slate-300 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10 transition-all text-left cursor-pointer';

                const selectedSpan = document.createElement('span');
                selectedSpan.className = 'selected-text truncate font-medium';
                const currentOpt = select.options[select.selectedIndex];
                selectedSpan.textContent = currentOpt ? currentOpt.textContent.trim() : 'Pilih...';
                if (!select.value) selectedSpan.classList.add('text-slate-600');
                else selectedSpan.classList.add('text-slate-900');

                trigger.appendChild(selectedSpan);
                trigger.insertAdjacentHTML('beforeend', `<svg class="w-4 h-4 text-slate-400 chevron transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>`);

                const menu = document.createElement('div');
                menu.className = 'select-options hidden absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-[12px] shadow-xl z-[70] max-h-60 overflow-y-auto';

                const list = document.createElement('div');
                list.className = 'p-1.5 space-y-0.5';

                Array.from(select.options).forEach(function(opt) {
                    const isSelected = opt.selected;
                    const item = document.createElement('div');
                    item.dataset.value = opt.value;
                    item.className = `option-item px-3 py-2 rounded-[8px] text-xs sm:text-sm cursor-pointer transition-colors flex items-center justify-between gap-2 ${isSelected ? 'bg-slate-100 text-[#142C5C] font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-[#142C5C]'}`;
                    item.innerHTML = `<span class="truncate">${opt.textContent.trim()}</span><svg class="w-4 h-4 text-[#142C5C] shrink-0 check-icon ${isSelected ? '' : 'hidden'}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7" /></svg>`;
                    list.appendChild(item);
                });

                menu.appendChild(list);
                wrapper.appendChild(trigger);
                wrapper.appendChild(menu);

                select.classList.add('sr-only');
                select.tabIndex = -1;
                select.parentNode.insertBefore(wrapper, select.nextSibling);
                wrapper.appendChild(select);
                attachSelectSearch(wrapper);
            });

            document.querySelectorAll('.custom-select').forEach(attachSelectSearch);
        }

        document.addEventListener('DOMContentLoaded', initSearchableSelects);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                toggleSidebar(false);
                closeAllSelects();
            }
        });

        document.addEventListener('click', function(e) {
            const activeSelect = e.target.closest('.custom-select');
            closeAllSelects(activeSelect);

            const trigger = e.target.closest('.select-trigger');
            if (trigger) {
                const parent = trigger.closest('.custom-select');
                const options = parent.querySelector('.select-options');
                const chevron = parent.querySelector('.chevron');
                const isHidden = options.classList.toggle('hidden');
                chevron?.classList.toggle('rotate-180', !isHidden);
                if (!isHidden) {
                    const searchInput = parent.querySelector('.select-search');
                    if (searchInput) {
                        searchInput.value = '';
                        filterSelectOptions(parent, '');
                        setTimeout(() => searchInput.focus(), 10);
                    }
                }
                return;
            }

            const option = e.target.closest('.option-item');
            if (option) {
                const parent = option.closest('.custom-select');
                const val = option.getAttribute('data-value');
                const text = option.querySelector('span')?.textContent || option.textContent;
                const nativeSelect = parent.querySelector('select');
                const hiddenInput = parent.querySelector('input[type="hidden"]');
                const selectedText = parent.querySelector('.selected-text');

                if (selectedText) {
                    selectedText.textContent = text.trim();
                    selectedText.classList.remove('text-slate-400', 'text-slate-500');
                    selectedText.classList.add('text-slate-900', 'font-medium');
                }
                parent.querySelectorAll('.option-item').forEach(opt => {
                    opt.classList.remove('bg-indigo-50', 'text-indigo-600', 'bg-slate-50', 'text-[#2457E8]', 'bg-slate-100', 'text-[#142C5C]', 'font-semibold');
                    opt.querySelector('.check-icon')?.classList.add('hidden');
                });
                option.classList.add('bg-slate-100', 'text-[#142C5C]', 'font-semibold');
                option.querySelector('.check-icon')?.classList.remove('hidden');

                closeAllSelects();

                if (nativeSelect) {
                    nativeSelect.value = val;
                    nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                } else if (hiddenInput) {
                    hiddenInput.value = val;
                }
            }
        });
    </script>
</body>
</html>
