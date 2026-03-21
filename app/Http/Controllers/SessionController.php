<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Models\User;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    private function successResponse(string $message, $data = null, int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(string $message, $errors = null, int $status = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    // POST /api/login
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required_without:login|email',
                'login' => 'required_without:email|string',
                'password' => 'required|string',
                'remember' => 'sometimes|boolean',
            ]);

            $remember = (bool) ($validated['remember'] ?? false);

            $loginValue = $validated['email'] ?? $validated['login'];

            $user = User::query()
                ->where('email', $loginValue)
                ->orWhere('student_id', $loginValue)
                ->orWhere('phone', $loginValue)
                ->orWhere('roll_no', $loginValue)
                ->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse('Invalid credentials', null, 401);
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            return $this->successResponse('Login successful', Auth::user());
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Login failed', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/register
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'student_id' => 'nullable|string|max:100|unique:users,student_id',
                'phone' => 'nullable|string|max:50|unique:users,phone',
                'roll_no' => 'nullable|string|max:50|unique:users,roll_no',
                'role' => 'nullable|string|max:50',
                'course_id' => 'nullable|exists:courses,id',
                'semester_id' => 'nullable|exists:semesters,id',
                'is_active' => 'sometimes|boolean',
                'remember' => 'sometimes|boolean',
            ]);

            $validated['password'] = Hash::make($validated['password']);

            $remember = (bool) ($validated['remember'] ?? false);
            unset($validated['remember']);

            $user = User::create($validated);

            Auth::login($user, $remember);
            $request->session()->regenerate();

            return $this->successResponse('Register successful', $user, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Register failed', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        try {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->successResponse('Logout successful');
        } catch (Throwable $e) {
            return $this->errorResponse('Logout failed', ['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/me
    public function me(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Unauthenticated', null, 401);
            }

            return $this->successResponse('Authenticated user fetched successfully', $user->load(['course', 'semester']));
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch authenticated user', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/forgot-password
    public function forgotPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $status = Password::sendResetLink([
                'email' => $validated['email'],
            ]);

            if ($status !== Password::RESET_LINK_SENT) {
                return $this->errorResponse('Failed to send reset link', ['error' => __($status)], 400);
            }

            return $this->successResponse('Password reset link sent');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to send reset link', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/reset-password
    public function resetPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $status = Password::reset(
                [
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'password_confirmation' => $request->input('password_confirmation'),
                    'token' => $validated['token'],
                ],
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();
                }
            );

            if ($status !== Password::PASSWORD_RESET) {
                return $this->errorResponse('Password reset failed', ['error' => __($status)], 400);
            }

            return $this->successResponse('Password reset successful');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Password reset failed', ['error' => $e->getMessage()], 500);
        }
    }
}
