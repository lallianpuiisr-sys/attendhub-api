<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class CourseController extends Controller
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

    // GET /api/courses
    public function index()
    {
        try {
            $courses = Course::latest()->get();

            return $this->successResponse('Courses fetched successfully', $courses);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch courses', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/courses
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'role' => 'nullable|string',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $course = Course::create($validated);

            return $this->successResponse('Course created successfully', $course, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to create course', ['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/courses/{id}
    public function show($id)
    {
        try {
            $course = Course::with('semesters')->findOrFail($id);

            return $this->successResponse('Course fetched successfully', $course);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Course not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch course', ['error' => $e->getMessage()], 500);
        }
    }

    // PUT /api/courses/{id}
    public function update(Request $request, $id)
    {
        try {
            $course = Course::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'role' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $course->update($validated);

            return $this->successResponse('Course updated successfully', $course);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Course not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to update course', ['error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/courses/{id}
    public function destroy($id)
    {
        try {
            $course = Course::findOrFail($id);
            $course->delete();

            return $this->successResponse('Course deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Course not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to delete course', ['error' => $e->getMessage()], 500);
        }
    }
}
