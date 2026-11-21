<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Violation;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ApiExamController extends Controller
{
    public function todayExams(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();
        // Rentang 1 pekan (7 hari terakhir untuk riwayat ujian, serta jadwal hari ini dan mendatang)
        $oneWeekAgo = Carbon::now()->subDays(7)->startOfDay();
        
        $exams = Exam::where('status', 'active')
            ->where(function ($query) use ($user) {
                $query->whereHas('participants', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orWhere(function ($q) use ($user) {
                    if ($user->class_id) {
                        $q->whereHas('classes', function ($sub) use ($user) {
                            $sub->where('class_id', $user->class_id);
                        });
                    }
                })
                ->orWhere(function ($q) {
                    $q->doesntHave('classes');
                });
            })
            ->where(function ($q) use ($oneWeekAgo, $now) {
                $q->where('start_at', '>=', $oneWeekAgo)
                  ->orWhere('end_at', '>=', $oneWeekAgo)
                  ->orWhere(function ($sub) use ($now) {
                      $sub->where('start_at', '<=', $now)
                          ->where(function ($endSub) use ($now) {
                              $endSub->where('end_at', '>=', $now)
                                     ->orWhereNull('end_at');
                          });
                  });
            })
            ->get();

        // Pastikan record participant terdaftar & lampirkan status pengerjaan siswa
        foreach ($exams as $exam) {
            $participant = \App\Models\ExamParticipant::firstOrCreate([
                'exam_id' => $exam->id,
                'user_id' => $user->id,
            ], [
                'status' => 'registered'
            ]);

            // Sinkronisasi jika waktu sesi pengerjaan siswa telah habis saat aplikasi ditutup
            if ($participant->status === 'working') {
                $activeSession = ExamSession::where('exam_id', $exam->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'ACTIVE')
                    ->latest('id')
                    ->first();
                if ($activeSession && $activeSession->expired_at && $now->gt($activeSession->expired_at)) {
                    $activeSession->update(['status' => 'COMPLETED']);
                    $participant->update([
                        'status' => 'finished',
                        'finished_at' => $activeSession->expired_at,
                    ]);
                }
            }

            $exam->student_status = $participant->status;
            $exam->makeHidden(['google_form_url']);
        }

        // Urutkan prioritas tampilan:
        // 0: Sedang aktif berlangsung & belum selesai
        // 1: Jadwal mendatang / belum dimulai
        // 2: Selesai / waktu habis (riwayat 7 hari terakhir, urut tanggal terbaru)
        $exams = $exams->sortBy(function ($exam) use ($now) {
            $isFinished = ($exam->student_status === 'finished') 
                || ($exam->end_at && $now->gt($exam->end_at));
            $isOngoing = ($exam->start_at && $now->gte($exam->start_at)) 
                && (!$exam->end_at || $now->lte($exam->end_at)) 
                && !$isFinished;

            if ($isOngoing) {
                return [0, $exam->start_at ? $exam->start_at->timestamp : 0];
            }
            if (!$isFinished) {
                return [1, $exam->start_at ? $exam->start_at->timestamp : 0];
            }
            return [2, $exam->start_at ? -$exam->start_at->timestamp : 0];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $exams
        ]);
    }

    private function isStudentEligible(Exam $exam, $user): bool
    {
        return $exam->participants()->where('user_id', $user->id)->exists()
            || ($user->class_id && $exam->classes()->where('class_id', $user->class_id)->exists())
            || ($exam->classes()->count() === 0);
    }

    public function show(Request $request, $id)
    {
        $exam = Exam::where('status', 'active')->findOrFail($id);
        $user = $request->user();

        if (!$this->isStudentEligible($exam, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar sebagai peserta ujian ini.'
            ], 403);
        }
        
        // Pastikan participant terdaftar
        \App\Models\ExamParticipant::firstOrCreate([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
        ], [
            'status' => 'registered'
        ]);

        $exam->makeHidden(['google_form_url']);

        return response()->json([
            'success' => true,
            'data' => $exam
        ]);
    }

    public function startSession(Request $request, $id)
    {
        $request->validate([
            'device_id' => 'required|string|max:100',
        ]);

        $exam = Exam::where('status', 'active')->findOrFail($id);
        $user = $request->user();

        if (!$this->isStudentEligible($exam, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar sebagai peserta ujian ini.'
            ], 403);
        }

        // Validasi GPS untuk Siswa Reguler (Bukan PKL)
        if (!$user->is_pkl) {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ], [
                'latitude.required' => 'Koordinat lokasi (latitude) wajib disertakan untuk verifikasi lokasi ujian.',
                'longitude.required' => 'Koordinat lokasi (longitude) wajib disertakan untuk verifikasi lokasi ujian.',
            ]);

            $schoolLat = (float) config('exam.school_lat', -6.3400545);
            $schoolLng = (float) config('exam.school_lng', 107.4686982);
            $maxRadius = (int) config('exam.max_radius', 600);

            $earthRadius = 6371000;
            $latFrom = deg2rad($schoolLat);
            $lonFrom = deg2rad($schoolLng);
            $latTo = deg2rad((float)$request->latitude);
            $lonTo = deg2rad((float)$request->longitude);
            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;
            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            $distance = $angle * $earthRadius;

            if ($distance > $maxRadius) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda berada di luar area sekolah (' . round($distance) . ' meter dari titik sekolah, batas maks ' . $maxRadius . ' meter). Ujian hanya dapat diakses di area sekolah.',
                    'distance' => round($distance),
                    'max_radius' => $maxRadius,
                ], 403);
            }
        }

        // Check if participant is locked
        $participant = \App\Models\ExamParticipant::firstOrCreate([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
        ], [
            'status' => 'registered'
        ]);

        if ($participant && $participant->status === 'locked') {
            return response()->json([
                'success' => false,
                'message' => 'Ujian Anda sedang dikunci karena melebihi batas pelanggaran. Silakan hubungi pengawas / operator untuk membuka kunci.',
                'locked' => true
            ], 403);
        }

        if ($participant && $participant->status === 'finished') {
            return response()->json([
                'success' => false,
                'message' => 'Anda telah menyelesaikan ujian ini.',
                'finished' => true
            ], 400);
        }

        // Validate Schedule (dengan toleransi 2 menit untuk desinkronisasi jam HP siswa)
        $now = Carbon::now();
        if ($now->copy()->addMinutes(2)->lt($exam->start_at)) {
            return response()->json(['success' => false, 'message' => 'Ujian belum dimulai'], 400);
        }

        // Perhitungan waktu kedaluwarsa sesi (tidak boleh melebihi end_at ujian)
        $sessionDuration = (int)($exam->duration ?: 60);
        $calculatedExpiry = $now->copy()->addMinutes($sessionDuration);
        $expiredAt = ($exam->end_at && $calculatedExpiry->gt($exam->end_at)) ? $exam->end_at : $calculatedExpiry;

        // Cek atau buat sesi ujian (Reuse sesi AKTIF jika ada agar tidak membuat sesi ganda saat restart aplikasi)
        $session = ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->latest('id')
            ->first();

        // Jika sesi siswa sebelumnya telah melebihi batas waktu (expired_at)
        if ($session && $session->expired_at && $now->gt($session->expired_at)) {
            $session->update(['status' => 'COMPLETED']);
            $exam->participants()->where('user_id', $user->id)->update([
                'status' => 'finished',
                'finished_at' => $session->expired_at,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Waktu pengerjaan ujian Anda telah habis.',
                'finished' => true
            ], 400);
        }

        $resumedViolation = false;
        if (!$session) {
            $session = ExamSession::create([
                'exam_id' => $exam->id,
                'user_id' => $user->id,
                'device_id' => $request->device_id,
                'session_token' => Str::random(40),
                'started_at' => $now,
                'expired_at' => $expiredAt,
                'status' => 'ACTIVE'
            ]);
        } else {
            // Sesi aktif sudah berjalan di database! Berarti siswa keluar dari ujian sebelumnya (HP dimatikan / restart / aplikasi ditutup paksa).
            // Cek apakah ini re-entry legal pertama tepat setelah admin membuka kunci (unlocked_at).
            $isFreshUnlockReentry = false;
            if ($session->unlocked_at) {
                $violationsSinceUnlock = Violation::where('exam_id', $exam->id)
                    ->where('user_id', $user->id)
                    ->where('created_at', '>=', $session->unlocked_at)
                    ->count();
                if ($violationsSinceUnlock === 0) {
                    $isFreshUnlockReentry = true;
                }
            }

            if (!$isFreshUnlockReentry) {
                // Catat pelanggaran HP dimatikan / aplikasi dimulai ulang
                Violation::create([
                    'exam_id' => $exam->id,
                    'user_id' => $user->id,
                    'session_id' => $session->id,
                    'type' => 'APP_RESTART_OR_DEVICE_OFF',
                    'description' => 'HP dimatikan / aplikasi dimulai ulang saat ujian sedang berlangsung',
                ]);
                $resumedViolation = true;
            }
        }

        // Update Participant Status
        $exam->participants()->where('user_id', $user->id)->update([
            'status' => 'working',
            'started_at' => $session->started_at ?? $now,
        ]);

        // Hitung akumulasi pelanggaran aktif siswa untuk ujian ini (memperhitungkan waktu buka kunci)
        $activeViolationQuery = Violation::where('exam_id', $exam->id)
            ->where('user_id', $user->id);
        if ($session && $session->unlocked_at) {
            $activeViolationQuery->where('created_at', '>=', $session->unlocked_at);
        }
        $currentViolations = $activeViolationQuery->count();
        $maxViolation = (int)($exam->max_violation ?? 3);

        if ($currentViolations >= $maxViolation) {
            $session->update(['status' => 'LOCKED']);
            $exam->participants()->where('user_id', $user->id)->update(['status' => 'locked']);

            return response()->json([
                'success' => false,
                'message' => 'Ujian Anda sedang dikunci karena melebihi batas pelanggaran. Silakan hubungi pengawas / operator untuk membuka kunci.',
                'locked' => true,
                'violation_count' => $currentViolations,
                'max_violation' => $maxViolation,
            ], 403);
        }

        $sessionData = $session->toArray();
        $sessionData['google_form_url'] = $exam->google_form_url;
        $sessionData['server_time'] = $now->toIso8601String();
        $sessionData['violation_count'] = $currentViolations;
        $sessionData['max_violation'] = $maxViolation;
        $sessionData['resumed_violation'] = $resumedViolation;

        return response()->json([
            'success' => true,
            'data' => $sessionData
        ]);
    }

    public function finishSession(Request $request, $id)
    {
        $exam = Exam::where('status', 'active')->findOrFail($id);
        $user = $request->user();

        if (!$this->isStudentEligible($exam, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar sebagai peserta ujian ini.'
            ], 403);
        }

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->first();

        if ($session) {
            $session->update(['status' => 'COMPLETED']);
        }

        $exam->participants()->where('user_id', $user->id)->update([
            'status' => 'finished',
            'finished_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ujian selesai'
        ]);
    }

    public function reportViolation(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|integer|exists:exams,id',
            'session_id' => 'required|integer|exists:exam_sessions,id',
            'type' => 'required|string|max:50|alpha_dash',
            'description' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        // Proteksi IDOR: pastikan sesi benar-benar milik siswa & ujian yang bersangkutan
        $session = ExamSession::where('id', $request->session_id)
            ->where('user_id', $user->id)
            ->where('exam_id', $request->exam_id)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi ujian tidak valid atau bukan milik akun Anda.',
            ], 403);
        }

        if ($session->status === 'LOCKED') {
            return response()->json([
                'success' => true,
                'message' => 'Sesi ujian sudah terkunci.',
                'locked' => true,
            ]);
        }

        if ($session->status !== 'ACTIVE') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi ujian sudah berakhir.',
            ], 400);
        }

        $exam = Exam::where('status', 'active')->find($request->exam_id);
        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Ujian sudah tidak aktif.',
            ], 403);
        }

        Violation::create([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
            'session_id' => $session->id,
            'type' => strtoupper($request->type),
            'description' => $request->description,
        ]);

        // Konsisten dengan dashboard admin: hitung pelanggaran berdasarkan exam_id dan user_id
        $violationQuery = Violation::where('exam_id', $exam->id)
            ->where('user_id', $user->id);
        if ($session->unlocked_at) {
            $violationQuery->where('created_at', '>=', $session->unlocked_at);
        }
        $violationCount = $violationQuery->count();
        $maxViolation = (int)($exam->max_violation ?? 3);

        if ($violationCount >= $maxViolation) {
            $session->update(['status' => 'LOCKED']);
            $exam->participants()->where('user_id', $user->id)->update(['status' => 'locked']);

            return response()->json([
                'success' => true,
                'message' => 'Ujian dikunci karena melanggar batas maksimal',
                'locked' => true,
                'violation_count' => $violationCount,
                'max_violation' => $maxViolation,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pelanggaran dicatat',
            'locked' => false,
            'violation_count' => $violationCount,
            'max_violation' => $maxViolation,
        ]);
    }
}
