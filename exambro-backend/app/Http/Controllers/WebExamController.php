<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\User;
use App\Models\Violation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WebExamController extends Controller
{
    public function index(Request $request)
    {
        $enabled = AppSetting::isWebEmergencyEnabled();
        $user = Auth::user();
        $verified = $enabled && $user && $user->role === 'siswa' && $user->status === 'active' && $request->session()->get('web_exam_verified') === true;

        $exams = collect();
        if ($verified) {
            $now = Carbon::now();
            $exams = Exam::where('status', 'active')
                ->where(function ($q) use ($now) {
                    $q->where('end_at', '>=', $now)->orWhereNull('end_at');
                })
                ->where(function ($q) use ($user) {
                    $q->whereDoesntHave('classes');
                    if ($user->class_id) {
                        $q->orWhereHas('classes', fn ($cq) => $cq->where('classes.id', $user->class_id));
                    }
                })
                ->orderBy('start_at')
                ->get()
                ->map(function (Exam $exam) use ($user) {
                    $participant = ExamParticipant::where('exam_id', $exam->id)
                        ->where('user_id', $user->id)
                        ->first();
                    $latestSession = ExamSession::where('exam_id', $exam->id)
                        ->where('user_id', $user->id)
                        ->latest('id')
                        ->first();

                    $exam->participant_status = ($latestSession && $latestSession->status === 'LOCKED')
                        ? 'locked'
                        : ($participant->status ?? 'registered');

                    return $exam;
                });
        }

        return view('web-exam.portal', [
            'enabled' => $enabled,
            'verified' => $verified,
            'user' => $user,
            'exams' => $exams,
        ]);
    }

    public function login(Request $request)
    {
        if (!AppSetting::isWebEmergencyEnabled()) {
            return back()->with('error', 'Mode Web Ujian Darurat sedang dinonaktifkan oleh Admin / PIC.');
        }

        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'pin' => ['required', 'string', 'max:20'],
        ], [
            'username.required' => 'NIS / Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'pin.required' => 'Kode PIN Pengawas wajib diisi.',
        ]);

        $expectedPin = AppSetting::webEmergencyPin();
        $inputPin = strtoupper(trim($credentials['pin']));
        if ($expectedPin === '' || !hash_equals($expectedPin, $inputPin)) {
            return back()->withInput($request->only('username'))->with('error', 'Kode PIN Pengawas tidak sesuai. Minta PIN aktif kepada pengawas ruang.');
        }

        $user = User::where('username', trim($credentials['username']))
            ->where('role', 'siswa')
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withInput($request->only('username'))->with('error', 'NIS atau Password siswa salah.');
        }

        if ($user->status !== 'active') {
            return back()->withInput($request->only('username'))->with('error', 'Akun siswa Anda dinonaktifkan. Hubungi admin.');
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('web_exam_verified', true);

        return redirect()->route('web-exam.index');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('web_exam_verified');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('web-exam.index');
    }

    public function showExam(Request $request, Exam $exam)
    {
        if (!AppSetting::isWebEmergencyEnabled()) {
            return redirect()->route('web-exam.index')->with('error', 'Mode Web Ujian Darurat sedang dinonaktifkan.');
        }

        $user = Auth::user();
        if (!$user || $user->role !== 'siswa' || $user->status !== 'active' || $request->session()->get('web_exam_verified') !== true) {
            return redirect()->route('web-exam.index')->with('error', 'Silakan login dengan NIS dan PIN Pengawas terlebih dahulu.');
        }

        if ($exam->status !== 'active') {
            return view('seb.error', [
                'exam' => $exam,
                'title' => 'Ujian Tidak Aktif',
                'message' => 'Ujian ini sedang nonaktif atau belum diterbitkan oleh pihak sekolah.',
                'backUrl' => route('web-exam.index'),
            ]);
        }

        if ($exam->classes()->exists()) {
            $classIds = $exam->classes()->pluck('classes.id')->all();
            if (!$user->class_id || !in_array($user->class_id, $classIds)) {
                return view('seb.error', [
                    'exam' => $exam,
                    'title' => 'Kelas Tidak Sesuai',
                    'message' => 'Ujian ini tidak ditujukan untuk kelas Anda (' . ($user->class?->name ?? 'Tanpa Kelas') . ').',
                    'backUrl' => route('web-exam.index'),
                ]);
            }
        }

        $participant = ExamParticipant::firstOrCreate(
            ['exam_id' => $exam->id, 'user_id' => $user->id],
            ['status' => 'registered']
        );

        $latestSession = ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if ($participant->status === 'locked' || ($latestSession && $latestSession->status === 'LOCKED')) {
            return view('seb.error', [
                'exam' => $exam,
                'title' => 'Ujian Terkunci',
                'message' => 'Ujian Anda telah dikunci karena melewati batas pelanggaran. Silakan lapor ke Pengawas / PIC Jurusan untuk membuka kunci.',
                'backUrl' => route('web-exam.index'),
            ]);
        }

        if ($participant->status === 'finished') {
            return view('seb.finished', [
                'exam' => $exam,
                'user' => $user,
                'message' => 'Anda telah menyelesaikan ujian ini sebelumnya.',
                'isWebEmergency' => true,
            ]);
        }

        $now = Carbon::now();
        if ($now->lt($exam->start_at)) {
            return view('seb.error', [
                'exam' => $exam,
                'title' => 'Ujian Belum Dimulai',
                'message' => 'Ujian dijadwalkan mulai pada ' . Carbon::parse($exam->start_at)->format('d M Y, H:i') . ' WIB.',
                'backUrl' => route('web-exam.index'),
            ]);
        }

        if ($exam->end_at && $now->gt($exam->end_at)) {
            return view('seb.error', [
                'exam' => $exam,
                'title' => 'Ujian Telah Berakhir',
                'message' => 'Batas waktu pengerjaan ujian telah berakhir pada ' . Carbon::parse($exam->end_at)->format('d M Y, H:i') . ' WIB.',
                'backUrl' => route('web-exam.index'),
            ]);
        }

        $session = ($latestSession && $latestSession->status === 'ACTIVE') ? $latestSession : null;

        if ($session && $session->expired_at && $now->gt($session->expired_at)) {
            $session->update(['status' => 'COMPLETED']);
            $participant->update([
                'status' => 'finished',
                'finished_at' => $session->expired_at,
            ]);

            return view('seb.finished', [
                'exam' => $exam,
                'user' => $user,
                'message' => 'Batas waktu pengerjaan ujian Anda telah berakhir.',
                'isWebEmergency' => true,
            ]);
        }

        if (!$session) {
            $calculatedExpiry = $now->copy()->addMinutes((int) $exam->duration);
            $expiredAt = ($exam->end_at && $calculatedExpiry->gt($exam->end_at)) ? $exam->end_at : $calculatedExpiry;

            $session = ExamSession::create([
                'exam_id' => $exam->id,
                'user_id' => $user->id,
                'device_id' => 'WEB_DARURAT_' . Str::random(8),
                'session_token' => Str::random(40),
                'started_at' => $now,
                'expired_at' => $expiredAt,
                'status' => 'ACTIVE',
            ]);

            $participant->update([
                'status' => 'working',
                'started_at' => $participant->started_at ?? $now,
            ]);
        }

        $activeViolationQuery = Violation::where('exam_id', $exam->id)->where('user_id', $user->id);
        if ($session->unlocked_at) {
            $activeViolationQuery->where('created_at', '>=', $session->unlocked_at);
        }
        $activeViolations = $activeViolationQuery->count();

        $formUrl = $exam->google_form_url;
        if (!str_contains($formUrl, 'embedded=true')) {
            $formUrl .= (str_contains($formUrl, '?') ? '&' : '?') . 'embedded=true';
        }

        return view('seb.exam', [
            'exam' => $exam,
            'user' => $user,
            'session' => $session,
            'formUrl' => $formUrl,
            'isWebEmergency' => true,
            'activeViolationCount' => $activeViolations,
        ]);
    }

    public function recordViolation(Request $request, Exam $exam)
    {
        $user = Auth::user();
        if (!AppSetting::isWebEmergencyEnabled() || !$user || $user->role !== 'siswa' || $request->session()->get('web_exam_verified') !== true) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (!$session) {
            return response()->json(['locked' => false, 'violations' => 0, 'max_violation' => (int) $exam->max_violation]);
        }

        if ($session->status === 'LOCKED') {
            return response()->json([
                'locked' => true,
                'violations' => (int) $exam->max_violation,
                'max_violation' => (int) $exam->max_violation,
            ]);
        }

        if ($session->status !== 'ACTIVE') {
            return response()->json(['locked' => false, 'violations' => 0, 'max_violation' => (int) $exam->max_violation]);
        }

        $type = strtoupper(trim((string) $request->input('type', 'APP_SWITCH')));
        if (!in_array($type, ['APP_SWITCH', 'WEB_TAB_SWITCH', 'WEB_FULLSCREEN_EXIT'], true)) {
            $type = 'APP_SWITCH';
        }

        $desc = match ($type) {
            'WEB_FULLSCREEN_EXIT' => 'Keluar dari mode layar penuh (Web Darurat)',
            'WEB_TAB_SWITCH' => 'Pindah tab / jendela browser (Web Darurat)',
            default => 'Meninggalkan layar ujian browser (Web Darurat)',
        };

        Violation::create([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
            'session_id' => $session->id,
            'type' => $type,
            'description' => $desc,
        ]);

        $activeQuery = Violation::where('exam_id', $exam->id)->where('user_id', $user->id);
        if ($session->unlocked_at) {
            $activeQuery->where('created_at', '>=', $session->unlocked_at);
        }
        $activeCount = $activeQuery->count();
        $locked = $activeCount >= (int) $exam->max_violation;

        if ($locked) {
            $session->update(['status' => 'LOCKED']);
            ExamParticipant::where('exam_id', $exam->id)
                ->where('user_id', $user->id)
                ->update(['status' => 'locked']);
        }

        return response()->json([
            'locked' => $locked,
            'violations' => $activeCount,
            'max_violation' => (int) $exam->max_violation,
        ]);
    }

    public function finish(Request $request, Exam $exam)
    {
        $user = Auth::user();
        if ($user && $user->role === 'siswa') {
            $latestSession = ExamSession::where('exam_id', $exam->id)
                ->where('user_id', $user->id)
                ->latest('id')
                ->first();
            $participant = ExamParticipant::where('exam_id', $exam->id)
                ->where('user_id', $user->id)
                ->first();

            if (($participant && $participant->status === 'locked') || ($latestSession && $latestSession->status === 'LOCKED')) {
                return view('seb.error', [
                    'exam' => $exam,
                    'title' => 'Ujian Terkunci',
                    'message' => 'Ujian Anda sedang dikunci dan tidak dapat diselesaikan sebelum dibuka oleh Pengawas / PIC Jurusan.',
                    'backUrl' => route('web-exam.index'),
                ]);
            }

            if ($latestSession && $latestSession->status === 'ACTIVE') {
                $latestSession->update(['status' => 'COMPLETED']);
            }

            ExamParticipant::where('exam_id', $exam->id)
                ->where('user_id', $user->id)
                ->where('status', '!=', 'locked')
                ->update([
                    'status' => 'finished',
                    'finished_at' => Carbon::now(),
                ]);
        }

        return view('seb.finished', [
            'exam' => $exam,
            'user' => $user,
            'message' => 'Jawaban Anda telah berhasil tersimpan dan sesi ujian Web Darurat telah ditutup.',
            'isWebEmergency' => true,
        ]);
    }
}
