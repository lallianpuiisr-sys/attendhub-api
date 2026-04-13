<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class SubjectController extends Controller
{
    private const WORKER_ROLES = ['teacher', 'admin', 'receptionist'];

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

    // GET /api/subjects
    public function index()
    {
        try {
            $subjects = Subject::with(['course', 'semester', 'period', 'worker'])->latest()->get();

            return $this->successResponse('Subjects fetched successfully', $subjects);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch subjects', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/subjects
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'course_id' => 'nullable|exists:courses,id',
                'semester_id' => 'nullable|exists:semesters,id',
                'day_of_week' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'period_id' => 'nullable|exists:periods,id',
                'worker_id' => [
                    'nullable',
                    Rule::exists('users', 'id')->whereIn('role', self::WORKER_ROLES),
                ],
                'is_active' => 'boolean',
            ]);

            $subject = Subject::create($validated);

            return $this->successResponse('Subject created successfully', $subject, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to create subject', ['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/subjects/{id}
    public function show($id)
    {
        try {
            $subject = Subject::with(['course', 'semester', 'period', 'worker'])->findOrFail($id);

            return $this->successResponse('Subject fetched successfully', $subject);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Subject not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch subject', ['error' => $e->getMessage()], 500);
        }
    }

    // PUT /api/subjects/{id}
    public function update(Request $request, $id)
    {
        try {
            $subject = Subject::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'code' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'course_id' => 'nullable|exists:courses,id',
                'semester_id' => 'nullable|exists:semesters,id',
                'day_of_week' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'period_id' => 'nullable|exists:periods,id',
                'worker_id' => [
                    'nullable',
                    Rule::exists('users', 'id')->whereIn('role', self::WORKER_ROLES),
                ],
                'is_active' => 'boolean',
            ]);

            $subject->update($validated);

            return $this->successResponse('Subject updated successfully', $subject);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Subject not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to update subject', ['error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/subjects/{id}
    public function destroy($id)
    {
        try {
            $subject = Subject::findOrFail($id);
            $subject->delete();

            return $this->successResponse('Subject deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Subject not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to delete subject', ['error' => $e->getMessage()], 500);
        }
    }
}
