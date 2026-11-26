<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ujian Selesai - {{ $exam->title }} ({{ !empty($isWebEmergency) ? 'Web Darurat' : 'Safe Exam Browser' }})</title>
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
            padding: 34px 26px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(13, 33, 73, 0.08);
        }
        .icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #059669;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
        }
        h2 { font-size: 21px; font-weight: 800; color: #142C5C; margin-bottom: 8px; }
        p { font-size: 13.5px; color: #475569; line-height: 1.55; margin-bottom: 20px; }
        .exam-info {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 16px;
            text-align: left;
            font-size: 12.5px;
            color: #334155;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }
        .info-row:last-child { margin-bottom: 0; }
        .info-label { color: #64748B; font-weight: 600; }
        .info-val { font-weight: 700; color: #0F172A; text-align: right; }
        .instruction {
            font-size: 12px;
            color: #142C5C;
            background: #DCE9FF;
            padding: 12px 14px;
            border-radius: 12px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="icon">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
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
            <span class="info-val" style="font-variant-numeric: tabular-nums;">{{ $user->username }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Waktu Selesai</span>
            <span class="info-val">{{ \Carbon\Carbon::now()->format('H:i') }} WIB</span>
        </div>
    </div>

    <div class="instruction">
        @if(!empty($isWebEmergency))
            Jawaban sudah dicatat. Anda dapat kembali ke daftar ujian Web Darurat atau menutup tab browser setelah melapor ke pengawas ruang.
        @else
            Silakan tutup aplikasi <strong>Safe Exam Browser</strong> atau hubungi pengawas ruang untuk meninggalkan kelas.
        @endif
    </div>

    @if(!empty($isWebEmergency))
        <div style="margin-top: 18px;">
            <a href="{{ route('web-exam.index') }}" style="display:inline-block;padding:11px 20px;border-radius:12px;background:#2457E8;color:#FFFFFF;font-size:13px;font-weight:700;text-decoration:none;">
                &larr; Kembali ke Daftar Ujian
            </a>
        </div>
    @endif
</div>

</body>
</html>
