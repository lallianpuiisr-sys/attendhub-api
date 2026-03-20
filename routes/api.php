<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;

Route::middleware('web')->group(function () {
    Route::post('login', [SessionController::class, 'login']);
    Route::post('register', [SessionController::class, 'register']);
    Route::post('logout', [SessionController::class, 'logout'])->middleware('auth');
});

Route::post('forgot-password', [SessionController::class, 'forgotPassword']);
Route::post('reset-password', [SessionController::class, 'resetPassword']);

Route::apiResource('courses', CourseController::class);
Route::apiResource('enrollments', EnrollmentController::class);
Route::apiResource('periods', PeriodController::class);
Route::apiResource('semesters', SemesterController::class);
Route::apiResource('subjects', SubjectController::class);
Route::apiResource('users', UserController::class);
