<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class AttendanceSessionController extends Controller
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

    // GET /api/attendance-sessions
    public function index()
    {
        try {
            $sessions = AttendanceSession::with([
                'course',
                'semester',
                'subject',
                'period',
                'creator',
            ])->latest()->get();

            return $this->successResponse('Attendance sessions fetched successfully', $sessions);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch attendance sessions', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/attendance-sessions
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'semester_id' => 'required|exists:semesters,id',
                'subject_id' => 'required|exists:subjects,id',
                'period_id' => 'required|exists:periods,id',
                'starts_at' => 'required|date',
                'ends_at' => 'required|date|after:starts_at',
                'created_by' => 'required|exists:users,id',
                'status' => 'in:active,expired',
                'qr_token' => 'required|string|max:255',
                'is_active' => 'boolean',
            ]);

            $session = AttendanceSession::create($validated);

            return $this->successResponse('Attendance session created successfully', $session, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to create attendance session', ['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/attendance-sessions/{id}
    public function show($id)
    {
        try {
            $session = AttendanceSession::with([
                'course',
                'semester',
                'subject',
                'period',
                'creator',
            ])->findOrFail($id);

            return $this->successResponse('Attendance session fetched successfully', $session);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Attendance session not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch attendance session', ['error' => $e->getMessage()], 500);
        }
    }

    // PUT /api/attendance-sessions/{id}
    public function update(Request $request, $id)
    {
        try {
            $session = AttendanceSession::findOrFail($id);

            $validated = $request->validate([
                'course_id' => 'sometimes|required|exists:courses,id',
                'semester_id' => 'sometimes|required|exists:semesters,id',
                'subject_id' => 'sometimes|required|exists:subjects,id',
                'period_id' => 'sometimes|required|exists:periods,id',
                'starts_at' => 'sometimes|required|date',
                'ends_at' => 'sometimes|required|date|after:starts_at',
                'created_by' => 'sometimes|required|exists:users,id',
                'status' => 'sometimes|required|in:active,expired',
                'qr_token' => 'sometimes|required|string|max:255',
                'is_active' => 'boolean',
            ]);

            $session->update($validated);

            return $this->successResponse('Attendance session updated successfully', $session);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Attendance session not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to update attendance session', ['error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/attendance-sessions/{id}
    public function destroy($id)
    {
        try {
            $session = AttendanceSession::findOrFail($id);
            $session->delete();

            return $this->successResponse('Attendance session deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Attendance session not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to delete attendance session', ['error' => $e->getMessage()], 500);
        }
    }
}
