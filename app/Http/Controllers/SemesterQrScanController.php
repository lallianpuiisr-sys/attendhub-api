<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Enrollment;
use App\Models\Period;
use App\Models\Semester;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class SemesterQrScanController extends Controller
{
    private const EARTH_RADIUS_METERS = 6371000;

    private function nowMs(): float
    {
        return microtime(true) * 1000;
    }

    private function elapsedMs(float $startedAtMs): float
    {
        return round($this->nowMs() - $startedAtMs, 2);
    }

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

    private function resolveCurrentPeriod(int $courseId, int $semesterId, Carbon $now): ?Period
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Period> $periods */
        $periods = Period::where('course_id', $courseId)
            ->where('semester_id', $semesterId)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $diagnostics = [];

        foreach ($periods as $candidate) {
            if (!$candidate instanceof Period) {
                continue;
            }

            $startAt = $now->copy()->startOfDay()->setTimeFromTimeString($candidate->getRawOriginal('start_time'));
            $windowStart = $startAt->copy();
            $windowEnd = $candidate->scan_window_minutes === null
                ? $now->copy()->startOfDay()->setTimeFromTimeString($candidate->getRawOriginal('end_time'))
                : $startAt->copy()->addMinutes(max(1, (int) $candidate->scan_window_minutes));

            $diagnostics[] = [
                'period_id' => $candidate->id,
                'period_name' => $candidate->name,
                'start_time' => $candidate->getRawOriginal('start_time'),
                'end_time' => $candidate->getRawOriginal('end_time'),
                'scan_window_minutes' => $candidate->scan_window_minutes,
                'window_start' => $windowStart->toDateTimeString(),
                'window_end' => $windowEnd->toDateTimeString(),
                'now' => $now->toDateTimeString(),
                'matched' => $now->betweenIncluded($windowStart, $windowEnd),
            ];

            if ($now->betweenIncluded($windowStart, $windowEnd)) {
                return $candidate;
            }
        }


        return null;
    }

    private function hasConfiguredGeofence(Semester $semester): bool
    {
        return $semester->geofence_latitude !== null
            && $semester->geofence_longitude !== null
            && $semester->geofence_radius_meters !== null;
    }

    private function distanceInMeters(
        float $fromLatitude,
        float $fromLongitude,
        float $toLatitude,
        float $toLongitude
    ): float {
        $deltaLatitude = deg2rad($toLatitude - $fromLatitude);
        $deltaLongitude = deg2rad($toLongitude - $fromLongitude);

        $fromLatitudeRadians = deg2rad($fromLatitude);
        $toLatitudeRadians = deg2rad($toLatitude);

        $a = sin($deltaLatitude / 2) ** 2
            + cos($fromLatitudeRadians) * cos($toLatitudeRadians) * sin($deltaLongitude / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    // POST /api/qr/scan-semester
    public function scan(Request $request)
    {
        $requestStartedAtMs = $this->nowMs();
        $timings = [];

        try {
            $stepStartedAtMs = $this->nowMs();
            $validated = $request->validate([
                'token' => 'required|string',
                'device_id' => 'nullable|string|max:255',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);
            $timings['validate_request_ms'] = $this->elapsedMs($stepStartedAtMs);

            $stepStartedAtMs = $this->nowMs();
            $user = $request->user();
            $timings['resolve_user_ms'] = $this->elapsedMs($stepStartedAtMs);

            if (!$user) {
                return $this->errorResponse('Unauthenticated', null, 401);
            }

            $stepStartedAtMs = $this->nowMs();
            $semester = Semester::where('static_qr_token', $validated['token'])->first();
            $timings['lookup_semester_ms'] = $this->elapsedMs($stepStartedAtMs);

            if (!$semester) {
                return $this->errorResponse('I QR scan hi a awm lo', null, 404);
            }

            if (!$this->hasConfiguredGeofence($semester)) {
                return $this->errorResponse('Semester geolocation is not configured', null, 422);
            }

            if (!isset($validated['latitude']) || !isset($validated['longitude'])) {
                return $this->errorResponse('Location is required to scan this QR', null, 422);
            }

            $stepStartedAtMs = $this->nowMs();
            $distanceMeters = $this->distanceInMeters(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                (float) $semester->geofence_latitude,
                (float) $semester->geofence_longitude
            );
            $timings['geofence_distance_ms'] = $this->elapsedMs($stepStartedAtMs);

            if ($distanceMeters > (float) $semester->geofence_radius_meters) {
                return $this->errorResponse(
                    'You are outside the allowed attendance scan area',
                    [
                        'distance_meters' => round($distanceMeters, 2),
                        'allowed_radius_meters' => (int) $semester->geofence_radius_meters,
                    ],
                    403
                );
            }

            $courseId = $semester->course_id;
            $semesterId = $semester->id;

            $stepStartedAtMs = $this->nowMs();
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->where('semester_id', $semesterId)
                ->where('is_active', true)
                ->first();
            $timings['lookup_enrollment_ms'] = $this->elapsedMs($stepStartedAtMs);

            if (!$enrollment) {
                return $this->errorResponse('Student not enrolled for this course/semester', null, 403);
            }

            $now = Carbon::now(config('app.timezone'));

            $stepStartedAtMs = $this->nowMs();
            $period = $this->resolveCurrentPeriod($courseId, $semesterId, $now);
            $timings['resolve_period_ms'] = $this->elapsedMs($stepStartedAtMs);

            if (!$period) {
                return $this->errorResponse('Attendance scan window is closed for current periods', null, 400);
            }

            $dayOfWeek = strtolower($now->englishDayOfWeek);

            $stepStartedAtMs = $this->nowMs();
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
            $timings['lookup_subject_ms'] = $this->elapsedMs($stepStartedAtMs);

            if (!$subject) {
                return $this->errorResponse('No subject scheduled for this period', null, 400);
            }

            $startAt = $now->copy()->startOfDay()->setTimeFromTimeString($period->getRawOriginal('start_time'));
            $endAt = $now->copy()->startOfDay()->setTimeFromTimeString($period->getRawOriginal('end_time'));

            $stepStartedAtMs = $this->nowMs();
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
            $timings['first_or_create_session_ms'] = $this->elapsedMs($stepStartedAtMs);

            $stepStartedAtMs = $this->nowMs();
            $existingAttendance = Attendance::where('user_id', $user->id)
                ->where('attendance_session_id', $session->id)
                ->first();
            $timings['lookup_existing_attendance_ms'] = $this->elapsedMs($stepStartedAtMs);

            if ($existingAttendance) {
                $timings['total_ms'] = $this->elapsedMs($requestStartedAtMs);
                Log::info('Semester QR scan completed', [
                    'user_id' => $user->id,
                    'semester_id' => $semesterId,
                    'course_id' => $courseId,
                    'period_id' => $period->id,
                    'subject_id' => $subject->id,
                    'result' => 'already_scanned',
                    'distance_meters' => round($distanceMeters, 2),
                    'timings_ms' => $timings,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Already scanned for this period. You are already marked present.',
                    'data' => $existingAttendance,
                    'already_scanned' => true,
                ], 200);
            }

            $stepStartedAtMs = $this->nowMs();
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'attendance_session_id' => $session->id,
                'status' => 'present',
                'scanned_at' => $now,
                'device_id' => $validated['device_id'] ?? null,
                'ip_address' => $request->ip(),
            ]);
            $timings['create_attendance_ms'] = $this->elapsedMs($stepStartedAtMs);
            $timings['total_ms'] = $this->elapsedMs($requestStartedAtMs);

            Log::info('Semester QR scan completed', [
                'user_id' => $user->id,
                'semester_id' => $semesterId,
                'course_id' => $courseId,
                'period_id' => $period->id,
                'subject_id' => $subject->id,
                'result' => 'marked_present',
                'distance_meters' => round($distanceMeters, 2),
                'timings_ms' => $timings,
            ]);

            return $this->successResponse('Attendance marked successfully', $attendance, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to scan semester QR', ['error' => $e->getMessage()], 500);
        }
    }
}
