<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class EnrollmentController extends Controller
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

    // GET /api/enrollments
    public function index()
    {
        try {
            $enrollments = Enrollment::with(['user', 'course', 'semester'])->latest()->get();

            return $this->successResponse('Enrollments fetched successfully', $enrollments);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch enrollments', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/enrollments
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'course_id' => 'required|exists:courses,id',
                'semester_id' => 'required|exists:semesters,id',
                'is_active' => 'boolean',
            ]);

            $enrollment = Enrollment::create($validated);

            return $this->successResponse('Enrollment created successfully', $enrollment, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to create enrollment', ['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/enrollments/{id}
    public function show($id)
    {
        try {
            $enrollment = Enrollment::with(['user', 'course', 'semester'])->findOrFail($id);

            return $this->successResponse('Enrollment fetched successfully', $enrollment);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Enrollment not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch enrollment', ['error' => $e->getMessage()], 500);
        }
    }

    // PUT /api/enrollments/{id}
    public function update(Request $request, $id)
    {
        try {
            $enrollment = Enrollment::findOrFail($id);

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'course_id' => 'sometimes|required|exists:courses,id',
                'semester_id' => 'sometimes|required|exists:semesters,id',
                'is_active' => 'boolean',
            ]);

            $enrollment->update($validated);

            return $this->successResponse('Enrollment updated successfully', $enrollment);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Enrollment not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to update enrollment', ['error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/enrollments/{id}
    public function destroy($id)
    {
        try {
            $enrollment = Enrollment::findOrFail($id);
            $enrollment->delete();

            return $this->successResponse('Enrollment deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Enrollment not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to delete enrollment', ['error' => $e->getMessage()], 500);
        }
    }
}
