<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\UserController;

Route::apiResource('courses', CourseController::class);
Route::apiResource('semesters', SemesterController::class);
Route::apiResource('users', UserController::class);
