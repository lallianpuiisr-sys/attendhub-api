<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Enrollment;
use App\Models\Period;
use App\Models\Semester;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AttendanceController extends Controller
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

    // GET /api/attendances
    public function index()
    {
        try {
            $attendances = Attendance::with(['user', 'session'])->latest()->get();

            return $this->successResponse('Attendances fetched successfully', $attendances);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch attendances', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/attendances
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'attendance_session_id' => 'required|exists:attendance_sessions,id',
                'status' => 'required|in:present,absent,late',
                'scanned_at' => 'nullable|date',
                'device_id' => 'nullable|string|max:255',
                'ip_address' => 'nullable|ip',
            ]);

            $attendance = Attendance::create($validated);

            return $this->successResponse('Attendance created successfully', $attendance, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to create attendance', ['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/attendances/{id}
    public function show($id)
    {
        try {
            $attendance = Attendance::with(['user', 'session'])->findOrFail($id);

            return $this->successResponse('Attendance fetched successfully', $attendance);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Attendance not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch attendance', ['error' => $e->getMessage()], 500);
        }
    }

    // PUT /api/attendances/{id}
    public function update(Request $request, $id)
    {
        try {
            $attendance = Attendance::findOrFail($id);

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'attendance_session_id' => 'sometimes|required|exists:attendance_sessions,id',
                'status' => 'sometimes|required|in:present,absent,late',
                'scanned_at' => 'nullable|date',
                'device_id' => 'nullable|string|max:255',
                'ip_address' => 'nullable|ip',
            ]);

            $attendance->update($validated);

            return $this->successResponse('Attendance updated successfully', $attendance);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Attendance not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to update attendance', ['error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/attendances/{id}
    public function destroy($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->delete();

            return $this->successResponse('Attendance deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Attendance not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to delete attendance', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/attendance/scan-static
    public function scanStatic(Request $request)
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
                'device_id' => 'nullable|string|max:255',
            ]);

            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Unauthenticated', null, 401);
            }

            $semester = Semester::where('static_qr_token', $validated['token'])->first();

            if (!$semester) {
                return $this->errorResponse('Invalid QR token', null, 404);
            }

            $courseId = $semester->course_id;
            $semesterId = $semester->id;

            $isEnrolled = Enrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->where('semester_id', $semesterId)
                ->where('is_active', true)
                ->exists();

            if (!$isEnrolled) {
                return $this->errorResponse('Student not enrolled for this course/semester', null, 403);
            }

            $now = Carbon::now();
            $currentTime = $now->format('H:i:s');
            $graceMinutes = 10;

            $periods = Period::where('course_id', $courseId)
                ->where('semester_id', $semesterId)
                ->orderBy('start_time')
                ->get();

            $period = null;

            foreach ($periods as $candidate) {
                $startAt = Carbon::today()->setTimeFromTimeString($candidate->getRawOriginal('start_time'));
                $endAt = Carbon::today()->setTimeFromTimeString($candidate->getRawOriginal('end_time'));

                $windowStart = $startAt->copy()->subMinutes($graceMinutes);
                $windowEnd = $endAt->copy()->addMinutes($graceMinutes);

                if ($now->betweenIncluded($windowStart, $windowEnd)) {
                    $period = $candidate;
                    break;
                }
            }

            if (!$period) {
                return $this->errorResponse('No active period at this time', null, 400);
            }

            $subject = Subject::where('course_id', $courseId)
                ->where('semester_id', $semesterId)
                ->where('period_id', $period->id)
                ->where('is_active', true)
                ->first();

            if (!$subject) {
                return $this->errorResponse('No subject scheduled for this period', null, 400);
            }

            $startAt = Carbon::today()->setTimeFromTimeString($period->getRawOriginal('start_time'));
            $endAt = Carbon::today()->setTimeFromTimeString($period->getRawOriginal('end_time'));

            $session = AttendanceSession::firstOrCreate(
                [
                    'course_id' => $courseId,
                    'semester_id' => $semesterId,
                    'subject_id' => $subject->id,
                    'period_id' => $period->id,
                    'starts_at' => $startAt,
                ],
                [
                    'ends_at' => $endAt,
                    'created_by' => $user->id,
                    'status' => 'active',
                    'qr_token' => (string) Str::uuid(),
                    'is_active' => true,
                ]
            );

            $existing = Attendance::where('user_id', $user->id)
                ->where('attendance_session_id', $session->id)
                ->first();

            if ($existing) {
                return $this->successResponse('Attendance already marked', $existing);
            }

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'attendance_session_id' => $session->id,
                'status' => 'present',
                'scanned_at' => $now,
                'device_id' => $validated['device_id'] ?? null,
                'ip_address' => $request->ip(),
            ]);

            return $this->successResponse('Attendance marked successfully', $attendance, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to scan attendance', ['error' => $e->getMessage()], 500);
        }
    }
}
