<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\User;
use App\Models\Violation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WebExamController extends Controller
{
    private function isVerifiedStudent(Request $request, ?User $user): bool
    {
        return $user
            && $user->role === 'siswa'
            && $user->status === 'active'
            && $request->session()->get('web_portal_verified') === true;
    }

    private function resolveBrowserId(Request $request): string
    {
        $browserId = (string) ($request->cookie('exasatrya_browser') ?: $request->session()->get('web_browser_id', ''));
        $browserId = preg_replace('/[^A-Za-z0-9]/', '', $browserId) ?: Str::random(12);

        $request->session()->put('web_browser_id', $browserId);
        Cookie::queue('exasatrya_browser', $browserId, 60 * 24);

        return $browserId;
    }

    private function sessionMatchesCurrentBrowser(Request $request, Exam $exam, ExamSession $session, string $browserId): bool
    {
        $sessionTokenMatch = hash_equals(
            (string) $session->session_token,
            (string) $request->session()->get("web_exam_session_token_{$exam->id}", '')
        );
        if ($sessionTokenMatch) {
            return true;
        }

        $deviceId = (string) ($session->device_id ?? '');
        if ($deviceId === "WEB_PORTAL_{$browserId}") {
            return true;
        }

        return !str_starts_with($deviceId, 'WEB_PORTAL_');
    }

    private function validateExamAccess(Exam $exam, User $user)
    {
        $backUrl = route('student.portal');

        if ($exam->status !== 'active') {
            return view('seb.error', [
                'exam' => $exam,
                'title' => 'Ujian Tidak Aktif',
                'message' => 'Ujian ini sedang nonaktif atau belum diterbitkan oleh pihak sekolah.',
                'backUrl' => $backUrl,
            ]);
        }

        if ($exam->classes()->exists()) {
            $classIds = $exam->classes()->pluck('classes.id')->all();
            if (!$user->class_id || !in_array($user->class_id, $classIds)) {
                return view('seb.error', [
                    'exam' => $exam,
                    'title' => 'Kelas Tidak Sesuai',
                    'message' => 'Ujian ini tidak ditujukan untuk kelas Anda (' . ($user->class?->name ?? 'Tanpa Kelas') . ').',
                    'backUrl' => $backUrl,
                ]);
            }
        }

        return null;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $verified = $this->isVerifiedStudent($request, $user);

        $exams = collect();
        $todayExams = collect();
        $weeklyDays = [];
        $otherExams = collect();
        $stats = [
            'today' => 0,
            'finished' => 0,
            'upcoming' => 0,
        ];
        $weekRangeLabel = '';

        if ($verified) {
            $now = Carbon::now()->locale('id');
            $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY)->locale('id');
            $weekEnd = $now->copy()->endOfWeek(Carbon::SUNDAY)->locale('id');
            $weekRangeLabel = $weekStart->translatedFormat('d M') . ' – ' . $weekStart->copy()->addDays(4)->locale('id')->translatedFormat('d M Y');

            $allExams = Exam::where('status', 'active')
                ->where(function ($q) use ($weekStart, $weekEnd, $now) {
                    $q->whereBetween('start_at', [$weekStart, $weekEnd])
                        ->orWhere('end_at', '>=', $now)
                        ->orWhereNull('end_at');
                })
                ->where(function ($q) use ($user) {
                    $q->whereDoesntHave('classes');
                    if ($user->class_id) {
                        $q->orWhereHas('classes', fn ($cq) => $cq->where('classes.id', $user->class_id));
                    }
                })
                ->orderBy('start_at')
                ->get();

            $examIds = $allExams->pluck('id');
            $participantsByExam = ExamParticipant::whereIn('exam_id', $examIds)
                ->where('user_id', $user->id)
                ->get()
                ->keyBy('exam_id');
            $sessionsByExam = ExamSession::whereIn('exam_id', $examIds)
                ->where('user_id', $user->id)
                ->orderByDesc('id')
                ->get()
                ->unique('exam_id')
                ->keyBy('exam_id');

            $allExams = $allExams->map(function (Exam $exam) use ($participantsByExam, $sessionsByExam) {
                $participant = $participantsByExam->get($exam->id);
                $latestSession = $sessionsByExam->get($exam->id);

                $exam->participant_status = ($latestSession && $latestSession->status === 'LOCKED')
                    ? 'locked'
                    : ($participant->status ?? 'registered');

                return $exam;
            });

            $exams = $allExams->filter(fn (Exam $e) => !$e->end_at || $e->end_at->gte($now))->values();

            $todayExams = $allExams->filter(function (Exam $e) use ($now) {
                $startsToday = $e->start_at && $e->start_at->isSameDay($now);
                $runningNow = $e->start_at && $e->start_at->lte($now) && (!$e->end_at || $e->end_at->gte($now));
                return $startsToday || $runningNow;
            })->values();

            $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
            foreach ($dayNames as $isoDay => $label) {
                $dateObj = $weekStart->copy()->addDays($isoDay - 1);
                $dayExams = $allExams->filter(function (Exam $e) use ($dateObj) {
                    return $e->start_at && $e->start_at->isSameDay($dateObj);
                })->values();

                $weeklyDays[] = [
                    'iso_day' => $isoDay,
                    'label' => $label,
                    'date' => $dateObj,
                    'is_today' => $dateObj->isSameDay($now),
                    'exams' => $dayExams,
                ];
            }

            $fridayEnd = $weekStart->copy()->addDays(4)->endOfDay();
            $otherExams = $allExams->filter(function (Exam $e) use ($weekStart, $fridayEnd) {
                return !$e->start_at || $e->start_at->lt($weekStart) || $e->start_at->gt($fridayEnd);
            })->values();

            $stats = [
                'today' => $todayExams->count(),
                'finished' => $allExams->where('participant_status', 'finished')->count(),
                'upcoming' => $exams->whereNotIn('participant_status', ['finished'])->count(),
            ];
        }

        return view('web-exam.portal', [
            'verified' => $verified,
            'user' => $user,
            'exams' => $exams,
            'todayExams' => $todayExams,
            'weeklyDays' => $weeklyDays,
            'otherExams' => $otherExams,
            'stats' => $stats,
            'weekRangeLabel' => $weekRangeLabel,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'username.required' => 'NIS / Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

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
        $request->session()->put('web_portal_verified', true);
        $this->resolveBrowserId($request);

        return redirect()->route('student.portal');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['web_portal_verified']);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.portal');
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();
        if (!$this->isVerifiedStudent($request, $user)) {
            return redirect()->route('student.portal')->with('error', 'Silakan login siswa terlebih dahulu.');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'regex:/^\S+$/u'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.regex' => 'Password tidak boleh mengandung spasi.',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->with('error', 'Password saat ini salah.');
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Password akun siswa berhasil diperbarui.');
    }

    public function verifyCode(Request $request, Exam $exam)
    {
        $user = Auth::user();
        if (!$this->isVerifiedStudent($request, $user)) {
            return redirect()->route('student.portal')->with('error', 'Silakan login dengan NIS siswa terlebih dahulu.');
        }

        if ($errorView = $this->validateExamAccess($exam, $user)) {
            return $errorView;
        }

        $expectedCode = strtoupper(trim((string) ($exam->access_code ?? '')));
        if ($expectedCode === '') {
            return redirect()->route('portal.exam', $exam);
        }

        $data = $request->validate([
            'access_code' => ['required', 'string', 'max:20'],
        ], [
            'access_code.required' => 'Kode ujian wajib diisi.',
        ]);

        $inputCode = strtoupper(trim($data['access_code']));
        if (!hash_equals($expectedCode, $inputCode)) {
            return back()->with('error', 'Kode ujian tidak sesuai. Minta kode aktif dari pengawas ruang.');
        }

        $request->session()->put("verified_exam_code_{$exam->id}", $expectedCode);

        return redirect()->route('portal.exam', $exam);
    }

    public function showExam(Request $request, Exam $exam)
    {
        $backUrl = route('student.portal');
        $user = Auth::user();
        if (!$this->isVerifiedStudent($request, $user)) {
            return redirect()->route('student.portal')->with('error', 'Silakan login dengan NIS siswa terlebih dahulu.');
        }

        if ($errorView = $this->validateExamAccess($exam, $user)) {
            return $errorView;
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
                'backUrl' => $backUrl,
            ]);
        }

        if ($participant->status === 'finished') {
            return view('seb.finished', [
                'exam' => $exam,
                'user' => $user,
                'message' => 'Anda telah menyelesaikan ujian ini sebelumnya.',
                'isWebPortal' => true,
                'backUrl' => $backUrl,
            ]);
        }

        $now = Carbon::now();
        if ($now->lt($exam->start_at)) {
            return view('seb.error', [
                'exam' => $exam,
                'title' => 'Ujian Belum Dimulai',
                'message' => 'Ujian dijadwalkan mulai pada ' . Carbon::parse($exam->start_at)->format('d M Y, H:i') . ' WIB.',
                'backUrl' => $backUrl,
            ]);
        }

        if ($exam->end_at && $now->gt($exam->end_at)) {
            return view('seb.error', [
                'exam' => $exam,
                'title' => 'Ujian Telah Berakhir',
                'message' => 'Batas waktu pengerjaan ujian telah berakhir pada ' . Carbon::parse($exam->end_at)->format('d M Y, H:i') . ' WIB.',
                'backUrl' => $backUrl,
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
                'isWebPortal' => true,
                'backUrl' => $backUrl,
            ]);
        }

        $browserId = $this->resolveBrowserId($request);

        if ($session && !$this->sessionMatchesCurrentBrowser($request, $exam, $session, $browserId)) {
            return view('seb.error', [
                'exam' => $exam,
                'title' => 'Sesi Aktif di Perangkat Lain',
                'message' => 'Ujian ini sedang aktif di browser atau perangkat lain. Lanjutkan di perangkat sebelumnya atau hubungi Pengawas untuk Reset Sesi.',
                'backUrl' => $backUrl,
            ]);
        }

        $expectedCode = strtoupper(trim((string) ($exam->access_code ?? '')));
        if (!$session && $expectedCode !== '') {
            $verifiedCode = strtoupper(trim((string) $request->session()->get("verified_exam_code_{$exam->id}", '')));
            if (!hash_equals($expectedCode, $verifiedCode)) {
                return view('web-exam.verify-code', [
                    'exam' => $exam,
                    'user' => $user,
                ]);
            }
        }

        if (!$session) {
            $calculatedExpiry = $now->copy()->addMinutes((int) $exam->duration);
            $expiredAt = ($exam->end_at && $calculatedExpiry->gt($exam->end_at)) ? $exam->end_at : $calculatedExpiry;
            $sessionToken = Str::random(40);

            $session = ExamSession::create([
                'exam_id' => $exam->id,
                'user_id' => $user->id,
                'device_id' => 'WEB_PORTAL_' . $browserId,
                'session_token' => $sessionToken,
                'started_at' => $now,
                'expired_at' => $expiredAt,
                'status' => 'ACTIVE',
            ]);

            $participant->update([
                'status' => 'working',
                'started_at' => $participant->started_at ?? $now,
            ]);
        }

        $request->session()->put("web_exam_session_token_{$exam->id}", $session->session_token);

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
            'isWebPortal' => true,
            'backPortalUrl' => $backUrl,
            'violationUrl' => route('portal.violation', $exam),
            'finishUrl' => route('portal.finish', $exam),
            'activeViolationCount' => $activeViolations,
        ]);
    }

    public function recordViolation(Request $request, Exam $exam)
    {
        $user = Auth::user();
        if (!$this->isVerifiedStudent($request, $user)) {
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
            'WEB_FULLSCREEN_EXIT' => 'Keluar dari mode layar penuh (Ujian Web)',
            'WEB_TAB_SWITCH' => 'Pindah tab / jendela browser (Ujian Web)',
            default => 'Meninggalkan layar ujian browser (Ujian Web)',
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
        $backUrl = route('student.portal');
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
                    'backUrl' => $backUrl,
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
            'message' => 'Jawaban Anda telah berhasil tersimpan dan sesi ujian web telah ditutup.',
            'isWebPortal' => true,
            'backUrl' => $backUrl,
        ]);
    }
}
