<?php

use App\Http\Controllers\CloudinaryController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceSessionController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\SemesterQrScanController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::post('login', [SessionController::class, 'login']);
    Route::post('register', [SessionController::class, 'register']);

    Route::middleware('auth')->group(function () {
        Route::post('logout', [SessionController::class, 'logout']);
        Route::get('me', [SessionController::class, 'me']);
        Route::post('me/avatar', [CloudinaryController::class, 'uploadAvatar']);

        Route::apiResource('complaints', ComplaintController::class);
        Route::apiResource('courses', CourseController::class);
        Route::apiResource('attendance-sessions', AttendanceSessionController::class);
        Route::apiResource('attendances', AttendanceController::class);
        Route::post('attendance/scan-static', [SemesterQrScanController::class, 'scan']);
        Route::apiResource('enrollments', EnrollmentController::class);
        Route::get('periods/by-context', [PeriodController::class, 'byContext']);
        Route::post('qr/scan-semester', [SemesterQrScanController::class, 'scan']);
        Route::apiResource('periods', PeriodController::class);
        Route::apiResource('semesters', SemesterController::class);
        Route::get('semesters/{id}/qr', [SemesterController::class, 'qr']);
        Route::apiResource('subjects', SubjectController::class);
        Route::apiResource('users', UserController::class);
    });
});

Route::post('forgot-password', [SessionController::class, 'forgotPassword']);
Route::post('reset-password', [SessionController::class, 'resetPassword']);
