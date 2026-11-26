<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ !empty($backUrl) ? 'Web Darurat' : 'Safe Exam Browser' }}</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; }
        body {
            background: #F5F8FF;
            color: #0F172A;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            padding: 32px 26px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(13, 33, 73, 0.08);
        }
        .icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #DC2626;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
        }
        h2 { font-size: 20px; font-weight: 800; color: #142C5C; margin-bottom: 8px; }
        p { font-size: 13.5px; color: #475569; line-height: 1.55; margin-bottom: 20px; }
        .exam-info {
            background: #F5F8FF;
            border: 1px solid #DCE9FF;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 12.5px;
            color: #142C5C;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
    </div>
    <h2>{{ $title }}</h2>
    <p>{{ $message }}</p>

    <div class="exam-info">
        Ujian: <strong>{{ $exam->title }}</strong>
    </div>

    @if(!empty($backUrl))
        <a href="{{ $backUrl }}" style="display:inline-block;padding:11px 20px;border-radius:12px;background:#2457E8;color:#FFFFFF;font-size:13px;font-weight:700;text-decoration:none;">
            &larr; Kembali ke Daftar Ujian
        </a>
    @endif
</div>

</body>
</html>
