<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::with(['classes', 'participants'])->latest('start_at')->get();
        return view('admin.exams.index', compact('exams'));
    }

    public function create()
    {
        $classes = \App\Models\StudentClass::orderBy('name')->get();
        return view('admin.exams.create', compact('classes'));
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
            'classes' => 'nullable|array',
            'classes.*' => 'exists:classes,id',
        ]);

        $validated['end_at'] = \Carbon\Carbon::parse($validated['start_at'])->addMinutes((int)$validated['duration']);

        $exam = Exam::create($validated);

        if (!empty($request->classes)) {
            $exam->classes()->sync($request->classes);
            $students = \App\Models\User::where('role', 'siswa')
                ->where('status', 'active')
                ->whereIn('class_id', $request->classes)
                ->get();
        } else {
            $students = \App\Models\User::where('role', 'siswa')
                ->where('status', 'active')
                ->get();
        }

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
        $exam->load('classes');
        $participants = $exam->participants()
            ->with(['user.class'])
            ->get()
            ->map(function ($participant) use ($exam) {
                $latestSession = \App\Models\ExamSession::where('exam_id', $exam->id)
                    ->where('user_id', $participant->user_id)
                    ->latest('id')
                    ->first();

                $totalViolations = \App\Models\Violation::where('exam_id', $exam->id)
                    ->where('user_id', $participant->user_id)
                    ->count();

                $activeViolationQuery = \App\Models\Violation::where('exam_id', $exam->id)
                    ->where('user_id', $participant->user_id);
                if ($latestSession && $latestSession->unlocked_at) {
                    $activeViolationQuery->where('created_at', '>=', $latestSession->unlocked_at);
                }
                $activeViolationCount = $activeViolationQuery->count();

                $participant->session = $latestSession;
                $participant->violation_count = $activeViolationCount;
                $participant->total_violation_count = $totalViolations;
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

        // Update active/locked sessions back to ACTIVE and record unlock timestamp
        \App\Models\ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('status', 'LOCKED')
            ->update([
                'status' => 'ACTIVE',
                'unlocked_at' => \Carbon\Carbon::now(),
            ]);

        // Catatan riwayat pelanggaran TIDAK dihapus agar tetap tersimpan untuk export / rekap

        return back()->with('success', "Kunci ujian untuk siswa {$user->name} berhasil dibuka. Riwayat pelanggaran tetap tersimpan.");
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
        $classes = \App\Models\StudentClass::orderBy('name')->get();
        $selectedClasses = $exam->classes->pluck('id')->toArray();
        return view('admin.exams.edit', compact('exam', 'classes', 'selectedClasses'));
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
            'classes' => 'nullable|array',
            'classes.*' => 'exists:classes,id',
        ]);

        $validated['end_at'] = \Carbon\Carbon::parse($validated['start_at'])->addMinutes((int)$validated['duration']);

        $exam->update($validated);

        if (!empty($request->classes)) {
            $exam->classes()->sync($request->classes);
            $targetStudents = \App\Models\User::where('role', 'siswa')
                ->where('status', 'active')
                ->whereIn('class_id', $request->classes)
                ->get();
            
            $targetStudentIds = $targetStudents->pluck('id')->toArray();

            // Daftarkan siswa baru yang belum terdaftar
            foreach ($targetStudents as $student) {
                \App\Models\ExamParticipant::firstOrCreate([
                    'exam_id' => $exam->id,
                    'user_id' => $student->id,
                ], [
                    'status' => 'registered'
                ]);
            }

            // Hapus peserta yang kelasnya tidak lagi terpilih HANYA jika statusnya masih 'registered'
            \App\Models\ExamParticipant::where('exam_id', $exam->id)
                ->whereNotIn('user_id', $targetStudentIds)
                ->where('status', 'registered')
                ->delete();
        } else {
            $exam->classes()->detach();
            $allStudents = \App\Models\User::where('role', 'siswa')->where('status', 'active')->get();
            foreach ($allStudents as $student) {
                \App\Models\ExamParticipant::firstOrCreate([
                    'exam_id' => $exam->id,
                    'user_id' => $student->id,
                ], [
                    'status' => 'registered'
                ]);
            }
        }

        return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil diupdate');
    }

    public function exportViolations(Exam $exam)
    {
        $violations = \App\Models\Violation::where('exam_id', $exam->id)
            ->with(['user.class', 'session'])
            ->latest('id')
            ->get();

        $filename = 'rekap_pelanggaran_' . \Illuminate\Support\Str::slug($exam->title) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($violations) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($file, [
                'Waktu Kejadian',
                'NIS / Username',
                'Nama Siswa',
                'Kelas',
                'Jenis Pelanggaran',
                'Keterangan'
            ]);

            foreach ($violations as $v) {
                fputcsv($file, [
                    $v->created_at ? $v->created_at->format('Y-m-d H:i:s') : '-',
                    $v->user?->username ?? '-',
                    $v->user?->name ?? '-',
                    $v->user?->class?->name ?? 'Tanpa Kelas',
                    $v->type ?? 'EXIT_APP',
                    $v->description ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

                // Cek apakah ada target_kelas / kelas di kolom import
                $targetClassNames = !empty($row['target_kelas']) ? $row['target_kelas'] : (!empty($row['kelas']) ? $row['kelas'] : null);
                
                if ($targetClassNames) {
                    $classNamesArray = array_filter(array_map('trim', explode(',', $targetClassNames)));
                    $classIds = [];
                    foreach ($classNamesArray as $cName) {
                        $classModel = \App\Models\StudentClass::firstOrCreate(['name' => $cName]);
                        $classIds[] = $classModel->id;
                    }
                    
                    if (!empty($classIds)) {
                        $exam->classes()->sync($classIds);
                        $students = \App\Models\User::where('role', 'siswa')
                            ->where('status', 'active')
                            ->whereIn('class_id', $classIds)
                            ->get();
                    } else {
                        $students = \App\Models\User::where('role', 'siswa')->where('status', 'active')->get();
                    }
                } else {
                    $students = \App\Models\User::where('role', 'siswa')->where('status', 'active')->get();
                }

                // Register peserta ujian
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
