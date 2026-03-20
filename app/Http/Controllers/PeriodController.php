<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class PeriodController extends Controller
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

    // GET /api/periods
    public function index()
    {
        try {
            $periods = Period::with(['course', 'semester'])->latest()->get();

            return $this->successResponse('Periods fetched successfully', $periods);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch periods', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/periods
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'course_id' => 'nullable|exists:courses,id',
                'semester_id' => 'nullable|exists:semesters,id',
                'is_active' => 'boolean',
            ]);

            $period = Period::create($validated);

            return $this->successResponse('Period created successfully', $period, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to create period', ['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/periods/{id}
    public function show($id)
    {
        try {
            $period = Period::with(['course', 'semester'])->findOrFail($id);

            return $this->successResponse('Period fetched successfully', $period);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Period not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch period', ['error' => $e->getMessage()], 500);
        }
    }

    // PUT /api/periods/{id}
    public function update(Request $request, $id)
    {
        try {
            $period = Period::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'start_time' => 'sometimes|required|date_format:H:i',
                'end_time' => 'sometimes|required|date_format:H:i',
                'course_id' => 'nullable|exists:courses,id',
                'semester_id' => 'nullable|exists:semesters,id',
                'is_active' => 'boolean',
            ]);

            $period->update($validated);

            return $this->successResponse('Period updated successfully', $period);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Period not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to update period', ['error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/periods/{id}
    public function destroy($id)
    {
        try {
            $period = Period::findOrFail($id);
            $period->delete();

            return $this->successResponse('Period deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Period not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to delete period', ['error' => $e->getMessage()], 500);
        }
    }
}
