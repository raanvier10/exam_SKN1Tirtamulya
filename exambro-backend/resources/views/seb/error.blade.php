<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ !empty($backUrl) ? 'Portal Ujian Web' : 'Safe Exam Browser' }}</title>
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
            border-radius: 12px;
            padding: 28px 24px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .icon {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #DC2626;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        h2 { font-size: 19px; font-weight: 800; color: #142C5C; margin-bottom: 8px; overflow-wrap: anywhere; word-break: break-word; }
        p { font-size: 14px; color: #475569; line-height: 1.55; margin-bottom: 20px; }
        .exam-info {
            background: #F5F8FF;
            border: 1px solid #DCE9FF;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            line-height: 1.5;
            color: #142C5C;
            margin-bottom: 20px;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
    </div>
    <h2>{{ $title }}</h2>
    <p>{{ $message }}</p>

    <div class="exam-info">
        Ujian: <strong>{{ $exam->title }}</strong>
    </div>

    @if(!empty($backUrl))
        <a href="{{ $backUrl }}" style="display:inline-flex;align-items:center;justify-content:center;gap:6px;height:40px;padding:0 18px;border-radius:8px;background:#142C5C;color:#FFFFFF;font-size:13.5px;font-weight:700;text-decoration:none;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Daftar Ujian</span>
        </a>
    @endif
</div>

</body>
</html>
