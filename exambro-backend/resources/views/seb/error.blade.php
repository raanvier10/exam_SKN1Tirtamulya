<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Safe Exam Browser</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        body {
            background: #0F172A;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #1E293B;
            border: 1px solid #334155;
            border-radius: 20px;
            padding: 32px 24px;
            max-width: 420px;
            width: 100%;
            text-align: center;
        }
        .icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: rgba(239, 68, 68, 0.15);
            color: #EF4444;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        h2 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
        p { font-size: 13px; color: #94A3B8; line-height: 1.5; margin-bottom: 24px; }
        .exam-info {
            background: #0F172A;
            border-radius: 12px;
            padding: 12px;
            font-size: 12px;
            color: #CBD5E1;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="icon">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
    </div>
    <h2>{{ $title }}</h2>
    <p>{{ $message }}</p>

    <div class="exam-info">
        Ujian: <strong>{{ $exam->title }}</strong>
    </div>
</div>

</body>
</html>
