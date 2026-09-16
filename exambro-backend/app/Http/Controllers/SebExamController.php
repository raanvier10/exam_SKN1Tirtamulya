<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SebExamController extends Controller
{
    /**
     * Titik koordinat sekolah & batas radius (meter)
     */
    public const SCHOOL_LAT = -6.3400545;
    public const SCHOOL_LNG = 107.4686982;
    public const MAX_RADIUS = 250;

    /**
     * Cek apakah request berasal dari Safe Exam Browser
     */
    public static function isSeb(Request $request): bool
    {
        $ua = $request->header('User-Agent') ?? '';
        return str_contains($ua, 'SEB') || str_contains($ua, 'SafeExamBrowser');
    }

    /**
     * Halaman Utama Ujian SEB
     */
    public function show(Request $request, Exam $exam)
    {
        // 1. Jika BUKAN dibuka dari Safe Exam Browser, tampilkan halaman panduan & link peluncur sebs://
        if (!self::isSeb($request)) {
            $currentUrl = $request->fullUrl();
            // Ubah skema https:// menjadi sebs:// atau http:// menjadi seb://
            $sebSchemeUrl = preg_replace('/^https:\/\//i', 'sebs://', $currentUrl);
            if ($sebSchemeUrl === $currentUrl) {
                $sebSchemeUrl = preg_replace('/^http:\/\//i', 'seb://', $currentUrl);
            }

            return view('seb.landing', [
                'exam' => $exam,
                'sebSchemeUrl' => $sebSchemeUrl,
                'currentUrl' => $currentUrl,
            ]);
        }

        // 2. Jika di dalam SEB tapi belum login, tampilkan form login siswa
        $user = Auth::user();
        if (!$user || $user->role !== 'siswa') {
            return view('seb.login', [
                'exam' => $exam,
            ]);
        }

        // 3. Validasi Target Kelas Siswa
        if ($exam->classes()->exists()) {
            $classIds = $exam->classes()->pluck('classes.id')->toArray();
            if (!$user->class_id || !in_array($user->class_id, $classIds)) {
                return view('seb.error', [
                    'exam' => $exam,
                    'title' => 'Kelas Tidak Sesuai',
                    'message' => 'Ujian ini tidak ditujukan untuk kelas Anda (' . ($user->class?->name ?? 'Tanpa Kelas') . ').',
                ]);
            }
        }

        // 4. Cek Status Peserta (Apakah Terkunci atau Sudah Selesai)
        $participant = ExamParticipant::firstOrCreate([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
        ], [
            'status' => 'registered',
        ]);

        if ($participant->status === 'locked') {
            return view('seb.error', [
                'exam' => $exam,
                'title' => 'Ujian Terkunci',
                'message' => 'Ujian Anda telah dikunci oleh sistem karena melebihi batas toleransi pelanggaran. Silakan hubungi pengawas ruang.',
            ]);
        }

        if ($participant->status === 'finished') {
            return view('seb.finished', [
                'exam' => $exam,
                'user' => $user,
                'message' => 'Anda telah menyelesaikan ujian ini sebelumnya.',
            ]);
        }

        // 5. Validasi Jadwal Ujian
        $now = Carbon::now();
        if ($now->lt($exam->start_at)) {
            return view('seb.error', [
                'exam' => $exam,
                'title' => 'Ujian Belum Dimulai',
                'message' => 'Ujian dijadwalkan mulai pada ' . Carbon::parse($exam->start_at)->format('d M Y, H:i') . ' WIB.',
            ]);
        }

        if ($exam->end_at && $now->gt($exam->end_at)) {
            return view('seb.error', [
                'exam' => $exam,
                'title' => 'Ujian Telah Berakhir',
                'message' => 'Batas waktu pengerjaan ujian telah berakhir pada ' . Carbon::parse($exam->end_at)->format('d M Y, H:i') . ' WIB.',
            ]);
        }

        // 6. Buat atau Ambil Sesi Aktif
        $session = ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->latest('id')
            ->first();

        if (!$session) {
            $calculatedExpiry = $now->copy()->addMinutes((int)$exam->duration);
            $expiredAt = ($exam->end_at && $calculatedExpiry->gt($exam->end_at)) ? $exam->end_at : $calculatedExpiry;

            $session = ExamSession::create([
                'exam_id' => $exam->id,
                'user_id' => $user->id,
                'device_id' => 'SEB_IOS_' . Str::random(8),
                'session_token' => Str::random(40),
                'started_at' => $now,
                'expired_at' => $expiredAt,
                'status' => 'ACTIVE',
            ]);

            $participant->update([
                'status' => 'working',
                'started_at' => $now,
            ]);
        }

        // Siapkan URL Google Form dengan parameter ?embedded=true
        $formUrl = $exam->google_form_url;
        if (!str_contains($formUrl, 'embedded=true')) {
            $separator = str_contains($formUrl, '?') ? '&' : '?';
            $formUrl .= $separator . 'embedded=true';
        }

        return view('seb.exam', [
            'exam' => $exam,
            'user' => $user,
            'session' => $session,
            'formUrl' => $formUrl,
            'schoolLat' => self::SCHOOL_LAT,
            'schoolLng' => self::SCHOOL_LNG,
            'maxRadius' => self::MAX_RADIUS,
        ]);
    }

    /**
     * Proses Login Siswa di Safe Exam Browser
     */
    public function authenticate(Request $request, Exam $exam)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'NIS / Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('username', $request->username)
            ->where('role', 'siswa')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withInput()->with('error', 'NIS atau Password salah!');
        }

        if ($user->status !== 'active') {
            return back()->withInput()->with('error', 'Akun siswa Anda dinonaktifkan. Hubungi admin.');
        }

        Auth::login($user);

        return redirect()->route('seb.exam.show', $exam);
    }

    /**
     * Siswa Menyelesaikan Ujian di SEB
     */
    public function finish(Request $request, Exam $exam)
    {
        $user = Auth::user();
        if ($user) {
            $session = ExamSession::where('exam_id', $exam->id)
                ->where('user_id', $user->id)
                ->where('status', 'ACTIVE')
                ->first();

            if ($session) {
                $session->update(['status' => 'COMPLETED']);
            }

            ExamParticipant::where('exam_id', $exam->id)
                ->where('user_id', $user->id)
                ->update([
                    'status' => 'finished',
                    'finished_at' => Carbon::now(),
                ]);

            Auth::logout();
        }

        return view('seb.finished', [
            'exam' => $exam,
            'user' => $user,
            'message' => 'Jawaban Anda telah berhasil tersimpan dan sesi ujian telah ditutup.',
        ]);
    }

    /**
     * Download File Konfigurasi .seb Standar
     */
    public function downloadConfig(Exam $exam)
    {
        $examUrl = route('seb.exam.show', $exam);

        // Format Plist XML standar Safe Exam Browser
        $plistXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>originatorVersion</key>
    <string>SEB_iOS_SMKN1Tirtamulya</string>
    <key>startURL</key>
    <string>{$examUrl}</string>
    <key>allowPreferencesWindow</key>
    <false/>
    <key>browserViewMode</key>
    <integer>0</integer>
    <key>allowQuit</key>
    <true/>
    <key>allowFlashFullscreen</key>
    <true/>
    <key>allowUserAppFolderInstall</key>
    <false/>
    <key>enableTouchExit</key>
    <false/>
    <key>browserWindowAllowReload</key>
    <false/>
    <key>showTaskBar</key>
    <false/>
    <key>showReloadButton</key>
    <false/>
</dict>
</plist>
XML;

        $fileName = 'Ujian_' . Str::slug($exam->title) . '.seb';

        return response($plistXml, 200, [
            'Content-Type' => 'application/seb',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
