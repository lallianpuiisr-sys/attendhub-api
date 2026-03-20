<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// Password reset link target (required by Password::sendResetLink)
Route::get('/reset-password/{token}', function (Request $request, string $token) {
    return response()->json([
        'token' => $token,
        'email' => $request->query('email'),
    ]);
})->name('password.reset');
