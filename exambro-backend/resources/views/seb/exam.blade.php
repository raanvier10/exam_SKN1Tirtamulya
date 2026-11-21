<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Ujian: {{ $exam->title }} (Safe Exam Browser)</title>
    <style>
        :root {
            --primary: #4F46E5;
            --primary-hover: #4338CA;
            --rose: #EF4444;
            --rose-dark: #DC2626;
            --slate-900: #0F172A;
            --slate-800: #1E293B;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748B;
            --slate-400: #94A3B8;
            --slate-200: #E2E8F0;
            --sat: env(safe-area-inset-top, 0px);
            --sab: env(safe-area-inset-bottom, 0px);
            --sal: env(safe-area-inset-left, 0px);
            --sar: env(safe-area-inset-right, 0px);
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-touch-callout: none !important;
            -webkit-user-select: none !important;
            user-select: none !important;
        }
        input, textarea, [contenteditable="true"] {
            -webkit-user-select: text !important;
            user-select: text !important;
        }
        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden;
            background: var(--slate-900);
            margin: 0;
            padding: 0;
            position: fixed;
            inset: 0;
        }

        /* Container pinned to viewport boundaries on iOS */
        .exam-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
            height: 100dvh;
            height: -webkit-fill-available;
            overflow: hidden;
            position: relative;
            background: var(--slate-900);
        }

        /* Top Header Wrapper with iOS Notch / Dynamic Island Safe Padding */
        .header-wrapper {
            background: var(--slate-900);
            border-bottom: 1px solid var(--slate-800);
            padding-top: max(10px, var(--sat));
            padding-left: max(12px, var(--sal));
            padding-right: max(12px, var(--sar));
            padding-bottom: 8px;
            flex-shrink: 0;
            z-index: 50;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        /* Tier 1: Identity & Finish Button */
        .tier-identity {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            width: 100%;
        }
        .identity-left {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            flex: 1;
        }
        .exam-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(79, 70, 229, 0.25);
            border: 1px solid rgba(129, 140, 248, 0.35);
            color: #C7D2FE;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 7px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            flex-shrink: 0;
        }
        .exam-meta {
            min-width: 0;
            flex: 1;
        }
        .exam-title-text {
            font-size: 13.5px;
            font-weight: 700;
            color: #FFFFFF;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
        }
        .student-info {
            font-size: 11px;
            color: var(--slate-400);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
            margin-top: 1px;
        }
        .btn-finish {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.35);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-finish:active {
            transform: scale(0.97);
            background: #B91C1C;
        }

        /* Tier 2: Cockpit Controls & Timer */
        .tier-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid rgba(51, 65, 85, 0.6);
        }
        .timer-badge {
            background: #112238;
            border: 1px solid #233B5D;
            color: #F8FAFC;
            padding: 4px 10px;
            border-radius: 8px;
            font-family: 'JetBrains Mono', -apple-system, monospace;
            font-size: 12.5px;
            font-weight: 800;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        .timer-badge.warning {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.6);
            color: #FCA5A5;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .controls-right {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        .btn-tool {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.16);
            color: #E2E8F0;
            padding: 4px 10px;
            border-radius: 7px;
            font-size: 11.5px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
            white-space: nowrap;
            cursor: pointer;
        }
        .btn-tool:active {
            background: rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
        }

        /* Banner Notice if Google Form encounters 3rd-party cookie issues on iOS */
        .ios-notice-banner {
            background: #FEF3C7;
            border-bottom: 1px solid #FDE68A;
            padding: 6px 12px;
            font-size: 11.5px;
            color: #92400E;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-shrink: 0;
            z-index: 40;
        }
        .ios-notice-text {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            line-height: 1.35;
        }
        .ios-notice-link {
            background: #D97706;
            color: #FFFFFF;
            text-decoration: none;
            padding: 3px 9px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 11px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .ios-notice-close {
            background: none;
            border: none;
            color: #B45309;
            cursor: pointer;
            padding: 2px 4px;
            font-size: 14px;
            line-height: 1;
            font-weight: bold;
        }

        /* Iframe Google Form Container (Fixed Height to prevent iOS WebKit expansion bug) */
        .frame-container {
            flex: 1 1 0%;
            min-height: 0;
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
            background: #F8FAFC;
            padding-bottom: max(0px, var(--sab));
        }
        .frame-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            min-width: 100%;
            max-width: 100%;
            min-height: 100%;
            max-height: 100%;
            border: none;
            display: block;
        }

        /* Large Screen / iPad Layout */
        @media (min-width: 640px) {
            .header-wrapper {
                padding-bottom: 10px;
            }
            .tier-identity {
                gap: 16px;
            }
            .tier-controls {
                margin-top: 10px;
                padding-top: 10px;
            }
        }
    </style>
