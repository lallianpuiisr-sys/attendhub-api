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

    // GET /api/attendances/user-summary?sort_by=daily|weekly|monthly|semester&user_id=1
    public function userSummary(Request $request)
    {
        try {
            $validated = $request->validate([
                'sort_by' => 'required|in:daily,weekly,monthly,semester',
                'user_id' => 'nullable|exists:users,id',
                'semester_id' => 'nullable|exists:semesters,id',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date',
            ]);

            $requestUser = $request->user();

            if (!$requestUser) {
                return $this->errorResponse('Unauthenticated', null, 401);
            }

            $role = strtolower((string) ($requestUser->role ?? ''));
            $isTeacherOrAdmin = in_array($role, ['teacher', 'admin'], true);

            $targetUserId = (int) ($validated['user_id'] ?? $requestUser->id);
            if (!$isTeacherOrAdmin && $targetUserId !== (int) $requestUser->id) {
                return $this->errorResponse('You can only view your own attendance summary', null, 403);
            }

            if ($validated['sort_by'] === 'semester' && !$isTeacherOrAdmin) {
                return $this->errorResponse('Semester-wise summary is only available for teacher/admin', null, 403);
            }

            $query = Attendance::query()
                ->from('attendances')
                ->leftJoin('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->where('attendances.user_id', $targetUserId);

            if (!empty($validated['semester_id'])) {
                $query->where('attendance_sessions.semester_id', (int) $validated['semester_id']);
            }

            if (!empty($validated['from_date'])) {
                $query->whereRaw('DATE(COALESCE(attendances.scanned_at, attendances.created_at)) >= ?', [$validated['from_date']]);
            }

            if (!empty($validated['to_date'])) {
                $query->whereRaw('DATE(COALESCE(attendances.scanned_at, attendances.created_at)) <= ?', [$validated['to_date']]);
            }

            switch ($validated['sort_by']) {
                case 'daily':
                    $query->selectRaw("DATE(COALESCE(attendances.scanned_at, attendances.created_at)) as bucket")
                        ->groupBy('bucket')
                        ->orderBy('bucket', 'desc');
                    break;
                case 'weekly':
                    $query->selectRaw("YEAR(COALESCE(attendances.scanned_at, attendances.created_at)) as year")
                        ->selectRaw("WEEK(COALESCE(attendances.scanned_at, attendances.created_at), 1) as week")
                        ->groupBy('year', 'week')
                        ->orderBy('year', 'desc')
                        ->orderBy('week', 'desc');
                    break;
                case 'monthly':
                    $query->selectRaw("YEAR(COALESCE(attendances.scanned_at, attendances.created_at)) as year")
                        ->selectRaw("MONTH(COALESCE(attendances.scanned_at, attendances.created_at)) as month")
                        ->groupBy('year', 'month')
                        ->orderBy('year', 'desc')
                        ->orderBy('month', 'desc');
                    break;
                case 'semester':
                    $query->leftJoin('semesters', 'semesters.id', '=', 'attendance_sessions.semester_id')
                        ->selectRaw('attendance_sessions.semester_id as semester_id')
                        ->selectRaw('semesters.title as semester_title')
                        ->groupBy('attendance_sessions.semester_id', 'semesters.title')
                        ->orderBy('attendance_sessions.semester_id', 'desc');
                    break;
            }

            $query->selectRaw('COUNT(attendances.id) as total_count')
                ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present_count")
                ->selectRaw("SUM(CASE WHEN attendances.status = 'absent' THEN 1 ELSE 0 END) as absent_count")
                ->selectRaw("SUM(CASE WHEN attendances.status = 'late' THEN 1 ELSE 0 END) as late_count");

            $rows = $query->get()->map(function ($row) use ($validated) {
                $total = (int) ($row->total_count ?? 0);
                $present = (int) ($row->present_count ?? 0);
                $absent = (int) ($row->absent_count ?? 0);
                $late = (int) ($row->late_count ?? 0);

                $item = [
                    'total_count' => $total,
                    'present_count' => $present,
                    'absent_count' => $absent,
                    'late_count' => $late,
                    'present_percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
                ];

                if ($validated['sort_by'] === 'daily') {
                    $item['date'] = $row->bucket;
                } elseif ($validated['sort_by'] === 'weekly') {
                    $item['year'] = (int) $row->year;
                    $item['week'] = (int) $row->week;
                } elseif ($validated['sort_by'] === 'monthly') {
                    $item['year'] = (int) $row->year;
                    $item['month'] = (int) $row->month;
                } elseif ($validated['sort_by'] === 'semester') {
                    $item['semester_id'] = (int) $row->semester_id;
                    $item['semester_title'] = $row->semester_title;
                }

                return $item;
            });

            return $this->successResponse('Attendance summary fetched successfully', [
                'sort_by' => $validated['sort_by'],
                'user_id' => $targetUserId,
                'filters' => [
                    'semester_id' => $validated['semester_id'] ?? null,
                    'from_date' => $validated['from_date'] ?? null,
                    'to_date' => $validated['to_date'] ?? null,
                ],
                'items' => $rows,
            ]);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch attendance summary', ['error' => $e->getMessage()], 500);
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

            $periods = Period::where('course_id', $courseId)
                ->where('semester_id', $semesterId)
                ->orderBy('start_time')
                ->get();

            $period = null;

            foreach ($periods as $candidate) {
                $startAt = Carbon::today()->setTimeFromTimeString($candidate->getRawOriginal('start_time'));
                $scanWindowMinutes = max(1, (int) ($candidate->scan_window_minutes ?? 5));
                $windowStart = $startAt->copy();
                $windowEnd = $startAt->copy()->addMinutes($scanWindowMinutes);

                if ($now->betweenIncluded($windowStart, $windowEnd)) {
                    $period = $candidate;
                    break;
                }
            }

            if (!$period) {
                return $this->errorResponse('Attendance scan window is closed for current periods', null, 400);
            }

            $dayOfWeek = strtolower($now->englishDayOfWeek);

            $subject = Subject::where('course_id', $courseId)
                ->where('semester_id', $semesterId)
                ->where('period_id', $period->id)
                ->where(function ($query) use ($dayOfWeek) {
                    $query->where('day_of_week', $dayOfWeek)
                        ->orWhereNull('day_of_week');
                })
                ->where('is_active', true)
                ->orderByRaw("CASE WHEN day_of_week = ? THEN 0 ELSE 1 END", [$dayOfWeek])
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
