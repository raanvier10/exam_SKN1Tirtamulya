<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\AdminAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

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
        
        Route::get('/exams/template', function () {
            $csv = "judul_ujian,deskripsi,url_google_form,waktu_mulai,durasi_menit,tingkat_keamanan,maksimal_pelanggaran,status\nUjian Matematika,Tutup buku,https://forms.gle/...,2026-08-18 09:00,90,STRICT,3,active\n";
            return response($csv)->header('Content-Type', 'text/csv')->header('Content-Disposition', 'attachment; filename="template_ujian.csv"');
        })->name('exams.template');
        Route::post('/exams/import', [ExamController::class, 'import'])->name('exams.import');
        Route::post('/exams/{exam}/students/{user}/unlock', [ExamController::class, 'unlockStudent'])->name('exams.students.unlock');
        Route::post('/exams/{exam}/students/{user}/reset', [ExamController::class, 'resetStudentSession'])->name('exams.students.reset');
        Route::resource('exams', ExamController::class);
    });
});
