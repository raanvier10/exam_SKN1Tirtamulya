<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ujian: {{ $exam->title }} - Akses Safe Exam Browser</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-hover: #4338CA;
            --primary-light: #EEF2FF;
            --slate-900: #0F172A;
            --slate-700: #334155;
            --slate-500: #64748B;
            --slate-200: #E2E8F0;
            --slate-50: #F8FAFC;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        body {
            background: linear-gradient(145deg, #0F172A 0%, #1E1B4B 50%, #0F172A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            color: #fff;
        }
        .card {
            background: rgba(255, 255, 255, 0.98);
            color: var(--slate-900);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 480px;
            width: 100%;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeIn 0.4s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .header {
            background: linear-gradient(135deg, #4F46E5, #6366F1);
            color: white;
            padding: 32px 24px 28px;
            text-align: center;
            position: relative;
        }
        .badge-lock {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .title {
            font-size: 22px;
            font-weight: 800;
            line-height: 1.3;
            letter-spacing: -0.5px;
        }
        .subtitle {
            font-size: 13px;
            opacity: 0.85;
            margin-top: 6px;
        }
        .body-content {
            padding: 28px 24px;
        }
        .notice-box {
            background: #FEF3C7;
            border: 1px solid #FDE68A;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 13px;
            color: #92400E;
            line-height: 1.5;
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }
        .btn-launch {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 16px 20px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-launch:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }
        .qr-section {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--slate-200);
            text-align: center;
        }
        .qr-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--slate-700);
            margin-bottom: 4px;
        }
        .qr-desc {
            font-size: 12px;
            color: var(--slate-500);
            margin-bottom: 16px;
        }
        .qr-image-wrapper {
            display: inline-block;
            background: white;
            padding: 12px;
            border-radius: 18px;
            border: 2px dashed #CBD5E1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .qr-image {
            width: 180px;
            height: 180px;
            display: block;
        }
        .steps-card {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 16px;
            padding: 16px;
            margin-top: 20px;
        }
        .step-item {
            display: flex;
            gap: 12px;
            font-size: 12px;
            color: var(--slate-700);
            margin-bottom: 10px;
            align-items: flex-start;
        }
        .step-item:last-child {
            margin-bottom: 0;
        }
        .step-num {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 10px;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .app-store-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }
        .app-store-badge:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="header">
        <div class="badge-lock">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            Ujian Terkunci (Kiosk)
        </div>
        <h1 class="title">{{ $exam->title }}</h1>
        <p class="subtitle">Wajib Menggunakan Safe Exam Browser (iOS / iPad)</p>
    </div>

    <div class="body-content">
        <div class="notice-box">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <div>
                <strong>Akses Khusus Ujian:</strong> Halaman ini tidak dapat dikerjakan lewat Safari/Chrome biasa untuk mencegah kecurangan. Silakan buka melalui tombol SEB di bawah.
            </div>
        </div>

        <!-- Tombol Buka di Safe Exam Browser -->
        <a href="{{ $sebSchemeUrl }}" class="btn-launch">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
            Buka di Safe Exam Browser
        </a>

        <!-- Panduan 3 Langkah -->
        <div class="steps-card">
            <div class="step-item">
                <div class="step-num">1</div>
                <div>Install aplikasi <strong>Safe Exam Browser</strong> gratis di App Store (jika belum terpasang).</div>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <div>Klik tombol ungu <strong>"Buka di Safe Exam Browser"</strong> di atas atau scan QR code di bawah.</div>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <div>iPhone/iPad Anda akan otomatis terkunci ke mode ujian dan Anda dapat login menggunakan NIS.</div>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="https://apps.apple.com/app/safe-exam-browser/id1138834203" target="_blank" class="app-store-badge">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-2 .6-2.65 1.35-.58.67-1.09 1.74-.95 2.77.99.08 2.05-.52 2.68-1.27z"/></svg>
                Download Safe Exam Browser di App Store &rarr;
            </a>
        </div>

        <!-- Scan QR Code -->
        <div class="qr-section">
            <div class="qr-title">Scan Menggunakan Kamera iPhone</div>
            <div class="qr-desc">Arahkan kamera iPhone Anda ke barcode ini untuk meluncurkan ujian</div>
            <div class="qr-image-wrapper">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($sebSchemeUrl) }}" alt="QR Code Safe Exam Browser" class="qr-image">
            </div>
            <div style="margin-top: 12px;">
                <a href="{{ route('seb.exam.config', $exam) }}" style="font-size: 11px; color: var(--slate-500); text-decoration: underline;">
                    Download File Konfigurasi (.seb)
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
