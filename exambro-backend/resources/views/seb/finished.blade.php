<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ujian Selesai - {{ $exam->title }} (Safe Exam Browser)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
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
            border-radius: 24px;
            padding: 36px 24px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }
        .icon {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: rgba(16, 185, 129, 0.15);
            color: #10B981;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        h2 { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
        p { font-size: 13px; color: #94A3B8; line-height: 1.5; margin-bottom: 24px; }
        .exam-info {
            background: #0F172A;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 16px;
            text-align: left;
            font-size: 12px;
            color: #CBD5E1;
            margin-bottom: 24px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label { color: #64748B; font-weight: 600; }
        .info-val { font-weight: 700; color: #fff; }
        .instruction {
            font-size: 12px;
            color: #A5B4FC;
            background: rgba(79, 70, 229, 0.15);
            padding: 12px;
            border-radius: 12px;
            border: 1px solid rgba(79, 70, 229, 0.3);
        }
    </style>
</head>
<body>

<div class="card">
    <div class="icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
    </div>
    <h2>Ujian Berhasil Diselesaikan!</h2>
    <p>{{ $message ?? 'Jawaban Anda telah tersimpan dengan aman di server sekolah.' }}</p>

    <div class="exam-info">
        <div class="info-row">
            <span class="info-label">Mata Ujian</span>
            <span class="info-val">{{ $exam->title }}</span>
        </div>
        @if(isset($user))
        <div class="info-row">
            <span class="info-label">Nama Siswa</span>
            <span class="info-val">{{ $user->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">NIS</span>
            <span class="info-val font-mono">{{ $user->username }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Waktu Selesai</span>
            <span class="info-val">{{ \Carbon\Carbon::now()->format('H:i') }} WIB</span>
        </div>
    </div>

    <div class="instruction">
        Silakan tutup aplikasi <strong>Safe Exam Browser</strong> atau hubungi pengawas ruang untuk meninggalkan kelas.
    </div>
</div>

</body>
</html>
