<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Kode Ujian — ExaSatrya</title>
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
        }
        h1, h2, h3 { overflow-wrap: anywhere; word-break: break-word; }
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
    </style>
</head>
<body class="min-h-[100svh] antialiased flex flex-col bg-white overflow-x-hidden selection:bg-[#142C5C] selection:text-white">

    <header class="border-b border-slate-200 bg-white sticky top-0 z-30 shrink-0">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-8 h-14 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('images/logo.webp') }}" alt="Logo SMKN 1 Tirtamulya" class="w-8 h-8 object-contain shrink-0">
                <span class="text-[18px] sm:text-[20px] font-extrabold text-[#142C5C] tracking-tight leading-none">ExaSatrya</span>
            </div>

            <div class="flex items-center gap-3.5 shrink-0">
                <div class="hidden sm:block text-right min-w-0">
                    <div class="text-[12.5px] font-bold text-[#142C5C] leading-tight">Portal Ujian Web</div>
                    <div class="text-[11px] text-slate-500 font-medium leading-tight truncate">SMKN 1 Tirtamulya</div>
                </div>
                <span class="hidden sm:block h-6 w-px bg-slate-200 shrink-0" aria-hidden="true"></span>
                <a href="{{ route('student.portal') }}" class="h-9 px-3.5 inline-flex items-center gap-[6px] rounded-[8px] border border-slate-300 bg-white hover:border-[#142C5C] text-xs font-bold text-[#142C5C] transition shrink-0 whitespace-nowrap">
                    <svg class="w-[14px] h-[14px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-[1040px] mx-auto px-4 sm:px-8 py-6 w-full flex-1 flex flex-col items-center justify-center">
        <div class="w-full max-w-md card p-6 sm:p-7 space-y-5">
            <div class="text-center space-y-2">
                <div class="w-11 h-11 rounded-[10px] bg-slate-100 border border-slate-200 text-[#142C5C] flex items-center justify-center mx-auto">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                    </svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-[0.1em] text-slate-500">Verifikasi Ruang Ujian</div>
                    <h1 class="text-lg sm:text-xl font-extrabold text-[#142C5C] tracking-tight mt-0.5">{{ $exam->title }}</h1>
                    <p class="text-[13px] text-slate-500 mt-1">
                        Masukkan kode ujian aktif yang diberikan oleh pengawas ruang untuk mata pelajaran ini.
                    </p>
                </div>
            </div>

            @if(session('error'))
                <div class="p-3.5 rounded-[8px] bg-rose-50 border border-rose-200 text-rose-900 text-xs font-medium flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('portal.verify-code', $exam) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="access_code" class="block text-xs font-bold text-[#142C5C]">Kode Ujian (PIN)</label>
                        <span class="text-[11px] text-slate-400 font-medium">{{ $exam->duration }} Menit</span>
                    </div>
                    <input type="text" id="access_code" name="access_code" required autofocus maxlength="20" autocomplete="off"
                           placeholder="Masukkan kode ujian"
                           class="w-full h-11 px-4 rounded-[8px] border border-slate-200 bg-slate-50/70 focus:bg-white text-center text-base tabular-nums font-extrabold tracking-[0.2em] uppercase text-[#142C5C] placeholder:normal-case placeholder:tracking-normal placeholder:font-normal placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10">
                    @error('access_code')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full h-10 bg-[#142C5C] hover:bg-[#0D2149] text-white rounded-[8px] text-[13.5px] font-bold transition cursor-pointer">
                    Verifikasi &amp; Mulai Ujian
                </button>
            </form>
        </div>
    </main>

    <footer class="hidden sm:block bg-[#142C5C] text-blue-100/90 py-2.5 px-4 text-center text-[11.5px] sm:text-xs font-medium shrink-0">
        &copy; {{ date('Y') }} SMKN 1 Tirtamulya &nbsp;|&nbsp; ExaSatrya &ndash; Sistem Ujian Terproteksi
    </footer>
</body>
</html>
