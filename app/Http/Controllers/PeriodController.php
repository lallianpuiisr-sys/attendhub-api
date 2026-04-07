<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Period;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class PeriodController extends Controller
{
    private const DAY_ORDER = [
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
        'sunday' => 7,
    ];

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

    private function formatPeriodsWithSubjects($periods)
    {
        return $periods->map(function ($period) {
            $subjects = $period->subjects->sortBy(function ($subject) {
                return self::DAY_ORDER[$subject->day_of_week] ?? 99;
            })->values();

            $periodData = $period->toArray();
            $periodData['subject'] = $subjects->first();
            $periodData['subjects'] = $subjects;

            return $periodData;
        })->values();
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

    // GET /api/periods/by-context?user_id=1&course_id=1&semester_id=1
    public function byContext(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'course_id' => 'nullable|exists:courses,id',
                'semester_id' => 'nullable|exists:semesters,id',
            ]);

            $user = User::findOrFail($validated['user_id']);
            $courseId = $validated['course_id'] ?? $user->course_id;
            $semesterId = $validated['semester_id'] ?? $user->semester_id;

            if (!$courseId || !$semesterId) {
                return $this->errorResponse(
                    'Course and semester are required for this user',
                    ['course_id' => ['Course or semester is missing for the selected user.']],
                    422
                );
            }

            $isEnrolled = Enrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->where('semester_id', $semesterId)
                ->where('is_active', true)
                ->exists();

            if (!$isEnrolled && ((int) $user->course_id !== (int) $courseId || (int) $user->semester_id !== (int) $semesterId)) {
                return $this->errorResponse(
                    'User is not assigned to the requested course and semester',
                    null,
                    403
                );
            }

            $periods = Period::with([
                'course',
                'semester',
                'subjects' => function ($query) use ($courseId, $semesterId) {
                    $query->where('course_id', $courseId)
                        ->where('semester_id', $semesterId)
                        ->where('is_active', true)
                        ->orderBy('day_of_week')
                        ->orderBy('id');
                },
            ])
                ->where('course_id', $courseId)
                ->where('semester_id', $semesterId)
                ->where('is_active', true)
                ->orderBy('start_time')
                ->get();

            return $this->successResponse('Periods fetched successfully', [
                'user_id' => $user->id,
                'course_id' => $courseId,
                'semester_id' => $semesterId,
                'periods' => $this->formatPeriodsWithSubjects($periods),
            ]);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('User not found', null, 404);
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
                'scan_window_minutes' => 'sometimes|integer|min:1|max:60',
                'course_id' => 'nullable|exists:courses,id',
                'semester_id' => 'nullable|exists:semesters,id',
                'is_active' => 'boolean',
            ]);

            $validated['scan_window_minutes'] = $validated['scan_window_minutes'] ?? 5;

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
                'scan_window_minutes' => 'sometimes|integer|min:1|max:60',
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
