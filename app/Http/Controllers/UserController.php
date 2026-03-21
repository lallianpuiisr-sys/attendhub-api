<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserController extends Controller
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

    // GET /api/users
    public function index()
    {
        try {
            $users = User::with(['course', 'semester'])->latest()->get();

            return $this->successResponse('Users fetched successfully', $users);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch users', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/users
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'nullable|string|min:6',
                'role' => 'required|string',
                'avatar_url' => 'nullable|string',
                'student_id' => 'nullable|string|max:100|unique:users,student_id',
                'course_id' => 'nullable|exists:courses,id',
                'semester_id' => 'nullable|exists:semesters,id',
                'is_active' => 'boolean',
            ]);

            if (empty($validated['password'])) {
                return $this->errorResponse('Validation failed', [
                    'password' => ['The password field is required.']
                ], 422);
            }

            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return $this->successResponse('User created successfully', $user, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to create user', ['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/users/{id}
    public function show($id)
    {
        try {
            $user = User::with(['course', 'semester'])->findOrFail($id);

            return $this->successResponse('User fetched successfully', $user);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('User not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch user', ['error' => $e->getMessage()], 500);
        }
    }

    // PUT /api/users/{id}
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:6',
                'role' => 'required|string',
                'avatar_url' => 'nullable|string',
                'student_id' => 'nullable|string|max:100|unique:users,student_id,' . $id,
                'course_id' => 'nullable|exists:courses,id',
                'semester_id' => 'nullable|exists:semesters,id',
                'is_active' => 'boolean',
            ]);

            if (isset($validated['password']) && $validated['password'] !== null) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);

            return $this->successResponse('User updated successfully', $user);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('User not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to update user', ['error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/users/{id}
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return $this->successResponse('User deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('User not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to delete user', ['error' => $e->getMessage()], 500);
        }
    }
}
