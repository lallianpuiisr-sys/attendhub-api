<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\UserController;

Route::middleware('web')->group(function () {
    Route::post('login', [SessionController::class, 'login']);
    Route::post('register', [SessionController::class, 'register']);
    Route::post('logout', [SessionController::class, 'logout'])->middleware('auth');
});

Route::apiResource('courses', CourseController::class);
Route::apiResource('semesters', SemesterController::class);
Route::apiResource('users', UserController::class);
