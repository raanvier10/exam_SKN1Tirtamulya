<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApiExamController;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Student Exams
    Route::get('/student/exams/today', [ApiExamController::class, 'todayExams']);
    Route::get('/student/exams/{id}', [ApiExamController::class, 'show']);

    // Exam Sessions
    Route::post('/exams/{id}/start', [ApiExamController::class, 'startSession']);
    Route::post('/exams/{id}/finish', [ApiExamController::class, 'finishSession']);

    // Google Status Sync
    Route::post('/student/google-status', [AuthController::class, 'updateGoogleStatus']);

    // Violations
    Route::post('/violations', [ApiExamController::class, 'reportViolation']);
});
