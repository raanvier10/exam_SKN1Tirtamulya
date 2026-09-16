<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ujian: {{ $exam->title }} (Safe Exam Browser)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --rose: #EF4444;
            --slate-900: #0F172A;
            --slate-800: #1E293B;
            --slate-700: #334155;
            --slate-500: #64748B;
            --slate-200: #E2E8F0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-touch-callout: none !important;
            -webkit-user-select: none !important;
            user-select: none !important;
        }
        input, textarea, [contenteditable="true"] {
            -webkit-user-select: text !important;
            user-select: text !important;
        }
        body, html {
            height: 100%;
            width: 100%;
            overflow: hidden;
            background: #ffffff;
        }
        .exam-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100vw;
        }
        /* Top Navigation Header */
        .topbar {
            background: var(--slate-900);
            color: #ffffff;
            height: 54px;
            padding: 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--slate-800);
            flex-shrink: 0;
            z-index: 50;
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .exam-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(79, 70, 229, 0.2);
            border: 1px solid rgba(79, 70, 229, 0.4);
            color: #A5B4FC;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            flex-shrink: 0;
        }
        .exam-title-text {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .student-info {
            font-size: 11px;
            color: #94A3B8;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .timer-badge {
            background: #1E293B;
            border: 1px solid #334155;
            color: #F8FAFC;
            padding: 4px 10px;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .timer-badge.warning {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.4);
            color: #FCA5A5;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .btn-finish {
            background: var(--rose);
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-finish:hover {
            background: #DC2626;
        }
        /* Iframe Google Form */
        .frame-container {
            flex: 1;
            position: relative;
            width: 100%;
            height: 100%;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
        /* GPS Lock Overlay */
        .gps-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(8px);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: white;
            text-align: center;
        }
        .gps-card {
            background: #1E293B;
            border: 1px solid #334155;
            border-radius: 20px;
            padding: 28px 24px;
            max-width: 420px;
            width: 100%;
        }
        .gps-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(239, 68, 68, 0.15);
            color: var(--rose);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .gps-title {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .gps-desc {
            font-size: 13px;
            color: #94A3B8;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .btn-retry-gps {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="exam-container">
    <!-- Top Header -->
    <header class="topbar">
        <div class="topbar-left">
            <span class="exam-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                SEB KIOSK
            </span>
            <div style="min-width: 0;">
                <div class="exam-title-text">{{ $exam->title }}</div>
                <div class="student-info">{{ $user->name }} ({{ $user->class?->name ?? 'Siswa' }})</div>
            </div>
        </div>

        <div class="topbar-right">
            <div id="countdownBadge" class="timer-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span id="countdownText">--:--:--</span>
            </div>
            
            <form action="{{ route('seb.exam.finish', $exam) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin sudah selesai mengerjakan dan ingin mengirim ujian?');">
                @csrf
                <button type="submit" class="btn-finish">Selesai</button>
            </form>
        </div>
    </header>

    <!-- Google Form Content -->
    <div class="frame-container">
        <iframe src="{{ $formUrl }}" id="examFrame" allow="geolocation"></iframe>
    </div>
</div>

@if(!$user->is_pkl)
<!-- GPS Verification Modal for Regular Students -->
<div id="gpsModal" class="gps-overlay" style="display: none;">
    <div class="gps-card">
        <div class="gps-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
        </div>
        <h3 class="gps-title">Verifikasi Lokasi Sekolah</h3>
        <p id="gpsMessage" class="gps-desc">Memeriksa apakah Anda berada di dalam radius area ujian sekolah...</p>
        <button type="button" onclick="checkStudentLocation()" class="btn-retry-gps">Coba Lagi</button>
    </div>
</div>
@endif

<script>
    // 1. Hitung Mundur Sisa Waktu Ujian
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

    // 2. Validasi GPS (Khusus Siswa Reguler)
    const isPkl = {{ $user->is_pkl ? 'true' : 'false' }};
    const schoolLat = {{ $schoolLat }};
    const schoolLng = {{ $schoolLng }};
    const maxRadius = {{ $maxRadius }};

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    function checkStudentLocation() {
        if (isPkl) return;

        const modal = document.getElementById('gpsModal');
        const msg = document.getElementById('gpsMessage');

        if (!navigator.geolocation) {
            modal.style.display = 'flex';
            msg.textContent = 'Perangkat Anda tidak mendukung pendeteksian lokasi GPS.';
            return;
        }

        modal.style.display = 'flex';
        msg.textContent = 'Memeriksa titik koordinat GPS...';

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const dist = calculateDistance(schoolLat, schoolLng, pos.coords.latitude, pos.coords.longitude);
                if (dist > maxRadius) {
                    msg.textContent = `Anda berada di luar area sekolah (${Math.round(dist)} meter dari target, batas maksimal: ${maxRadius} meter).`;
                    modal.style.display = 'flex';
                } else {
                    modal.style.display = 'none';
                }
            },
            (err) => {
                modal.style.display = 'flex';
                msg.textContent = 'Izin lokasi (GPS) ditolak atau belum aktif. Silakan izinkan akses lokasi di Safe Exam Browser.';
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    if (!isPkl) {
        checkStudentLocation();
    }

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
