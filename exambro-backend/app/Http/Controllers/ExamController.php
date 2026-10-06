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

        // Otomatis sinkronkan siswa dan sesi ujian yang telah berakhir menjadi finished/FINISHED
        $expiredExamIds = Exam::where('status', 'active')
            ->whereNotNull('end_at')
            ->where('end_at', '<', now())
            ->pluck('id');

        if ($expiredExamIds->isNotEmpty()) {
            \App\Models\ExamParticipant::whereIn('exam_id', $expiredExamIds)
                ->whereIn('status', ['working', 'locked'])
                ->update(['status' => 'finished']);

            \App\Models\ExamSession::whereIn('exam_id', $expiredExamIds)
                ->whereIn('status', ['ACTIVE', 'LOCKED'])
                ->update(['status' => 'FINISHED']);
        }

        $exams = $query->get();

        // Group exams by title + google_form_url + creator (Ponytail: native Laravel collection grouping)
        $groupedExams = $exams->groupBy(function ($exam) {
            return trim(mb_strtolower($exam->title)) . '|' . trim($exam->google_form_url ?? '') . '|' . ($exam->created_by ?? 'school');
        })->map(function ($group) {
            $sortedSessions = $group->sortBy('start_at')->values();
            $primary = $sortedSessions->first();
            $allClasses = $sortedSessions->flatMap->classes->unique('id')->sortBy('name')->values();
            $hasOngoing = $sortedSessions->contains(fn($e) => $e->isOngoing());
            $allExpired = $sortedSessions->every(fn($e) => $e->isExpired());
            $hasActive = $sortedSessions->contains(fn($e) => $e->status === 'active' && (!$e->end_at || now()->lte($e->end_at)));
            
            return (object) [
                'group_id' => 'grp_' . $primary->id,
                'title' => $primary->title,
                'google_form_url' => $primary->google_form_url,
                'created_by' => $primary->created_by,
                'creator' => $primary->creator,
                'primary' => $primary,
                'sessions' => $sortedSessions,
                'session_count' => $sortedSessions->count(),
                'classes' => $allClasses,
                'is_ongoing' => $hasOngoing,
                'is_expired' => $allExpired,
                'has_active' => $hasActive,
                'max_violation' => $primary->max_violation,
                'duration' => $primary->duration,
                'start_at' => $sortedSessions->first()->start_at,
                'end_at' => $sortedSessions->last()->end_at,
            ];
        })->values();

        // Deteksi tingkat kelas (X, XI, XII, UMUM)
        $detectGrades = function ($item) {
            $classes = $item->classes;
            if ($classes->isEmpty()) {
                return ['UMUM'];
            }

            $grades = [];
            foreach ($classes as $class) {
                $name = strtoupper(trim($class->name));
                if (preg_match('/(^|[\s\-_.,])(XII|12)($|[\s\-_.,])/i', $name)) {
                    $grades['XII'] = true;
                } elseif (preg_match('/(^|[\s\-_.,])(XI|11)($|[\s\-_.,])/i', $name)) {
                    $grades['XI'] = true;
                } elseif (preg_match('/(^|[\s\-_.,])(X|10)($|[\s\-_.,])/i', $name)) {
                    $grades['X'] = true;
                }
            }

            return !empty($grades) ? array_keys($grades) : ['UMUM'];
        };

        // Attach detected grades & type (UAS vs UTS)
        $groupedExams = $groupedExams->map(function ($item) use ($detectGrades) {
            $item->detected_grades = $detectGrades($item);

            // Tentukan kategori apakah UAS atau UTS:
            // Jika dibuat oleh Guru -> SELALU masuk ke UTS (Penilaian Mapel / Pengawasan Guru)
            $isTeacherCreator = ($item->creator && $item->creator->role === 'guru');
            $rawType = $item->primary->type ?? null;

            if ($isTeacherCreator) {
                $item->type = 'uts';
            } elseif (!empty($rawType) && in_array(strtolower($rawType), ['uas', 'uts'])) {
                $item->type = strtolower($rawType);
            } else {
                $item->type = preg_match('/\b(uts|pts|tengah|harian|uh)\b/i', $item->title) ? 'uts' : 'uas';
            }

            return $item;
        })->sortBy(function ($item) {
            if ($item->is_ongoing) return 0;
            if ($item->has_active) return 1;
            return 2;
        })->values();

        $classes = \App\Models\StudentClass::orderBy('name')->get();

        // 1. STRUKTUR KHUSUS UAS: DIUTAMAKAN KATEGORI KELAS
        $uasExams = $groupedExams->filter(fn($item) => $item->type === 'uas')->values();
        $uasClassGroups = collect();

        foreach ($classes as $cls) {
            $clsName = $cls->name;
            $cGrade = 'UMUM';
            if (preg_match('/(^|[\s\-_.,])(XII|12)($|[\s\-_.,])/i', $clsName)) {
                $cGrade = 'XII';
            } elseif (preg_match('/(^|[\s\-_.,])(XI|11)($|[\s\-_.,])/i', $clsName)) {
                $cGrade = 'XI';
            } elseif (preg_match('/(^|[\s\-_.,])(X|10)($|[\s\-_.,])/i', $clsName)) {
                $cGrade = 'X';
            }

            // Temukan ujian UAS untuk kelas ini
            $clsExams = $uasExams->filter(function ($item) use ($cls) {
                return $item->classes->contains('id', $cls->id);
            })->map(function ($item) use ($cls) {
                $specificSession = $item->sessions->first(function ($s) use ($cls) {
                    return $s->classes->contains('id', $cls->id);
                }) ?? $item->primary;

                $clone = clone $item;
                $clone->target_session = $specificSession;
                return $clone;
            })->values();

            if ($clsExams->isNotEmpty()) {
                $uasClassGroups->push((object) [
                    'class' => $cls,
                    'id' => $cls->id,
                    'name' => $cls->name,
                    'grade' => $cGrade,
                    'exams' => $clsExams,
                    'total_exams' => $clsExams->count(),
                    'ongoing_exams' => $clsExams->where('is_ongoing', true)->count(),
                    'active_exams' => $clsExams->where('has_active', true)->count(),
                ]);
            }
        }

        // Ujian UAS umum tanpa kelas spesifik
        $generalUasExams = $uasExams->filter(fn($item) => $item->classes->isEmpty())->values();
        if ($generalUasExams->isNotEmpty()) {
            $uasClassGroups->push((object) [
                'class' => null,
                'id' => 'umum',
                'name' => 'Semua Kelas / Umum',
                'grade' => 'UMUM',
                'exams' => $generalUasExams,
                'total_exams' => $generalUasExams->count(),
                'ongoing_exams' => $generalUasExams->where('is_ongoing', true)->count(),
                'active_exams' => $generalUasExams->where('has_active', true)->count(),
            ]);
        }

        // 2. STRUKTUR KHUSUS UTS: DIUTAMAKAN NAMA GURU PENGAMPU
        $utsExams = $groupedExams->filter(fn($item) => $item->type === 'uts')->values();
        $utsTeacherGroups = [];

        foreach ($utsExams as $item) {
            $creator = $item->creator;
            if ($creator && $creator->role !== 'admin') {
                $tId = 'teacher_' . $creator->id;
                $tName = $creator->name;
                $tRole = $creator->role;
                $tEmail = $creator->email;
                $isCurrent = (Auth::id() === $creator->id);
            } else {
                $tId = 'school_admin';
                $tName = Auth::user()->isAdmin() ? Auth::user()->name : 'Administrator (Ujian Sekolah)';
                $tRole = 'admin';
                $tEmail = ($creator && $creator->email) ? $creator->email : (Auth::user()->isAdmin() ? Auth::user()->email : 'admin@smkn1tirtamulya.online');
                $isCurrent = Auth::user()->isAdmin();
            }

            if (!isset($utsTeacherGroups[$tId])) {
                $words = preg_split('/\s+/', trim($tName));
                $initials = '';
                foreach (array_slice($words, 0, 2) as $w) {
                    $initials .= mb_strtoupper(mb_substr($w, 0, 1));
                }

                $utsTeacherGroups[$tId] = (object) [
                    'id' => $tId,
                    'name' => $tName,
                    'role' => $tRole,
                    'email' => $tEmail,
                    'initials' => $initials ?: 'GR',
                    'is_current' => $isCurrent,
                    'total_exams' => 0,
                    'active_exams' => 0,
                    'ongoing_exams' => 0,
                    'grades' => [
                        'X' => (object) ['label' => 'Kelas X', 'exams' => collect()],
                        'XI' => (object) ['label' => 'Kelas XI', 'exams' => collect()],
                        'XII' => (object) ['label' => 'Kelas XII', 'exams' => collect()],
                        'UMUM' => (object) ['label' => 'Semua Tingkat', 'exams' => collect()],
                    ]
                ];
            }

            $utsTeacherGroups[$tId]->total_exams++;
            if ($item->is_ongoing) {
                $utsTeacherGroups[$tId]->ongoing_exams++;
            } elseif ($item->has_active) {
                $utsTeacherGroups[$tId]->active_exams++;
            }

            $grades = $detectGrades($item);
            foreach ($grades as $g) {
                if (isset($utsTeacherGroups[$tId]->grades[$g])) {
                    $utsTeacherGroups[$tId]->grades[$g]->exams->push($item);
                }
            }
        }

        // Seluruh Guru untuk Mode Guru Umum / Kompatibilitas
        $teacherGroups = [];
        foreach ($groupedExams as $item) {
            $creator = $item->creator;
            if ($creator && $creator->role !== 'admin') {
                $tId = 'teacher_' . $creator->id;
                $tName = $creator->name;
                $tRole = $creator->role;
                $tEmail = $creator->email;
                $isCurrent = (Auth::id() === $creator->id);
            } else {
                $tId = 'school_admin';
                $tName = Auth::user()->isAdmin() ? Auth::user()->name : 'Administrator (Ujian Sekolah)';
                $tRole = 'admin';
                $tEmail = ($creator && $creator->email) ? $creator->email : (Auth::user()->isAdmin() ? Auth::user()->email : 'admin@smkn1tirtamulya.online');
                $isCurrent = Auth::user()->isAdmin();
            }

            if (!isset($teacherGroups[$tId])) {
                $words = preg_split('/\s+/', trim($tName));
                $initials = '';
                foreach (array_slice($words, 0, 2) as $w) {
                    $initials .= mb_strtoupper(mb_substr($w, 0, 1));
                }

                $teacherGroups[$tId] = (object) [
                    'id' => $tId,
                    'name' => $tName,
                    'role' => $tRole,
                    'email' => $tEmail,
                    'initials' => $initials ?: 'GR',
                    'is_current' => $isCurrent,
                    'total_exams' => 0,
                    'active_exams' => 0,
                    'ongoing_exams' => 0,
                    'grades' => [
                        'X' => (object) ['label' => 'Kelas X', 'exams' => collect()],
                        'XI' => (object) ['label' => 'Kelas XI', 'exams' => collect()],
                        'XII' => (object) ['label' => 'Kelas XII', 'exams' => collect()],
                        'UMUM' => (object) ['label' => 'Semua Tingkat', 'exams' => collect()],
                    ]
                ];
            }

            $teacherGroups[$tId]->total_exams++;
            if ($item->is_ongoing) {
                $teacherGroups[$tId]->ongoing_exams++;
            } elseif ($item->has_active) {
                $teacherGroups[$tId]->active_exams++;
            }

            $grades = $detectGrades($item);
            foreach ($grades as $g) {
                if (isset($teacherGroups[$tId]->grades[$g])) {
                    $teacherGroups[$tId]->grades[$g]->exams->push($item);
                }
            }
        }

        // Jika guru login, sertakan profilnya di kelompok guru agar kartu profil dan tombol buat ujian selalu muncul
        if (Auth::user()->isTeacher()) {
            $current = Auth::user();
            $words = preg_split('/\s+/', trim($current->name));
            $initials = '';
            foreach (array_slice($words, 0, 2) as $w) {
                $initials .= mb_strtoupper(mb_substr($w, 0, 1));
            }
            $emptyProfile = (object) [
                'id' => 'teacher_' . $current->id,
                'name' => $current->name,
                'role' => $current->role,
                'email' => $current->email,
                'initials' => $initials ?: 'GR',
                'is_current' => true,
                'total_exams' => 0,
                'active_exams' => 0,
                'ongoing_exams' => 0,
                'grades' => [
                    'X' => (object) ['label' => 'Kelas X', 'exams' => collect()],
                    'XI' => (object) ['label' => 'Kelas XI', 'exams' => collect()],
                    'XII' => (object) ['label' => 'Kelas XII', 'exams' => collect()],
                    'UMUM' => (object) ['label' => 'Semua Tingkat', 'exams' => collect()],
                ]
            ];

            if (!isset($teacherGroups['teacher_' . $current->id])) {
                $teacherGroups['teacher_' . $current->id] = clone $emptyProfile;
            }
            if (!isset($utsTeacherGroups['teacher_' . $current->id])) {
                $utsTeacherGroups['teacher_' . $current->id] = clone $emptyProfile;
            }
        }

        $teacherGroups = collect($teacherGroups)->sortBy(function ($t) {
            if ($t->is_current) return 0;
            if ($t->role === 'admin') return 1;
            return 2 . $t->name;
        })->values();

        $utsTeacherGroups = collect($utsTeacherGroups)->sortBy(function ($t) {
            if ($t->is_current) return 0;
            if ($t->role === 'admin') return 1;
            return 2 . $t->name;
        })->values();

        return view('admin.exams.index', compact(
            'exams', 
            'groupedExams', 
            'teacherGroups', 
            'classes',
            'uasClassGroups',
            'utsTeacherGroups',
            'uasExams',
            'utsExams'
        ));
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
            'type' => 'nullable|in:uas,uts',
            'description' => 'nullable|string',
            'google_form_url' => 'required|url',
            'duration' => 'required|integer|min:1',
            'max_violation' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
            'schedules' => 'required|array|min:1',
            'schedules.*.date' => 'required|date',
            'schedules.*.time' => 'required|date_format:H:i',
            'schedules.*.duration' => 'nullable|integer|min:1',
            'schedules.*.classes' => 'required|array|min:1',
            'schedules.*.classes.*' => 'exists:classes,id',
        ], [
            'schedules.required' => 'Tambahkan minimal 1 jadwal.',
            'schedules.*.classes.required' => 'Setiap jadwal wajib memilih minimal 1 kelas target.',
            'schedules.*.classes.min' => 'Setiap jadwal wajib memilih minimal 1 kelas target.',
        ]);

        if (Auth::user()->isTeacher()) {
            $examType = 'uts';
        } else {
            $examType = $request->input('type') ?: (preg_match('/\b(uts|pts|tengah)/i', $validated['title']) ? 'uts' : 'uas');
        }

        $createdCount = 0;
        foreach ($validated['schedules'] as $slot) {
            $slotDuration = !empty($slot['duration']) ? (int)$slot['duration'] : (int)$validated['duration'];
            $start_at = \Carbon\Carbon::parse($slot['date'] . ' ' . $slot['time']);
            $end_at = $start_at->copy()->addMinutes($slotDuration);

            $exam = Exam::create([
                'title' => $validated['title'],
                'type' => $examType,
                'description' => $validated['description'] ?? null,
                'google_form_url' => $validated['google_form_url'],
                'start_at' => $start_at,
                'end_at' => $end_at,
                'duration' => $slotDuration,
                'max_violation' => $validated['max_violation'],
                'status' => $validated['status'],
                'created_by' => Auth::id(),
            ]);

            // Hubungkan HANYA kelas yang dipilih pada slot jadwal ini
            $exam->classes()->sync($slot['classes']);

            // Daftarkan HANYA siswa yang berada di kelas jadwal ini
            $slotStudents = \App\Models\User::where('role', 'siswa')
                ->where('status', 'active')
                ->whereIn('class_id', $slot['classes'])
                ->get();

            foreach ($slotStudents as $student) {
                \App\Models\ExamParticipant::firstOrCreate([
                    'exam_id' => $exam->id,
                    'user_id' => $student->id,
                ], [
                    'status' => 'registered'
                ]);
            }
            $createdCount++;
        }

        $msg = $createdCount > 1
            ? "Berhasil membuat {$createdCount} jadwal ujian spesifik per kelas."
            : 'Ujian berhasil dibuat.';

        return redirect()->route('admin.exams.index')->with('success', $msg);
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

        // Jika waktu ujian sudah berakhir, otomatis ubah siswa yang masih 'working' atau 'locked' menjadi 'finished'
        if ($exam->end_at && now()->gt($exam->end_at)) {
            \App\Models\ExamParticipant::where('exam_id', $exam->id)
                ->whereIn('status', ['working', 'locked'])
                ->update(['status' => 'finished']);

            \App\Models\ExamSession::where('exam_id', $exam->id)
                ->whereIn('status', ['ACTIVE', 'LOCKED'])
                ->update(['status' => 'FINISHED']);
        }

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
            })
            ->sortBy(function ($participant) {
                $isLocked = ($participant->status === 'locked' || (isset($participant->session) && $participant->session->status === 'LOCKED'));

                // Prioritas urutan:
                // 0 = TERKUNCI (LOCKED) -> Selalu di paling atas agar proctor/guru langsung lihat tanpa repot cari
                // 1 = Sedang Mengerjakan dengan pelanggaran (makin banyak pelanggaran makin di atas)
                // 2 = Sedang Mengerjakan normal
                // 3 = Belum Mulai (registered)
                // 4 = Selesai (finished)
                if ($isLocked) {
                    $rank = 0;
                } elseif ($participant->status === 'working') {
                    $rank = $participant->violation_count > 0 ? 1 : 2;
                } elseif ($participant->status === 'registered') {
                    $rank = 3;
                } else {
                    $rank = 4;
                }

                $className = strtolower($participant->user->class->name ?? 'tanpa_kelas');
                $violationScore = 999 - min(999, (int)($participant->violation_count ?? 0));
                $name = strtolower($participant->user->name ?? '');

                return sprintf('%d_%s_%03d_%s', $rank, $className, $violationScore, $name);
            })
            ->values();

        $violations = \App\Models\Violation::where('exam_id', $exam->id)
            ->with(['user.class', 'session'])
            ->latest('id')
            ->get();

        $stats = [
            'total_participants' => $participants->count(),
            'registered' => $participants->where('status', 'registered')->count(),
            'working' => $participants->where('status', 'working')->count(),
            'locked' => $participants->filter(function ($p) {
                return $p->status === 'locked' || (isset($p->session) && $p->session->status === 'LOCKED');
            })->count(),
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
            'type' => 'nullable|in:uas,uts',
            'description' => 'nullable|string',
            'google_form_url' => 'required|url',
            'start_at' => 'required|date',
            'duration' => 'required|integer|min:1',
            'max_violation' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
            'classes' => 'required|array|min:1',
            'classes.*' => 'exists:classes,id',
        ], [
            'classes.required' => 'Pilih minimal 1 kelas target.',
            'classes.min' => 'Pilih minimal 1 kelas target.',
        ]);

        if (Auth::user()->isTeacher()) {
            $validated['type'] = 'uts';
        }

        $validated['end_at'] = \Carbon\Carbon::parse($validated['start_at'])->addMinutes((int)$validated['duration']);

        $exam->update($validated);

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
                $typeLabel = match (strtoupper($v->type ?? '')) {
                    'SCREEN_OFF' => 'Layar Dimatikan (Screen Off)',
                    'APP_RESTART_OR_DEVICE_OFF', 'DEVICE_OFF', 'REBOOT' => 'HP Dimatikan / Dimulai Ulang (Restart / Off)',
                    'APP_SWITCH', 'SWITCH', 'APP' => 'Keluar Aplikasi (App Switch)',
                    'FLOATING_WINDOW' => 'Jendela Mengambang (Floating Window)',
                    'SPLIT_SCREEN', 'SPLIT', 'MULTI_WINDOW' => 'Layar Belah (Split Screen)',
                    'SCREEN_RECORDING' => 'Perekaman Layar (Screen Recording)',
                    'SCREENSHOT' => 'Tangkapan Layar (Screenshot)',
                    'UNAUTHORIZED_URL', 'URL' => 'Akses URL Terlarang / Browsing',
                    'UNAUTHORIZED_COPY', 'COPY' => 'Salin Soal (Copy Attempt)',
                    default => $v->type ?? 'Keluar Aplikasi'
                };

                fputcsv($file, [
                    $v->created_at ? $v->created_at->format('Y-m-d H:i:s') : '-',
                    $sanitizeCsv($v->user?->username ?? '-'),
                    $sanitizeCsv($v->user?->name ?? '-'),
                    $sanitizeCsv($v->user?->class?->name ?? 'Tanpa Kelas'),
                    $sanitizeCsv($typeLabel),
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

        if ($exam->isOngoing()) {
            return redirect()->route('admin.exams.index')->with('error', 'Ujian "' . $exam->title . '" sedang berlangsung dan tidak dapat dihapus!');
        }

        $exam->delete();
        return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil dihapus');
    }

    public function destroyBulk(Request $request)
    {
        $request->validate([
            'exam_ids' => 'required|array|min:1',
            'exam_ids.*' => 'exists:exams,id',
        ]);

        $query = Exam::whereIn('id', $request->exam_ids);

        // Guru hanya bisa hapus ujian buatannya sendiri
        if (Auth::user()->isTeacher()) {
            $query->where('created_by', Auth::id());
        }

        $exams = $query->get();
        $deletableIds = [];
        $ongoingCount = 0;

        foreach ($exams as $exam) {
            if ($exam->isOngoing()) {
                $ongoingCount++;
            } else {
                $deletableIds[] = $exam->id;
            }
        }

        if (empty($deletableIds)) {
            return redirect()->route('admin.exams.index')->with('error', 'Semua jadwal ujian yang dipilih sedang berlangsung dan tidak dapat dihapus.');
        }

        Exam::whereIn('id', $deletableIds)->delete();

        $count = count($deletableIds);
        $msg = "Berhasil menghapus {$count} jadwal ujian.";
        if ($ongoingCount > 0) {
            $msg .= " ({$ongoingCount} jadwal dilewati karena sedang berlangsung).";
        }

        return redirect()->route('admin.exams.index')->with('success', $msg);
    }

    public function import(\Illuminate\Http\Request $request)
    {
        $request->validate(['file' => 'required|file']);
        $rows = \App\Helpers\SimpleSpreadsheetReader::read($request->file('file'));
        $count = 0;
        $skipped = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($rows, &$count, &$skipped) {
            foreach ($rows as $row) {
                if (!empty($row['judul_ujian']) && !empty($row['url_google_form'])) {
                    $start_at = !empty($row['waktu_mulai']) ? \Carbon\Carbon::parse($row['waktu_mulai']) : now();
                    $duration = (int)($row['durasi_menit'] ?? 60);

                    // ponytail: dedup by composite key — skip if exact same exam already exists
                    $exists = \App\Models\Exam::where('title', $row['judul_ujian'])
                        ->where('google_form_url', $row['url_google_form'])
                        ->where('start_at', $start_at)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    $examType = Auth::user()->isTeacher() ? 'uts' : (
                        !empty($row['tipe']) && in_array(strtolower($row['tipe']), ['uas', 'uts']) ? strtolower($row['tipe']) : (
                            preg_match('/\b(uts|pts|tengah)/i', $row['judul_ujian']) ? 'uts' : 'uas'
                        )
                    );

                    $exam = \App\Models\Exam::create([
                        'title' => $row['judul_ujian'],
                        'type' => $examType,
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
                                ->orderBy('class_id')
                                ->orderBy('name')
                                ->get();
                        } else {
                            $students = \App\Models\User::where('role', 'siswa')
                                ->where('status', 'active')
                                ->orderBy('class_id')
                                ->orderBy('name')
                                ->get();
                        }
                    } else {
                        $students = \App\Models\User::where('role', 'siswa')
                            ->where('status', 'active')
                            ->orderBy('class_id')
                            ->orderBy('name')
                            ->get();
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

        $msg = "Berhasil mengimpor {$count} jadwal ujian baru.";
        if ($skipped > 0) {
            $msg .= " {$skipped} data sudah ada sebelumnya (dilewati).";
        }

        return back()->with('success', $msg);
    }
}
