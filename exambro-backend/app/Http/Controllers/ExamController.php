<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::all();
        return view('admin.exams.index', compact('exams'));
    }

    public function create()
    {
        return view('admin.exams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'google_form_url' => 'required|url',
            'start_at' => 'required|date',
            'duration' => 'required|integer|min:1',
            'max_violation' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['end_at'] = \Carbon\Carbon::parse($validated['start_at'])->addMinutes((int)$validated['duration']);

        $exam = Exam::create($validated);

        // Auto daftarkan semua siswa aktif sebagai peserta ujian
        $students = \App\Models\User::where('role', 'siswa')->where('status', 'active')->get();
        foreach ($students as $student) {
            \App\Models\ExamParticipant::firstOrCreate([
                'exam_id' => $exam->id,
                'user_id' => $student->id,
            ], [
                'status' => 'registered'
            ]);
        }

        return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil dibuat');
    }

    public function show(Exam $exam)
    {
        $participants = $exam->participants()
            ->with(['user.class'])
            ->get()
            ->map(function ($participant) use ($exam) {
                $latestSession = \App\Models\ExamSession::where('exam_id', $exam->id)
                    ->where('user_id', $participant->user_id)
                    ->latest('id')
                    ->first();

                $violationCount = \App\Models\Violation::where('exam_id', $exam->id)
                    ->where('user_id', $participant->user_id)
                    ->count();

                $participant->session = $latestSession;
                $participant->violation_count = $violationCount;
                return $participant;
            });

        $violations = \App\Models\Violation::where('exam_id', $exam->id)
            ->with(['user.class', 'session'])
            ->latest('id')
            ->get();

        $stats = [
            'total_participants' => $participants->count(),
            'registered' => $participants->where('status', 'registered')->count(),
            'working' => $participants->where('status', 'working')->count(),
            'locked' => $participants->where('status', 'locked')->count(),
            'finished' => $participants->where('status', 'finished')->count(),
            'total_violations' => $violations->count(),
        ];

        return view('admin.exams.show', compact('exam', 'participants', 'violations', 'stats'));
    }

    public function unlockStudent(Exam $exam, \App\Models\User $user)
    {
        // Update participant status back to working
        \App\Models\ExamParticipant::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->update(['status' => 'working']);

        // Update active/locked sessions back to ACTIVE
        \App\Models\ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('status', 'LOCKED')
            ->update(['status' => 'ACTIVE']);

        // Reset violation records so counter is cleared
        \App\Models\Violation::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', "Kunci ujian untuk siswa {$user->name} berhasil dibuka.");
    }

    public function resetStudentSession(Exam $exam, \App\Models\User $user)
    {
        // Reset participant status
        \App\Models\ExamParticipant::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->update([
                'status' => 'registered',
                'started_at' => null,
                'finished_at' => null,
            ]);

        // Delete session and violations
        \App\Models\ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->delete();

        \App\Models\Violation::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', "Sesi pengerjaan siswa {$user->name} telah di-reset ke awal.");
    }

    public function edit(Exam $exam)
    {
        return view('admin.exams.edit', compact('exam'));
    }

    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'google_form_url' => 'required|url',
            'start_at' => 'required|date',
            'duration' => 'required|integer|min:1',
            'max_violation' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['end_at'] = \Carbon\Carbon::parse($validated['start_at'])->addMinutes((int)$validated['duration']);

        $exam->update($validated);
        return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil diupdate');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil dihapus');
    }

    public function import(\Illuminate\Http\Request $request)
    {
        $request->validate(['file' => 'required|file']);
        $rows = \App\Helpers\SimpleSpreadsheetReader::read($request->file('file'));
        $count = 0;
        $students = \App\Models\User::where('role', 'siswa')->where('status', 'active')->get();

        foreach ($rows as $row) {
            if (!empty($row['judul_ujian']) && !empty($row['url_google_form'])) {
                $start_at = !empty($row['waktu_mulai']) ? \Carbon\Carbon::parse($row['waktu_mulai']) : now();
                $duration = (int)($row['durasi_menit'] ?? 60);
                $exam = \App\Models\Exam::create([
                    'title' => $row['judul_ujian'],
                    'description' => $row['deskripsi'] ?? null,
                    'google_form_url' => $row['url_google_form'],
                    'start_at' => $start_at,
                    'end_at' => $start_at->copy()->addMinutes($duration),
                    'duration' => $duration,
                    'max_violation' => (int)($row['maksimal_pelanggaran'] ?? 3),
                    'status' => strtolower($row['status'] ?? 'active'),
                ]);

                // Auto register students
                foreach ($students as $student) {
                    \App\Models\ExamParticipant::firstOrCreate([
                        'exam_id' => $exam->id,
                        'user_id' => $student->id,
                    ], [
                        'status' => 'registered'
                    ]);
                }

                $count++;
            }
        }
        return back()->with('success', "Berhasil mengimpor {$count} jadwal ujian.");
    }
}
