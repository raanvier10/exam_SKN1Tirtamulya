<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login Siswa - {{ $exam->title }} (Safe Exam Browser)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-hover: #4338CA;
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
            -webkit-user-select: none;
            user-select: none;
        }
        body {
            background: #F8FAFC;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--slate-900);
        }
        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
            border: 1px solid var(--slate-200);
            max-width: 400px;
            width: 100%;
            padding: 32px 28px;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #EEF2FF;
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }
        .title {
            font-size: 20px;
            font-weight: 800;
            color: var(--slate-900);
            line-height: 1.3;
        }
        .subtitle {
            font-size: 12px;
            color: var(--slate-500);
            margin-top: 4px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--slate-700);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .form-input {
            width: 100%;
            padding: 13px 16px;
            border-radius: 12px;
            border: 1.5px solid var(--slate-200);
            background: #F8FAFC;
            font-size: 14px;
            color: var(--slate-900);
            outline: none;
            transition: all 0.2s;
            -webkit-user-select: text;
            user-select: text;
        }
        .form-input:focus {
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            background: var(--primary);
            color: white;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
            transition: all 0.2s;
            margin-top: 6px;
        }
        .btn-submit:hover {
            background: var(--primary-hover);
        }
        .alert-error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #B91C1C;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="header">
        <div class="badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            SEB Secure Gateway
        </div>
        <h2 class="title">{{ $exam->title }}</h2>
        <p class="subtitle">Silakan masukkan NIS & kata sandi Anda untuk mulai</p>
    </div>

    @if(session('error'))
    <div class="alert-error">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <form action="{{ route('seb.exam.auth', $exam) }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label" for="username">Nomor Induk Siswa (NIS)</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus class="form-input" placeholder="Contoh: 20241001" autocomplete="off" autocorrect="off" autocapitalize="off">
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" required class="form-input" placeholder="Masukkan password Anda">
        </div>

        <button type="submit" class="btn-submit">
            Masuk & Mulai Ujian &rarr;
        </button>
    </form>
</div>

</body>
</html>
