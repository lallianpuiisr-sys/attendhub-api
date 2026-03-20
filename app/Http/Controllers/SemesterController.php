<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class SemesterController extends Controller
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

    // GET /api/semesters
    public function index()
    {
        try {
            $semesters = Semester::with('course')->latest()->get();

            return $this->successResponse('Semesters fetched successfully', $semesters);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch semesters', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/semesters
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'semester_number' => 'required|integer|min:1',
                'is_active' => 'boolean',
            ]);

            $semester = Semester::create($validated);

            return $this->successResponse('Semester created successfully', $semester, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to create semester', ['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/semesters/{id}
    public function show($id)
    {
        try {
            $semester = Semester::with('course')->findOrFail($id);

            return $this->successResponse('Semester fetched successfully', $semester);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Semester not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch semester', ['error' => $e->getMessage()], 500);
        }
    }

    // PUT /api/semesters/{id}
    public function update(Request $request, $id)
    {
        try {
            $semester = Semester::findOrFail($id);

            $validated = $request->validate([
                'course_id' => 'sometimes|exists:courses,id',
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'semester_number' => 'sometimes|required|integer|min:1',
                'is_active' => 'boolean',
            ]);

            $semester->update($validated);

            return $this->successResponse('Semester updated successfully', $semester);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Semester not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to update semester', ['error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/semesters/{id}
    public function destroy($id)
    {
        try {
            $semester = Semester::findOrFail($id);
            $semester->delete();

            return $this->successResponse('Semester deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Semester not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to delete semester', ['error' => $e->getMessage()], 500);
        }
    }
}
