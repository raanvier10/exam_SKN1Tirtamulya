<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Ujian: {{ $exam->title }} ({{ !empty($isWebPortal) ? 'Portal Ujian Web' : 'Safe Exam Browser' }})</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #142C5C;
            --primary-hover: #0D2149;
            --rose: #EF4444;
            --rose-dark: #DC2626;
            --slate-900: #0D2149;
            --slate-800: #142C5C;
            --slate-700: #1E3A70;
            --slate-600: #475569;
            --slate-500: #64748B;
            --slate-400: #CBD5E1;
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
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-touch-callout: none !important;
            -webkit-user-select: none !important;
            user-select: none !important;
            -webkit-user-drag: none !important;
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: max(10px, var(--sat));
            padding-left: max(14px, var(--sal));
            padding-right: max(14px, var(--sar));
            padding-bottom: 10px;
            flex-shrink: 0;
            z-index: 50;
        }

        /* Tier 1: Identity & Finish Button */
        .tier-identity {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
        }
        .exam-meta {
            min-width: 0;
            flex: 1;
        }
        .exam-title-text {
            font-size: 14.5px;
            font-weight: 700;
            color: #FFFFFF;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
        }
        .student-info {
            font-size: 11.5px;
            color: var(--slate-400);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.35;
            margin-top: 2px;
        }
        .btn-finish {
            background: var(--rose-dark);
            color: #FFFFFF;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.15s ease, transform 0.15s ease;
            white-space: nowrap;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-finish:hover {
            background: #B91C1C;
        }
        .btn-finish:active {
            transform: scale(0.97);
        }

        /* Tier 2: Cockpit Controls & Timer */
        .tier-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .timer-badge {
            background: rgba(255, 255, 255, 0.08);
            color: #FFFFFF;
            padding: 5px 10px;
            border-radius: 8px;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            font-variant-numeric: tabular-nums;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        .timer-badge.warning {
            background: rgba(239, 68, 68, 0.22);
            color: #FECACA;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .controls-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .violation-text {
            font-size: 11.5px;
            font-weight: 600;
            color: #CBD5E1;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .btn-tool {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.16);
            color: #F8FAFC;
            padding: 6px 11px;
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.15s ease;
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
            <div class="exam-meta">
                <div class="exam-title-text">{{ $exam->title }}</div>
                <div class="student-info">{{ $user->name }} &mdash; {{ $user->class?->name ?? 'Siswa' }} | {{ !empty($isWebPortal) ? 'UJIAN WEB' : 'SEB KIOSK' }}</div>
            </div>

            <form id="examFinishForm" action="{{ !empty($isWebPortal) ? ($finishUrl ?? route('portal.finish', $exam)) : route('seb.exam.finish', $exam) }}" method="POST">
                @csrf
                <button type="button" onclick="openFinishConfirmModal()" class="btn-finish">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Selesai</span>
                </button>
            </form>
        </div>

        <!-- Tier 2: Cockpit Bar (Countdown + Tools) -->
        <div class="tier-controls">
            <div id="countdownBadge" class="timer-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span id="countdownText">--:--:--</span>
            </div>

            <div class="controls-right">
                @if(!empty($isWebPortal))
                <span class="violation-text" id="webViolationCounter">
                    Pelanggaran: <span id="webViolationText">{{ $activeViolationCount ?? 0 }} / {{ $exam->max_violation }}</span>
                </span>
                @else
                <a href="{{ $exam->google_form_url }}" class="btn-tool" title="Buka Google Form Langsung jika layar menampilkan Page Expired">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    <span>Buka Penuh</span>
                </a>
                @endif

                <button type="button" onclick="reloadExamFrame()" class="btn-tool" title="Muat Ulang Soal jika lembar jawaban tidak muncul">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    <span>Muat Ulang</span>
                </button>
            </div>
        </div>
    </header>

    @if(empty($isWebPortal))
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
    @endif

    <!-- Google Form Content (Locked inside strictly bounded iframe container) -->
    <div class="frame-container">
        <iframe src="{{ $formUrl }}" id="examFrame" allow="autoplay; fullscreen"></iframe>
    </div>
</div>

<!-- Custom In-Page Finish Confirmation Modal (stays inside fullscreen without native confirm/alert) -->
<div id="finishConfirmModal" onclick="if (event.target === this) closeFinishConfirmModal()" style="position:fixed;inset:0;z-index:998;background:rgba(13,33,73,0.72);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:16px;max-width:380px;width:100%;padding:24px;color:#0F172A;box-shadow:0 20px 50px rgba(13,33,73,0.35);">
        <h3 style="font-size:17px;font-weight:800;color:#142C5C;margin-bottom:8px;letter-spacing:-0.01em;">Selesaikan Ujian?</h3>
        <p style="font-size:13px;color:#475569;line-height:1.55;margin-bottom:20px;">
            Pastikan Anda sudah menekan tombol <strong>Kirim / Submit</strong> pada lembar soal sebelum mengakhiri sesi ujian ini.
        </p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <button type="button" onclick="closeFinishConfirmModal()" style="height:42px;border-radius:10px;border:1px solid #CBD5E1;background:#FFFFFF;color:#334155;font-size:13px;font-weight:700;cursor:pointer;">
                Kembali
            </button>
            <button type="button" id="confirmFinishSubmitBtn" onclick="submitExamFinish()" style="height:42px;border-radius:10px;border:none;background:#DC2626;color:#FFFFFF;font-size:13px;font-weight:700;cursor:pointer;">
                Ya, Selesai
            </button>
        </div>
    </div>
</div>

<script>
    // 1. Muat Ulang Iframe tanpa kehilangan sesi SEB
    function openFinishConfirmModal() {
        const modal = document.getElementById('finishConfirmModal');
        if (modal) modal.style.display = 'flex';
    }

    function closeFinishConfirmModal() {
        const modal = document.getElementById('finishConfirmModal');
        if (modal) modal.style.display = 'none';
    }

    function submitExamFinish() {
        window.__confirmingExamFinish = true;
        const btn = document.getElementById('confirmFinishSubmitBtn');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Mengirim...';
        }
        const form = document.getElementById('examFinishForm');
        if (form) form.submit();
    }

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
            submitExamFinish();
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

    // 3. Anti-Copy, Anti-Drag, Anti-Shortcut & Anti-ContextMenu (shell halaman ujian)
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.addEventListener('dragstart', e => e.preventDefault());
    ['copy', 'cut', 'paste'].forEach(evt => {
        document.addEventListener(evt, e => {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });
    });
    document.addEventListener('selectstart', e => {
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });
    document.addEventListener('keydown', e => {
        const key = (e.key || '').toLowerCase();
        const mod = e.ctrlKey || e.metaKey;
        if (e.key === 'F12' || (mod && e.shiftKey && ['i', 'j', 'c'].includes(key))) {
            e.preventDefault();
            return;
        }
        if (mod && ['c', 'x', 'v', 'a', 's', 'p', 'u'].includes(key)) {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        }
    });
</script>

@if(!empty($isWebPortal))
<div id="webExamOverlay" style="position:fixed;inset:0;z-index:999;background:rgba(13,33,73,0.92);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;padding:24px;">
    <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:16px;max-width:420px;width:100%;padding:28px;text-align:center;color:#0F172A;box-shadow:0 24px 60px rgba(13,33,73,0.35);">
        <div style="width:48px;height:48px;border-radius:8px;background:#F1F5F9;border:1px solid #E2E8F0;color:#142C5C;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#142C5C;margin-bottom:6px;">
            Portal Ujian Web | ExaSatrya
        </div>
        <h3 id="webOverlayTitle" style="font-size:18px;font-weight:800;color:#142C5C;margin-bottom:8px;letter-spacing:-0.01em;overflow-wrap:anywhere;">Mode Ujian Web Terproteksi</h3>
        <p id="webOverlayMessage" style="font-size:13.5px;color:#475569;line-height:1.55;margin-bottom:22px;">
            Dilarang berpindah tab, membuka aplikasi lain, atau keluar dari layar penuh. Pelanggaran dicatat otomatis dan akan mengunci ujian Anda.
        </p>
        <button type="button" id="webOverlayBtn" onclick="enterWebExamMode()" style="width:100%;height:42px;padding:0 18px;border-radius:8px;background:#142C5C;color:#FFFFFF;font-size:14px;font-weight:700;border:none;cursor:pointer;">
            Masuk Layar Ujian
        </button>
    </div>
</div>

<script>
    (function () {
        const violationUrl = @json($violationUrl ?? route('portal.violation', $exam));
        const portalUrl = @json($backPortalUrl ?? route('student.portal'));
        const csrfToken = @json(csrf_token());
        const overlay = document.getElementById('webExamOverlay');
        const overlayTitle = document.getElementById('webOverlayTitle');
        const overlayMsg = document.getElementById('webOverlayMessage');
        const overlayBtn = document.getElementById('webOverlayBtn');
        const counterText = document.getElementById('webViolationText');

        let started = false;
        let isLocked = false;
        let isSubmittingFinish = false;
        let lastViolationAt = 0;
        let fullscreenActive = false;

        const finishForm = document.querySelector('form[action*="finish"]');
        if (finishForm) {
            finishForm.addEventListener('submit', () => { isSubmittingFinish = true; });
        }

        window.enterWebExamMode = function () {
            if (isLocked) {
                window.location.href = portalUrl;
                return;
            }
            started = true;
            overlay.style.display = 'none';
            const el = document.documentElement;
            if (el.requestFullscreen) {
                el.requestFullscreen().then(() => { fullscreenActive = true; }).catch(() => {});
            }
        };

        function sendViolation(type) {
            if (!started || isLocked || isSubmittingFinish || window.__confirmingExamFinish) return;
            const now = Date.now();
            if (now - lastViolationAt < 2000) return;
            lastViolationAt = now;

            fetch(violationUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ type })
            })
            .then(r => r.json())
            .then(data => {
                if (counterText && typeof data.violations !== 'undefined') {
                    counterText.textContent = data.violations + ' / ' + data.max_violation;
                }
                overlay.style.display = 'flex';
                if (data.locked) {
                    isLocked = true;
                    overlayTitle.textContent = 'Ujian Terkunci!';
                    overlayTitle.style.color = '#DC2626';
                    overlayMsg.textContent = 'Pelanggaran Anda mencapai batas maksimal. Lapor ke Pengawas Ruang / PIC Jurusan untuk membuka kunci.';
                    overlayBtn.textContent = 'Kembali ke Daftar Ujian';
                    overlayBtn.style.background = '#DC2626';
                } else {
                    overlayTitle.textContent = 'Peringatan Pelanggaran (' + data.violations + '/' + data.max_violation + ')';
                    overlayTitle.style.color = '#B45309';
                    overlayMsg.textContent = 'Terdeteksi meninggalkan layar ujian. Klik tombol di bawah untuk melanjutkan; jika mencapai batas, ujian akan dikunci otomatis.';
                    overlayBtn.textContent = 'Lanjutkan Ujian';
                    overlayBtn.style.background = '#2457E8';
                }
            })
            .catch(() => {});
        }

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                sendViolation('WEB_TAB_SWITCH');
            }
        });

        window.addEventListener('blur', () => {
            if (window.__confirmingExamFinish || isSubmittingFinish) return;
            setTimeout(() => {
                if (window.__confirmingExamFinish || isSubmittingFinish) return;
                if (document.activeElement && document.activeElement.id === 'examFrame') return;
                if (!document.hasFocus()) {
                    sendViolation('WEB_TAB_SWITCH');
                }
            }, 150);
        });

        document.addEventListener('fullscreenchange', () => {
            if (fullscreenActive && !document.fullscreenElement) {
                fullscreenActive = false;
                sendViolation('WEB_FULLSCREEN_EXIT');
            }
        });
    })();
</script>
@endif

</body>
</html>
