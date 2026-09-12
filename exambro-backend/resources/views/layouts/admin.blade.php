<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Exambro Admin')</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.webp') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; color: #0F172A; }

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
    </style>
</head>
<body class="antialiased min-h-screen flex selection:bg-indigo-500 selection:text-white">

    <!-- Sidebar -->
    <aside class="w-64 bg-white flex flex-col fixed h-full z-10 border-r border-slate-200/80 shadow-[1px_0_10px_rgba(0,0,0,0.02)]">
        <div class="p-6 border-b border-slate-100 flex items-center gap-3.5">
            <img src="{{ asset('images/logo.webp') }}" class="w-9 h-9 object-contain shrink-0" alt="Logo">
            <div>
                <h1 class="text-base font-bold tracking-tight text-slate-900 leading-none">Exambro</h1>
                <span class="text-[11px] font-medium text-slate-400">{{ auth()->user()->role === 'admin' ? 'Admin Kurikulum' : 'Portal Guru & Pengawas' }}</span>
            </div>
        </div>
        
        <nav class="flex-1 px-3 py-6 space-y-1.5 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-600 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.classes.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.classes.*') ? 'bg-indigo-50 text-indigo-600 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('admin.classes.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Master Kelas
            </a>

            <a href="{{ route('admin.students.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.students.*') ? 'bg-indigo-50 text-indigo-600 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('admin.students.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Master Siswa
            </a>
            @endif

            <a href="{{ route('admin.exams.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.exams.*') ? 'bg-indigo-50 text-indigo-600 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('admin.exams.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                {{ auth()->user()->isAdmin() ? 'Manajemen Ujian' : 'Monitoring & Ujian' }}
            </a>

            <a href="{{ route('admin.change-password') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.change-password*') ? 'bg-indigo-50 text-indigo-600 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('admin.change-password*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Ganti Password
            </a>
        </nav>

        <div class="p-4 border-t border-slate-100 space-y-3">
            <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <div class="overflow-hidden">
                    <div class="text-xs font-semibold text-slate-800 truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] font-medium text-slate-400">
                        @if(auth()->user()->isAdmin())
                            <span class="text-indigo-600 font-semibold">Administrator</span>
                        @else
                            <span class="text-emerald-600 font-semibold">Guru Pengampu</span>
                        @endif
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-all duration-150 group">
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-rose-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 min-h-screen flex flex-col">
        <!-- Content Area -->
        <div class="p-8 lg:p-10 flex-1 max-w-7xl w-full mx-auto">
            @if(session('success'))
                <div class="mb-8 p-4 rounded-xl bg-emerald-50/80 border border-emerald-200/80 text-emerald-900 text-sm flex items-center gap-3 shadow-xs animate-fade-in">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></div>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-8 p-4 rounded-xl bg-rose-50/80 border border-rose-200/80 text-rose-900 text-sm flex items-center gap-3 shadow-xs animate-fade-in">
                    <div class="w-2 h-2 rounded-full bg-rose-500 ring-4 ring-rose-100"></div>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        document.addEventListener('click', function(e) {
            // Close all custom selects if clicked outside
            document.querySelectorAll('.custom-select').forEach(function(el) {
                if (!el.contains(e.target)) {
                    el.querySelector('.select-options')?.classList.add('hidden');
                    el.querySelector('.chevron')?.classList.remove('rotate-180');
                }
            });

            // Toggle select on trigger click
            const trigger = e.target.closest('.select-trigger');
            if (trigger) {
                const parent = trigger.closest('.custom-select');
                const options = parent.querySelector('.select-options');
                const chevron = parent.querySelector('.chevron');
                const isHidden = options.classList.toggle('hidden');
                if (!isHidden) {
                    chevron?.classList.add('rotate-180');
                } else {
                    chevron?.classList.remove('rotate-180');
                }
            }

            // Select an option
            const option = e.target.closest('.option-item');
            if (option) {
                const parent = option.closest('.custom-select');
                const val = option.getAttribute('data-value');
                const text = option.querySelector('span')?.textContent || option.textContent;
                const input = parent.querySelector('input[type="hidden"]');
                const selectedText = parent.querySelector('.selected-text');
                
                if (input) input.value = val;
                if (selectedText) {
                    selectedText.textContent = text.trim();
                    selectedText.classList.remove('text-slate-400', 'text-slate-500');
                    selectedText.classList.add('text-slate-900', 'font-medium');
                }
                parent.querySelectorAll('.option-item').forEach(opt => opt.classList.remove('bg-indigo-50', 'text-indigo-600', 'font-semibold'));
                option.classList.add('bg-indigo-50', 'text-indigo-600', 'font-semibold');
                parent.querySelector('.select-options')?.classList.add('hidden');
                parent.querySelector('.chevron')?.classList.remove('rotate-180');
            }
        });
    </script>
</body>
</html>
