<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    /**
     * Kembalikan array ID kelas jurusan jika user adalah PIC jurusan, atau null jika bukan PIC.
     *
     * @return array<int>|null
     */
    private function departmentClassIds(): ?array
    {
        return Auth::user()?->departmentClassIds();
    }

    private function canPicManageFullExam(Exam $exam, ?array $deptClassIds): bool
    {
        if ($deptClassIds === null) {
            return true;
        }

        $examClassIds = $exam->classes()->pluck('classes.id')->map(fn ($id) => (int) $id)->all();
        if (empty($examClassIds)) {
            return false;
        }

        return empty(array_diff($examClassIds, $deptClassIds));
    }

    public function index(Request $request)
    {
        $deptClassIds = $this->departmentClassIds();
        $query = Exam::with(['classes', 'creator'])
            ->withExists(['sessions as has_active_session' => fn ($q) => $q->whereIn('status', ['ACTIVE', 'LOCKED'])])
            ->latest('start_at');

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

        // ISOLASI PIC JURUSAN:
        // Hanya tampilkan jadwal ujian yang menargetkan minimal 1 kelas di jurusan PIC.
        if ($deptClassIds !== null) {
            $query->whereHas('classes', function ($q) use ($deptClassIds) {
                $q->whereIn('classes.id', $deptClassIds);
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
                ->update(['status' => 'COMPLETED']);
        }

        $exams = $query->get();

        // Group exams by title + google_form_url + creator (Ponytail: native Laravel collection grouping)
        $groupedExams = $exams->groupBy(function ($exam) {
            return trim(mb_strtolower($exam->title)) . '|' . trim($exam->google_form_url ?? '') . '|' . ($exam->created_by ?? 'school');
        })->map(function ($group) use ($deptClassIds) {
            $sortedSessions = $group->sortBy('start_at')->values();
            $primary = $sortedSessions->first();
            $allClasses = $sortedSessions->flatMap->classes->unique('id')->sortBy('name')->values();
            if ($deptClassIds !== null) {
                $allClasses = $allClasses->whereIn('id', $deptClassIds)->values();
            }
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

        $classes = \App\Models\StudentClass::when($deptClassIds !== null, fn ($q) => $q->whereIn('id', $deptClassIds))
            ->orderBy('name')
            ->get();

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
        $deptClassIds = $this->departmentClassIds();
        $classes = \App\Models\StudentClass::when($deptClassIds !== null, fn ($q) => $q->whereIn('id', $deptClassIds))
            ->orderBy('name')
            ->get();
        return view('admin.exams.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $deptClassIds = $this->departmentClassIds();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'nullable|in:uas,uts,pkl',
            'description' => 'nullable|string|max:2000',
            'google_form_url' => 'required|url:http,https|max:2048',
            'duration' => 'required|integer|min:1',
            'max_violation' => 'required|integer|min:1',
            'access_code' => 'nullable|string|max:20',
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

        if ($deptClassIds !== null) {
            foreach ($validated['schedules'] as $slot) {
                $slotIds = array_map('intval', $slot['classes']);
                if (!empty(array_diff($slotIds, $deptClassIds))) {
                    return back()->withInput()->with('error', 'Akses ditolak. PIC jurusan hanya dapat membuat jadwal untuk kelas jurusannya sendiri.');
                }
            }
        }

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

    public function show(Exam $exam, Request $request)
    {
        $deptClassIds = $this->departmentClassIds();

        if (Auth::user()->isTeacher()) {
            $isOwn = $exam->created_by === Auth::id();
            $isSchoolExam = $exam->created_by === null || ($exam->creator && $exam->creator->isAdmin());
            if (!$isOwn && !$isSchoolExam) {
                return redirect()->route('admin.exams.index')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk memantau ujian milik guru lain.');
            }
        }

        $exam->load('classes');

        if ($deptClassIds !== null) {
            if ($exam->classes->whereIn('id', $deptClassIds)->isEmpty()) {
                return redirect()->route('admin.exams.index')->with('error', 'Akses ditolak. Jadwal ujian ini bukan untuk jurusan ' . Auth::user()->departmentCode() . '.');
            }
            $exam->setRelation('classes', $exam->classes->whereIn('id', $deptClassIds)->values());
        }

        // Jika waktu ujian sudah berakhir, otomatis ubah siswa yang masih 'working' atau 'locked' menjadi 'finished'
        if ($exam->end_at && now()->gt($exam->end_at)) {
            \App\Models\ExamParticipant::where('exam_id', $exam->id)
                ->whereIn('status', ['working', 'locked'])
                ->update(['status' => 'finished']);

            \App\Models\ExamSession::where('exam_id', $exam->id)
                ->whereIn('status', ['ACTIVE', 'LOCKED'])
                ->update(['status' => 'COMPLETED']);
        }

        // Dukungan filter ruang kelas spesifik (misal saat dipantau dari daftar per ruang kelas UAS)
        $selectedClass = null;
        if ($request->filled('class_id') && is_numeric($request->class_id)) {
            if ($deptClassIds !== null && !in_array((int) $request->class_id, $deptClassIds, true)) {
                return redirect()->route('admin.exams.index')->with('error', 'Akses ditolak. Ruang kelas tersebut bukan bagian dari jurusan ' . Auth::user()->departmentCode() . '.');
            }
            $selectedClass = \App\Models\StudentClass::find($request->class_id);
        }

        $participantsQuery = $exam->participants()->with(['user.class']);
        if ($deptClassIds !== null) {
            $participantsQuery->whereHas('user', fn ($q) => $q->whereIn('class_id', $deptClassIds));
        }
        if ($selectedClass) {
            $participantsQuery->whereHas('user', function ($q) use ($selectedClass) {
                $q->where('class_id', $selectedClass->id);
            });
        }

        $participantsCollection = $participantsQuery->get();
        $participantUserIds = $participantsCollection->pluck('user_id')->unique()->values();

        $latestSessionsByUser = collect();
        $violationsByUser = collect();

        if ($participantUserIds->isNotEmpty()) {
            $latestSessionsByUser = \App\Models\ExamSession::where('exam_id', $exam->id)
                ->whereIn('user_id', $participantUserIds)
                ->orderByDesc('id')
                ->get()
                ->unique('user_id')
                ->keyBy('user_id');

            $violationsByUser = \App\Models\Violation::select(['user_id', 'created_at'])
                ->where('exam_id', $exam->id)
                ->whereIn('user_id', $participantUserIds)
                ->get()
                ->groupBy('user_id');
        }

        $participants = $participantsCollection
            ->map(function ($participant) use ($latestSessionsByUser, $violationsByUser) {
                $latestSession = $latestSessionsByUser->get($participant->user_id);
                $userViolations = $violationsByUser->get($participant->user_id, collect());
                $totalViolations = $userViolations->count();

                if ($latestSession && $latestSession->unlocked_at) {
                    $unlockedAt = $latestSession->unlocked_at;
                    $activeViolationCount = $userViolations->filter(
                        fn ($v) => $v->created_at && $v->created_at->gte($unlockedAt)
                    )->count();
                } else {
                    $activeViolationCount = $totalViolations;
                }

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

        $violationsQuery = \App\Models\Violation::where('exam_id', $exam->id)
            ->with(['user.class', 'session']);
        if ($deptClassIds !== null) {
            $violationsQuery->whereHas('user', fn ($q) => $q->whereIn('class_id', $deptClassIds));
        }
        if ($selectedClass) {
            $violationsQuery->whereHas('user', function ($q) use ($selectedClass) {
                $q->where('class_id', $selectedClass->id);
            });
        }
        $violations = $violationsQuery->latest('id')->get();

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

        return view('admin.exams.show', compact('exam', 'participants', 'violations', 'stats', 'selectedClass'));
    }

    private function unlockBaseQuery(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $isTeacher = Auth::user()->isTeacher();
        $deptClassIds = $this->departmentClassIds();

        return \App\Models\ExamSession::with(['user.class', 'exam'])
            ->withCount('violations')
            ->when($isTeacher, fn ($q) => $q->whereHas('exam', fn ($e) => $e->where('created_by', Auth::id())))
            ->when($deptClassIds !== null, fn ($q) => $q->whereHas('user', fn ($u) => $u->whereIn('class_id', $deptClassIds)))
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            });
    }

    public function unlockCenter(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $baseQuery = $this->unlockBaseQuery($request);

        $lockedSessions = (clone $baseQuery)
            ->where('status', 'LOCKED')
            ->orderByDesc('updated_at')
            ->get();

        $unlockArchive = (clone $baseQuery)
            ->whereNotNull('unlocked_at')
            ->orderByDesc('unlocked_at')
            ->limit(100)
            ->get();

        return view('admin.unlock.index', compact(
            'lockedSessions',
            'unlockArchive',
            'search'
        ));
    }

    public function exportUnlockExcel(Request $request)
    {
        $sessions = $this->unlockBaseQuery($request)
            ->where(function ($q) {
                $q->where('status', 'LOCKED')
                  ->orWhereNotNull('unlocked_at')
                  ->orHas('violations');
            })
            ->orderByDesc('updated_at')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $usedTitles = ['keseluruhan' => true];

        $safeText = static function ($value): string {
            $str = (string) ($value ?? '-');
            if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                return "'" . $str;
            }
            return $str;
        };

        $populateSheet = static function (\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, $items) use ($safeText): void {
            $headers = [
                'No',
                'NIS / Username',
                'Nama Siswa',
                'Kelas',
                'Ujian',
                'Status Sesi',
                'Jumlah Pelanggaran',
                'Waktu Terkunci / Update',
                'Waktu Buka Kunci',
                'Mode Akses',
            ];

            $sheet->fromArray($headers, null, 'A1');
            $sheet->getStyle('A1:J1')->getFont()->setBold(true);
            $sheet->getStyle('A1:J1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE8EEF9');

            $rowNum = 2;
            foreach ($items as $idx => $s) {
                $statusLabel = match ($s->status) {
                    'LOCKED' => 'Terkunci',
                    'COMPLETED', 'FINISHED' => 'Selesai',
                    default => $s->unlocked_at ? 'Aktif (Pernah Dibuka)' : 'Aktif',
                };
                $deviceId = (string) ($s->device_id ?? '');
                $accessMode = str_starts_with($deviceId, 'WEB_PORTAL') ? 'Web Portal' : 'SEB / Lainnya';

                $sheet->setCellValue('A' . $rowNum, $idx + 1);
                $sheet->setCellValueExplicit('B' . $rowNum, $safeText($s->user?->username ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C' . $rowNum, $safeText($s->user?->name ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('D' . $rowNum, $safeText($s->user?->class?->name ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('E' . $rowNum, $safeText($s->exam?->title ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('F' . $rowNum, $statusLabel, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('G' . $rowNum, (int) ($s->violations_count ?? 0));
                $sheet->setCellValueExplicit('H' . $rowNum, $s->updated_at ? $s->updated_at->format('Y-m-d H:i') : '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('I' . $rowNum, $s->unlocked_at ? $s->unlocked_at->format('Y-m-d H:i') : '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('J' . $rowNum, $accessMode, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $rowNum++;
            }

            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        };

        // Sheet 1: Keseluruhan
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Keseluruhan');
        $populateSheet($summarySheet, $sessions->values());

        // Sheet 2..N: Dipisah per kelas (urut natural: X -> XI -> XII)
        $groupedByClass = $sessions->groupBy(fn ($s) => trim((string) ($s->user?->class?->name ?? '')) ?: 'Tanpa Kelas')->all();
        uksort($groupedByClass, 'strnatcasecmp');

        foreach ($groupedByClass as $className => $classSessions) {
            $cleanTitle = trim((string) preg_replace('/[:\\\\\\/\\?\\*\\[\\]]/', '-', $className));
            $baseTitle = mb_substr($cleanTitle !== '' ? $cleanTitle : 'Kelas', 0, 31);
            $sheetTitle = $baseTitle;
            $suffix = 2;
            while (isset($usedTitles[mb_strtolower($sheetTitle)])) {
                $tag = " ({$suffix})";
                $sheetTitle = mb_substr($baseTitle, 0, 31 - mb_strlen($tag)) . $tag;
                $suffix++;
            }
            $usedTitles[mb_strtolower($sheetTitle)] = true;

            $classSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $sheetTitle);
            $spreadsheet->addSheet($classSheet);
            $populateSheet($classSheet, $classSessions->values());
        }

        $spreadsheet->setActiveSheetIndex(0);

        $deptSuffix = Auth::user()->isDepartmentPic() ? '_' . strtolower(Auth::user()->departmentCode()) : '';
        $filename = 'rekap_buka_kunci_pelanggaran' . $deptSuffix . '_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function unlockStudent(Exam $exam, \App\Models\User $user)
    {
        // Untuk UAS (dibuat kurikulum/admin), buka kunci HANYA bisa dilakukan oleh Kurikulum/Admin (termasuk PIC jurusan).
        // Guru hanya bisa buka kunci pada ujian PTS yang dibuatnya sendiri.
        if (Auth::user()->isTeacher() && $exam->created_by !== Auth::id()) {
            return back()->with('error', 'Akses ditolak. Buka kunci untuk ujian sekolah (UAS) hanya dapat dilakukan langsung oleh Kurikulum / Administrator.');
        }

        $deptClassIds = $this->departmentClassIds();
        if ($deptClassIds !== null && !in_array((int) $user->class_id, $deptClassIds, true)) {
            return back()->with('error', 'Akses ditolak. Siswa tersebut bukan dari jurusan ' . Auth::user()->departmentCode() . '.');
        }

        // Update kondisional agar aman jika ada 2 proktor/tab membuka kunci bersamaan
        $participantUnlocked = \App\Models\ExamParticipant::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('status', 'locked')
            ->update(['status' => 'working']);

        $sessionUnlocked = \App\Models\ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('status', 'LOCKED')
            ->update([
                'status' => 'ACTIVE',
                'unlocked_at' => \Carbon\Carbon::now(),
            ]);

        if ($participantUnlocked === 0 && $sessionUnlocked === 0) {
            return back()->with('success', "Kunci ujian siswa {$user->name} sudah dalam kondisi terbuka sebelumnya.");
        }

        // Catatan riwayat pelanggaran TIDAK dihapus agar tetap tersimpan untuk export / rekap

        return back()->with('success', "Kunci ujian untuk siswa {$user->name} berhasil dibuka. Riwayat pelanggaran tetap tersimpan.");
    }

    public function resetStudentSession(Exam $exam, \App\Models\User $user)
    {
        // Untuk UAS, reset sesi HANYA bisa dilakukan oleh Kurikulum/Admin (termasuk PIC jurusan).
        if (Auth::user()->isTeacher() && $exam->created_by !== Auth::id()) {
            return back()->with('error', 'Akses ditolak. Reset sesi ujian sekolah (UAS) hanya dapat dilakukan langsung oleh Kurikulum / Administrator.');
        }

        $deptClassIds = $this->departmentClassIds();
        if ($deptClassIds !== null && !in_array((int) $user->class_id, $deptClassIds, true)) {
            return back()->with('error', 'Akses ditolak. Siswa tersebut bukan dari jurusan ' . Auth::user()->departmentCode() . '.');
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
        if (Auth::user()->isTeacher() && $exam->created_by !== Auth::id()) {
            return redirect()->route('admin.exams.index')->with('error', 'Anda hanya dapat mengubah jadwal ujian yang Anda buat sendiri.');
        }

        $deptClassIds = $this->departmentClassIds();
        if (!$this->canPicManageFullExam($exam, $deptClassIds)) {
            return redirect()->route('admin.exams.index')->with('error', 'Jadwal ujian lintas jurusan hanya dapat diubah oleh Admin Kurikulum.');
        }

        $classes = \App\Models\StudentClass::when($deptClassIds !== null, fn ($q) => $q->whereIn('id', $deptClassIds))
            ->orderBy('name')
            ->get();
        $selectedClasses = $exam->classes->pluck('id')->toArray();
        return view('admin.exams.edit', compact('exam', 'classes', 'selectedClasses'));
    }

    public function update(Request $request, Exam $exam)
    {
        if (Auth::user()->isTeacher() && $exam->created_by !== Auth::id()) {
            return redirect()->route('admin.exams.index')->with('error', 'Anda hanya dapat mengubah jadwal ujian yang Anda buat sendiri.');
        }

        $deptClassIds = $this->departmentClassIds();
        if (!$this->canPicManageFullExam($exam, $deptClassIds)) {
            return redirect()->route('admin.exams.index')->with('error', 'Jadwal ujian lintas jurusan hanya dapat diubah oleh Admin Kurikulum.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'nullable|in:uas,uts,pkl',
            'description' => 'nullable|string|max:2000',
            'google_form_url' => 'required|url:http,https|max:2048',
            'start_at' => 'required|date',
            'duration' => 'required|integer|min:1',
            'max_violation' => 'required|integer|min:1',
            'access_code' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'classes' => 'required|array|min:1',
            'classes.*' => 'exists:classes,id',
        ], [
            'classes.required' => 'Pilih minimal 1 kelas target.',
            'classes.min' => 'Pilih minimal 1 kelas target.',
        ]);

        if ($deptClassIds !== null && !empty(array_diff(array_map('intval', $request->classes), $deptClassIds))) {
            return back()->withInput()->with('error', 'Akses ditolak. PIC jurusan hanya dapat memilih kelas jurusannya sendiri.');
        }

        if (Auth::user()->isTeacher()) {
            $validated['type'] = 'uts';
        } elseif (empty($validated['type'])) {
            $validated['type'] = $exam->type ?? 'uas';
        }

        $validated['end_at'] = \Carbon\Carbon::parse($validated['start_at'])->addMinutes((int)$validated['duration']);
        $rawCode = strtoupper(trim((string) ($request->input('access_code') ?? '')));
        if ($request->has('access_code')) {
            $validated['access_code'] = $rawCode !== '' ? $rawCode : null;
        }

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
        if (Auth::user()->isTeacher()) {
            $isOwn = $exam->created_by === Auth::id();
            $isSchoolExam = $exam->created_by === null || ($exam->creator && $exam->creator->isAdmin());
            if (!$isOwn && !$isSchoolExam) {
                return redirect()->route('admin.exams.index')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengunduh rekap ujian milik guru lain.');
            }
        }

        $deptClassIds = $this->departmentClassIds();
        if ($deptClassIds !== null && $exam->classes()->whereIn('classes.id', $deptClassIds)->doesntExist()) {
            return redirect()->route('admin.exams.index')->with('error', 'Akses ditolak. Jadwal ujian ini bukan untuk jurusan ' . Auth::user()->departmentCode() . '.');
        }

        $violations = \App\Models\Violation::where('exam_id', $exam->id)
            ->when($deptClassIds !== null, fn ($q) => $q->whereHas('user', fn ($u) => $u->whereIn('class_id', $deptClassIds)))
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
                    'WEB_TAB_SWITCH' => 'Pindah Tab / Jendela Browser (Web Darurat)',
                    'WEB_FULLSCREEN_EXIT' => 'Keluar Layar Penuh (Web Darurat)',
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

    private function authorizeExamInlineAction(Exam $exam, string $teacherMsg): ?\Illuminate\Http\RedirectResponse
    {
        if (Auth::user()->isTeacher() && $exam->created_by !== Auth::id()) {
            return back()->with('error', $teacherMsg);
        }

        if (!$this->canPicManageFullExam($exam, $this->departmentClassIds())) {
            return back()->with('error', 'Jadwal ujian lintas jurusan hanya dapat diubah oleh Admin Kurikulum.');
        }

        return null;
    }

    public function quickUpdateGoogleForm(Request $request, Exam $exam)
    {
        if ($deny = $this->authorizeExamInlineAction($exam, 'Anda hanya dapat mengubah link form ujian yang Anda buat sendiri.')) {
            return $deny;
        }

        $request->validate([
            'google_form_url' => 'required|url:http,https|max:2048',
        ], [
            'google_form_url.required' => 'URL Google Form wajib diisi.',
            'google_form_url.url' => 'Format URL Google Form tidak valid (harus diawali http:// atau https://).',
        ]);

        $exam->update([
            'google_form_url' => $request->google_form_url,
        ]);

        return back()->with('success', 'Link Google Form ujian berhasil diperbarui.');
    }

    public function rotateAccessCode(Exam $exam)
    {
        if ($deny = $this->authorizeExamInlineAction($exam, 'Anda hanya dapat mengubah kode ujian yang Anda buat sendiri.')) {
            return $deny;
        }

        $code = Exam::generateAccessCode();
        $exam->update(['access_code' => $code]);

        return back()->with('success', "Kode masuk ujian diperbarui menjadi: {$code}");
    }

    public function destroy(Exam $exam)
    {
        if (Auth::user()->isTeacher() && $exam->created_by !== Auth::id()) {
            return redirect()->route('admin.exams.index')->with('error', 'Anda hanya dapat menghapus jadwal ujian yang Anda buat sendiri.');
        }

        if (!$this->canPicManageFullExam($exam, $this->departmentClassIds())) {
            return redirect()->route('admin.exams.index')->with('error', 'Jadwal ujian lintas jurusan hanya dapat dihapus oleh Admin Kurikulum.');
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

        $deptClassIds = $this->departmentClassIds();
        $exams = $query->get();
        $deletableIds = [];
        $ongoingCount = 0;

        foreach ($exams as $exam) {
            if (!$this->canPicManageFullExam($exam, $deptClassIds)) {
                continue;
            }
            if ($exam->isOngoing()) {
                $ongoingCount++;
            } else {
                $deletableIds[] = $exam->id;
            }
        }

        if (empty($deletableIds)) {
            return redirect()->route('admin.exams.index')->with('error', 'Tidak ada jadwal ujian yang dapat dihapus (sedang berlangsung atau milik lintas jurusan).');
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
        if ($request->boolean('confirm_import')) {
            $readyRows = \App\Helpers\ImportPreview::pullPayload((string) $request->input('import_token', ''));
            if ($readyRows === null) {
                return redirect()->route('admin.exams.index')->with('error', 'Sesi pratinjau import sudah kedaluwarsa. Silakan unggah ulang file.');
            }
            if (empty($readyRows)) {
                return redirect()->route('admin.exams.index')->with('error', 'Tidak ada jadwal ujian valid untuk disimpan.');
            }

            @set_time_limit(300);
            $count = 0;

            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($readyRows, &$count) {
                    $now = now();
                    $creatorId = Auth::id();
                    $studentsByClass = \App\Models\User::where('role', 'siswa')
                        ->where('status', 'active')
                        ->select('id', 'class_id')
                        ->get()
                        ->groupBy('class_id');
                    $allStudentIds = $studentsByClass->flatten(1)->pluck('id')->all();

                    $participantBuffer = [];
                    $flushParticipants = function () use (&$participantBuffer) {
                        if (empty($participantBuffer)) {
                            return;
                        }
                        foreach (array_chunk($participantBuffer, 500) as $chunk) {
                            \App\Models\ExamParticipant::insert($chunk);
                        }
                        $participantBuffer = [];
                    };

                    foreach ($readyRows as $item) {
                        $startAt = \Carbon\Carbon::parse($item['start_at']);
                        $exam = \App\Models\Exam::create([
                            'title' => $item['title'],
                            'type' => $item['type'],
                            'description' => $item['description'],
                            'google_form_url' => $item['google_form_url'],
                            'start_at' => $startAt,
                            'end_at' => $startAt->copy()->addMinutes($item['duration']),
                            'duration' => $item['duration'],
                            'max_violation' => $item['max_violation'],
                            'status' => $item['status'],
                            'created_by' => $creatorId,
                        ]);

                        $studentIds = [];
                        if (!empty($item['class_ids'])) {
                            $exam->classes()->sync($item['class_ids']);
                            foreach ($item['class_ids'] as $cid) {
                                foreach ($studentsByClass->get($cid, []) as $stu) {
                                    $studentIds[] = $stu->id;
                                }
                            }
                        } else {
                            $studentIds = $allStudentIds;
                        }

                        foreach (array_unique($studentIds) as $sid) {
                            $participantBuffer[] = [
                                'exam_id' => $exam->id,
                                'user_id' => $sid,
                                'status' => 'registered',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }

                        if (count($participantBuffer) >= 500) {
                            $flushParticipants();
                        }

                        $count++;
                    }

                    $flushParticipants();
                });
            } catch (\Throwable $e) {
                return redirect()->route('admin.exams.index')->with('error', 'Terjadi kesalahan saat mengimpor jadwal ujian: ' . $e->getMessage());
            }

            return redirect()->route('admin.exams.index')->with('success', "Berhasil mengimpor {$count} jadwal ujian baru.");
        }

        $request->validate(
            ['file' => 'required|file|mimes:csv,txt,xlsx|max:15360'],
            [
                'file.required' => 'Pilih file terlebih dahulu sebelum mengklik upload.',
                'file.mimes' => 'Format file harus berupa CSV (.csv) atau Excel (.xlsx).',
                'file.max' => 'Ukuran file maksimal adalah 15 MB.',
            ]
        );

        @set_time_limit(300);

        try {
            $rows = \App\Helpers\SimpleSpreadsheetReader::read($request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file import: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', 'File kosong atau tidak memiliki baris data yang terbaca.');
        }

        $now = now();
        $isTeacher = Auth::user()->isTeacher();
        $deptClassIds = $this->departmentClassIds();
        $classesByName = \App\Models\StudentClass::all()->keyBy(fn ($c) => strtolower(trim($c->name)));

        $existingKeys = [];
        \App\Models\Exam::select('title', 'google_form_url', 'start_at')->chunk(500, function ($exams) use (&$existingKeys) {
            foreach ($exams as $ex) {
                $key = strtolower(trim($ex->title)) . '|' . trim($ex->google_form_url) . '|' . \Carbon\Carbon::parse($ex->start_at)->format('Y-m-d H:i:s');
                $existingKeys[$key] = true;
            }
        });

        $previewRows = [];
        $readyPayload = [];
        $seenKeys = [];

        foreach ($rows as $idx => $row) {
            $excelRow = $idx + 2;
            $title = trim((string) ($row['judul_ujian'] ?? ''));
            $rawUrl = trim((string) ($row['url_google_form'] ?? ''));
            $rawStartAt = trim((string) ($row['waktu_mulai'] ?? ''));
            $targetClassNames = trim((string) (!empty($row['target_kelas']) ? $row['target_kelas'] : (!empty($row['kelas']) ? $row['kelas'] : '')));
            $duration = max(1, (int) ($row['durasi_menit'] ?? 60));

            $cells = [
                'title' => $title ?: '-',
                'schedule' => ($rawStartAt ?: 'Sekarang') . " ({$duration}m)",
                'classes' => $targetClassNames ?: 'Semua Kelas',
            ];

            $parsedScheme = strtolower((string) parse_url($rawUrl, PHP_URL_SCHEME));
            $isValidUrl = filter_var($rawUrl, FILTER_VALIDATE_URL) !== false && in_array($parsedScheme, ['http', 'https'], true);

            if ($title === '' || mb_strlen($title) > 255 || !$isValidUrl) {
                $note = $title === ''
                    ? 'Judul ujian wajib diisi. (akan dilewati)'
                    : (mb_strlen($title) > 255
                        ? 'Judul ujian melebihi 255 karakter. (akan dilewati)'
                        : 'URL Google Form tidak valid (wajib http/https). (akan dilewati)');
                $previewRows[] = [
                    'excel_row' => $excelRow,
                    'cells' => $cells,
                    'status' => 'error',
                    'note' => $note,
                ];
                continue;
            }

            if ($rawStartAt !== '') {
                $startAt = \App\Helpers\SimpleSpreadsheetReader::parseDateTime($rawStartAt);
                if (!$startAt) {
                    $previewRows[] = [
                        'excel_row' => $excelRow,
                        'cells' => $cells,
                        'status' => 'error',
                        'note' => "Format waktu mulai '{$rawStartAt}' tidak valid. Gunakan format YYYY-MM-DD HH:MM. (akan dilewati)",
                    ];
                    continue;
                }
            } else {
                $startAt = $now->copy();
            }

            $cells['schedule'] = $startAt->format('d M Y, H:i') . " ({$duration}m)";
            $dedupKey = strtolower($title) . '|' . $rawUrl . '|' . $startAt->format('Y-m-d H:i:s');

            if (isset($existingKeys[$dedupKey]) || isset($seenKeys[$dedupKey])) {
                $previewRows[] = [
                    'excel_row' => $excelRow,
                    'cells' => $cells,
                    'status' => 'duplicate',
                    'note' => 'Jadwal ujian dengan judul, link, dan waktu mulai yang sama sudah ada. (akan dilewati)',
                ];
                continue;
            }
            $seenKeys[$dedupKey] = true;

            $classIds = [];
            if ($targetClassNames !== '') {
                $missingClasses = [];
                $forbiddenClasses = [];
                foreach (array_filter(array_map('trim', explode(',', $targetClassNames))) as $cName) {
                    $cls = $classesByName->get(strtolower($cName));
                    if (!$cls) {
                        $missingClasses[] = $cName;
                    } elseif ($deptClassIds !== null && !in_array((int) $cls->id, $deptClassIds, true)) {
                        $forbiddenClasses[] = $cName;
                    } else {
                        $classIds[] = $cls->id;
                    }
                }
                if (!empty($missingClasses) || !empty($forbiddenClasses)) {
                    $note = !empty($missingClasses)
                        ? "Kelas '" . implode(', ', $missingClasses) . "' tidak ditemukan dalam master kelas. (akan dilewati)"
                        : "Kelas '" . implode(', ', $forbiddenClasses) . "' bukan bagian dari jurusan " . Auth::user()->departmentCode() . ". (akan dilewati)";
                    $previewRows[] = [
                        'excel_row' => $excelRow,
                        'cells' => $cells,
                        'status' => 'error',
                        'note' => $note,
                    ];
                    continue;
                }
                $classIds = array_values(array_unique($classIds));
            } elseif ($deptClassIds !== null) {
                $classIds = $deptClassIds;
                $cells['classes'] = 'Semua Kelas ' . Auth::user()->departmentCode();
                if (empty($classIds)) {
                    $previewRows[] = [
                        'excel_row' => $excelRow,
                        'cells' => $cells,
                        'status' => 'error',
                        'note' => 'Belum ada master kelas untuk jurusan ' . Auth::user()->departmentCode() . '. (akan dilewati)',
                    ];
                    continue;
                }
            }

            $examType = $isTeacher ? 'uts' : (
                !empty($row['tipe']) && in_array(strtolower(trim((string) $row['tipe'])), ['uas', 'uts', 'pkl'], true)
                    ? strtolower(trim((string) $row['tipe']))
                    : (preg_match('/\b(uts|pts|tengah)/i', $title) ? 'uts' : 'uas')
            );

            $rawStatus = strtolower(trim((string) ($row['status'] ?? 'active')));
            $status = in_array($rawStatus, ['inactive', 'nonaktif', 'draft', '0', 'disabled'], true) ? 'inactive' : 'active';

            $readyPayload[] = [
                'title' => $title,
                'type' => $examType,
                'description' => $row['deskripsi'] ?? null,
                'google_form_url' => $rawUrl,
                'start_at' => $startAt->format('Y-m-d H:i:s'),
                'duration' => $duration,
                'max_violation' => max(1, (int) ($row['maksimal_pelanggaran'] ?? 3)),
                'status' => $status,
                'class_ids' => $classIds,
            ];

            $previewRows[] = [
                'excel_row' => $excelRow,
                'cells' => $cells,
                'status' => 'ready',
                'note' => 'Siap dijadwalkan (' . strtoupper($examType) . ').',
            ];
        }

        return \App\Helpers\ImportPreview::storeAndRender([
            'entityLabel' => 'Jadwal Ujian',
            'unitLabel' => 'jadwal ujian',
            'backRoute' => route('admin.exams.index'),
            'actionRoute' => route('admin.exams.import'),
            'columns' => ['title' => 'Judul Ujian', 'schedule' => 'Waktu & Durasi', 'classes' => 'Target Kelas'],
            'fileName' => $request->file('file')->getClientOriginalName(),
        ], $previewRows, $readyPayload);
    }
}