</head>
<body>

<div class="exam-container">
    <!-- Top Header -->
    <header class="header-wrapper">
        <!-- Tier 1: Identity & Finish Button -->
        <div class="tier-identity">
            <div class="identity-left">
                <span class="exam-badge">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    SEB KIOSK
                </span>
                <div class="exam-meta">
                    <div class="exam-title-text">{{ $exam->title }}</div>
                    <div class="student-info">{{ $user->name }} • {{ $user->class?->name ?? 'Siswa' }}</div>
                </div>
            </div>

            <form action="{{ route('seb.exam.finish', $exam) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin sudah selesai mengerjakan dan ingin mengirim ujian?');">
                @csrf
                <button type="submit" class="btn-finish">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Selesai</span>
                </button>
            </form>
        </div>

        <!-- Tier 2: Cockpit Bar (Countdown + Tools) -->
        <div class="tier-controls">
            <div id="countdownBadge" class="timer-badge">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span id="countdownText">--:--:--</span>
            </div>

            <div class="controls-right">
                <button type="button" onclick="reloadExamFrame()" class="btn-tool" title="Muat Ulang Soal jika lembar jawaban tidak muncul">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    <span>Muat Ulang</span>
                </button>

                <a href="{{ $exam->google_form_url }}" class="btn-tool" title="Buka Google Form Langsung jika layar menampilkan Page Expired">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    <span>Buka Penuh</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Dismissible notice banner for iOS Cookie "Page Expired" issue -->
    <div id="iosNoticeBanner" class="ios-notice-banner">
        <div class="ios-notice-text">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink: 0;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span>Jika form error / <strong>Page Expired</strong> (kebijakan cookie iOS):</span>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
            <a href="{{ $exam->google_form_url }}" class="ios-notice-link">Buka Penuh &rarr;</a>
            <button type="button" onclick="document.getElementById('iosNoticeBanner').style.display='none'" class="ios-notice-close" title="Tutup pemberitahuan">&times;</button>
        </div>
    </div>

    <!-- Google Form Content (Locked inside strictly bounded iframe container) -->
    <div class="frame-container">
        <iframe src="{{ $formUrl }}" id="examFrame" allow="autoplay; fullscreen"></iframe>
    </div>
</div>

<script>
    // 1. Muat Ulang Iframe tanpa kehilangan sesi SEB
    function reloadExamFrame() {
        const frame = document.getElementById('examFrame');
        if (frame) {
            const currentSrc = frame.src;
            frame.src = 'about:blank';
            setTimeout(() => {
                frame.src = currentSrc;
            }, 100);
        }
    }

    // 2. Hitung Mundur Sisa Waktu Ujian
    const expiredAt = new Date("{{ $session->expired_at ? $session->expired_at->toIso8601String() : $exam->end_at->toIso8601String() }}").getTime();
    const countdownEl = document.getElementById('countdownText');
    const badgeEl = document.getElementById('countdownBadge');

    const timer = setInterval(() => {
        const now = new Date().getTime();
        const distance = expiredAt - now;

        if (distance <= 0) {
            clearInterval(timer);
            countdownEl.textContent = "00:00:00";
            badgeEl.classList.add('warning');
            alert("Waktu pengerjaan ujian telah habis!");
            document.querySelector('form[action*="finish"]').submit();
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        countdownEl.textContent = 
            String(hours).padStart(2, '0') + ":" + 
            String(minutes).padStart(2, '0') + ":" + 
            String(seconds).padStart(2, '0');

        if (distance < 5 * 60 * 1000) {
            badgeEl.classList.add('warning');
        }
    }, 1000);

    // 3. Anti-Copy & Anti-ContextMenu
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.addEventListener('selectstart', e => {
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });
</script>

</body>
</html>
