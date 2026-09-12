<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $query = Exam::with(['classes', 'participants', 'creator'])->latest('start_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('classes', function($q) use ($request) {
                $q->where('classes.id', $request->class_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ISOLASI KETAT ANTAR GURU:
        // Guru hanya melihat ujian miliknya sendiri + ujian umum sekolah (UAS oleh Admin).
        // Guru A TIDAK BISA melihat ujian buatan Guru B.
        if (Auth::user()->isTeacher()) {
            $query->where(function ($q) {
                $q->where('created_by', Auth::id())
                  ->orWhereNull('created_by')
                  ->orWhereHas('creator', function ($sq) {
                      $sq->where('role', 'admin');
                  });
            });
        }

        if ($request->filled('scope')) {
            if ($request->scope === 'my') {
                $query->where('created_by', Auth::id());
            } elseif ($request->scope === 'school') {
                $query->where(function ($q) {
                    $q->whereNull('created_by')
                      ->orWhereHas('creator', function ($sq) {
                          $sq->where('role', 'admin');
                      });
                });
            }
        }

        $exams = $query->get();
        $classes = \App\Models\StudentClass::orderBy('name')->get();

        return view('admin.exams.index', compact('exams', 'classes'));
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
        $validated['created_by'] = Auth::id();

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
        if (Auth::user()->isTeacher()) {
            $isOwn = $exam->created_by === Auth::id();
            $isSchoolExam = $exam->created_by === null || ($exam->creator && $exam->creator->isAdmin());
            if (!$isOwn && !$isSchoolExam) {
                return redirect()->route('admin.exams.index')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk memantau ujian milik guru lain.');
            }
        }

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
        // Untuk UAS (dibuat kurikulum/admin), buka kunci HANYA bisa dilakukan oleh Kurikulum/Admin.
        // Guru hanya bisa buka kunci pada ujian PTS yang dibuatnya sendiri.
        if (Auth::user()->isTeacher() && $exam->created_by !== Auth::id()) {
            return back()->with('error', 'Akses ditolak. Buka kunci untuk ujian sekolah (UAS) hanya dapat dilakukan langsung oleh Kurikulum / Administrator.');
        }

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
        // Untuk UAS, reset sesi HANYA bisa dilakukan oleh Kurikulum/Admin.
        if (Auth::user()->isTeacher() && $exam->created_by !== Auth::id()) {
            return back()->with('error', 'Akses ditolak. Reset sesi ujian sekolah (UAS) hanya dapat dilakukan langsung oleh Kurikulum / Administrator.');
        }

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
        if (Auth::user()->isTeacher() && $exam->created_by && $exam->created_by !== Auth::id()) {
            return redirect()->route('admin.exams.index')->with('error', 'Anda hanya dapat mengubah jadwal ujian yang Anda buat sendiri.');
        }

        $classes = \App\Models\StudentClass::orderBy('name')->get();
        $selectedClasses = $exam->classes->pluck('id')->toArray();
        return view('admin.exams.edit', compact('exam', 'classes', 'selectedClasses'));
    }

    public function update(Request $request, Exam $exam)
    {
        if (Auth::user()->isTeacher() && $exam->created_by && $exam->created_by !== Auth::id()) {
            return redirect()->route('admin.exams.index')->with('error', 'Anda hanya dapat mengubah jadwal ujian yang Anda buat sendiri.');
        }

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

            $sanitizeCsv = function ($value) {
                $str = (string)$value;
                if (!empty($str) && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"])) {
                    return "'" . $str;
                }
                return $str;
            };

            foreach ($violations as $v) {
                fputcsv($file, [
                    $v->created_at ? $v->created_at->format('Y-m-d H:i:s') : '-',
                    $sanitizeCsv($v->user?->username ?? '-'),
                    $sanitizeCsv($v->user?->name ?? '-'),
                    $sanitizeCsv($v->user?->class?->name ?? 'Tanpa Kelas'),
                    $sanitizeCsv($v->type ?? 'EXIT_APP'),
                    $sanitizeCsv($v->description ?? '-'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function quickUpdateGoogleForm(Request $request, Exam $exam)
    {
        if (Auth::user()->isTeacher() && $exam->created_by !== Auth::id()) {
            return back()->with('error', 'Anda hanya dapat mengubah link form ujian yang Anda buat sendiri.');
        }

        $request->validate([
            'google_form_url' => 'required|url',
        ], [
            'google_form_url.required' => 'URL Google Form wajib diisi.',
            'google_form_url.url' => 'Format URL Google Form tidak valid (harus diawali http:// atau https://).',
        ]);

        $exam->update([
            'google_form_url' => $request->google_form_url,
        ]);

        return back()->with('success', 'Link Google Form ujian berhasil diperbarui.');
    }

    public function destroy(Exam $exam)
    {
        if (Auth::user()->isTeacher() && $exam->created_by && $exam->created_by !== Auth::id()) {
            return redirect()->route('admin.exams.index')->with('error', 'Anda hanya dapat menghapus jadwal ujian yang Anda buat sendiri.');
        }

        $exam->delete();
        return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil dihapus');
    }

    public function import(\Illuminate\Http\Request $request)
    {
        $request->validate(['file' => 'required|file']);
        $rows = \App\Helpers\SimpleSpreadsheetReader::read($request->file('file'));
        $count = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($rows, &$count) {
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
                        'created_by' => Auth::id(),
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
        });

        return back()->with('success', "Berhasil mengimpor {$count} jadwal ujian.");
    }
}
