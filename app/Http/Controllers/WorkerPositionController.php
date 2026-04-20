<?php

namespace App\Http\Controllers;

use App\Models\WorkerPosition;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class WorkerPositionController extends Controller
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

    // GET /api/worker-positions
    public function index()
    {
        try {
            $workerPositions = WorkerPosition::orderByDesc('id')->get();

            return $this->successResponse('Worker positions fetched successfully', $workerPositions);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch worker positions', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/worker-positions
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'timestamp' => 'nullable|date',
                'is_active' => 'boolean',
            ]);

            $workerPosition = WorkerPosition::create($validated);

            return $this->successResponse('Worker position created successfully', $workerPosition, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to create worker position', ['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/worker-positions/{id}
    public function show($id)
    {
        try {
            $workerPosition = WorkerPosition::findOrFail($id);

            return $this->successResponse('Worker position fetched successfully', $workerPosition);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Worker position not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch worker position', ['error' => $e->getMessage()], 500);
        }
    }

    // PUT /api/worker-positions/{id}
    public function update(Request $request, $id)
    {
        try {
            $workerPosition = WorkerPosition::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'timestamp' => 'nullable|date',
                'is_active' => 'boolean',
            ]);

            $workerPosition->update($validated);

            return $this->successResponse('Worker position updated successfully', $workerPosition);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Worker position not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to update worker position', ['error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/worker-positions/{id}
    public function destroy($id)
    {
        try {
            $workerPosition = WorkerPosition::findOrFail($id);
            $workerPosition->delete();

            return $this->successResponse('Worker position deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Worker position not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to delete worker position', ['error' => $e->getMessage()], 500);
        }
    }
}
