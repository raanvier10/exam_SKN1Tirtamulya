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
        $today = Carbon::today();
        
        $exams = Exam::where(function ($query) use ($user) {
            $query->whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orDoesntHave('participants');
        })
        ->whereDate('start_at', $today)
        ->where('status', 'active')
        ->get();

        // Pastikan record participant terdaftar & lampirkan status pengerjaan siswa
        foreach ($exams as $exam) {
            $participant = \App\Models\ExamParticipant::firstOrCreate([
                'exam_id' => $exam->id,
                'user_id' => $user->id,
            ], [
                'status' => 'registered'
            ]);
            $exam->student_status = $participant->status;
        }

        return response()->json([
            'success' => true,
            'data' => $exams
        ]);
    }

    public function show(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        $user = $request->user();
        
        // Pastikan participant terdaftar
        \App\Models\ExamParticipant::firstOrCreate([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
        ], [
            'status' => 'registered'
        ]);

        return response()->json([
            'success' => true,
            'data' => $exam
        ]);
    }

    public function startSession(Request $request, $id)
    {
        $request->validate([
            'device_id' => 'required|string',
        ]);

        $exam = Exam::findOrFail($id);
        $user = $request->user();

        // Check if participant is locked
        $participant = \App\Models\ExamParticipant::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->first();

        if ($participant && $participant->status === 'locked') {
            return response()->json([
                'success' => false,
                'message' => 'Ujian Anda sedang dikunci karena melebihi batas pelanggaran. Silakan hubungi pengawas / operator untuk membuka kunci.',
                'locked' => true
            ], 403);
        }

        // Validate Schedule
        if (Carbon::now()->lt($exam->start_at)) {
            return response()->json(['success' => false, 'message' => 'Ujian belum dimulai'], 400);
        }

        // Create Session
        $session = ExamSession::create([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
            'device_id' => $request->device_id,
            'session_token' => Str::random(40),
            'started_at' => Carbon::now(),
            'expired_at' => Carbon::now()->addMinutes((int)$exam->duration),
            'status' => 'ACTIVE'
        ]);

        // Update Participant Status
        $exam->participants()->where('user_id', $user->id)->update([
            'status' => 'working',
            'started_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $session
        ]);
    }

    public function finishSession(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        $user = $request->user();

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
            'exam_id' => 'required|exists:exams,id',
            'session_id' => 'required|exists:exam_sessions,id',
            'type' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $violation = Violation::create([
            'exam_id' => $request->exam_id,
            'user_id' => $request->user()->id,
            'session_id' => $request->session_id,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        $exam = Exam::find($request->exam_id);
        $violationCount = Violation::where('session_id', $request->session_id)->count();

        if ($violationCount >= $exam->max_violation) {
            ExamSession::where('id', $request->session_id)->update(['status' => 'LOCKED']);
            $exam->participants()->where('user_id', $request->user()->id)->update(['status' => 'locked']);

            return response()->json([
                'success' => true,
                'message' => 'Ujian dikunci karena melanggar batas maksimal',
                'locked' => true
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pelanggaran dicatat',
            'locked' => false
        ]);
    }
}
