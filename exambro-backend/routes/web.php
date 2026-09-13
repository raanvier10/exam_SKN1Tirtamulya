<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\AdminAuthController;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role:admin,guru'])->group(function () {
        Route::get('/dashboard', function () {
            $now = \Carbon\Carbon::now();
            $examQuery = \App\Models\Exam::where('status', 'active')
                ->where('start_at', '<=', $now)
                ->where(function ($query) use ($now) {
                    $query->where('end_at', '>=', $now)
                          ->orWhereNull('end_at');
                });

            if (auth()->user()->isTeacher()) {
                $examQuery->where(function ($q) {
                    $q->where('created_by', auth()->id())
                      ->orWhereNull('created_by')
                      ->orWhereHas('creator', function ($sq) {
                          $sq->where('role', 'admin');
                      });
                });
            }

            $ongoingExams = $examQuery->withCount([
                'participants',
                'participants as working_count' => function ($q) {
                    $q->where('status', 'working');
                },
                'participants as locked_count' => function ($q) {
                    $q->where('status', 'locked');
                },
                'participants as finished_count' => function ($q) {
                    $q->where('status', 'finished');
                }
            ])
            ->get();

            return view('admin.dashboard', compact('ongoingExams'));
        })->name('dashboard');

        // Khusus Admin (Kurikulum): Master Kelas & Master Siswa
        Route::middleware('role:admin')->group(function () {
            Route::get('/classes/template', function () {
                $csv = "nama_kelas,deskripsi\nXII IPA 1,Kelas IPA Angkatan 2023\n";
                return response($csv)->header('Content-Type', 'text/csv')->header('Content-Disposition', 'attachment; filename="template_kelas.csv"');
            })->name('classes.template');
            Route::post('/classes/import', [StudentClassController::class, 'import'])->name('classes.import');
            Route::resource('classes', StudentClassController::class);
            
            Route::get('/students/template', function () {
                $csv = "nama_lengkap,nis_username,password,nama_kelas,status,is_pkl\nBudi Santoso,12345678,password123,XII RPL 1,active,0\nSiti Rahma,12345679,password123,XII RPL 2,active,1\n";
                return response($csv)->header('Content-Type', 'text/csv')->header('Content-Disposition', 'attachment; filename="template_siswa.csv"');
            })->name('students.template');
            Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
            Route::post('/students/{student}/toggle-pkl', [StudentController::class, 'togglePkl'])->name('students.toggle-pkl');
            Route::resource('students', StudentController::class);

            Route::resource('teachers', TeacherController::class);
        });
        
        // Akses Bersama: Admin (Kurikulum) & Guru (Pengawas UAS / Pembuat PTS)
        Route::get('/exams/template', function () {
            $csv = "judul_ujian,target_kelas,deskripsi,url_google_form,waktu_mulai,durasi_menit,maksimal_pelanggaran,status\nUjian Matematika,\"11 TJKT 1, 11 TJKT 2\",Tutup buku,https://forms.gle/...,2026-08-18 09:00,90,3,active\n";
            return response($csv)->header('Content-Type', 'text/csv')->header('Content-Disposition', 'attachment; filename="template_ujian.csv"');
        })->name('exams.template');
        Route::post('/exams/import', [ExamController::class, 'import'])->name('exams.import');
        Route::get('/exams/{exam}/export-violations', [ExamController::class, 'exportViolations'])->name('exams.export-violations');
        Route::patch('/exams/{exam}/quick-form-url', [ExamController::class, 'quickUpdateGoogleForm'])->name('exams.quick-form-url');
        Route::post('/exams/{exam}/students/{user}/unlock', [ExamController::class, 'unlockStudent'])->name('exams.students.unlock');
        Route::post('/exams/{exam}/students/{user}/reset', [ExamController::class, 'resetStudentSession'])->name('exams.students.reset');
        Route::resource('exams', ExamController::class);

        Route::get('/change-password', [AdminAuthController::class, 'showChangePasswordForm'])->name('change-password');
        Route::put('/change-password', [AdminAuthController::class, 'updatePassword'])->name('change-password.update');
    });
});
