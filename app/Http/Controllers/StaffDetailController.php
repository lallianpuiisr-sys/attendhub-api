<?php

namespace App\Http\Controllers;

use App\Models\StaffDetail;
use App\Services\StaffDetailService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class StaffDetailController extends Controller
{
    public function __construct(private StaffDetailService $staffDetailService)
    {
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

    // GET /api/staff-details
    public function index()
    {
        try {
            $staffDetails = $this->staffDetailService->listAll();

            return $this->successResponse('Staff details fetched successfully', $staffDetails);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch staff details', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/staff-details
    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->staffDetailService->validationRules());
            $validated = $this->staffDetailService->normalizePayload($validated, $request->user());

            $staffDetail = $this->staffDetailService->create($validated);

            return $this->successResponse('Staff detail created successfully', $staffDetail, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to create staff detail', ['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/staff-details/{id}
    public function show($id)
    {
        try {
            $staffDetail = $this->staffDetailService->findByIdOrFail((int) $id);

            return $this->successResponse('Staff detail fetched successfully', $staffDetail);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Staff detail not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch staff detail', ['error' => $e->getMessage()], 500);
        }
    }

    // PUT /api/staff-details/{id}
    public function update(Request $request, $id)
    {
        try {
            $staffDetail = StaffDetail::findOrFail($id);

            $rules = $this->staffDetailService->validationRules((int) $staffDetail->id, true);

            $validated = $request->validate($rules);
            $validated = $this->staffDetailService->normalizePayload($validated, $request->user());

            $staffDetail = $this->staffDetailService->update($staffDetail, $validated);

            return $this->successResponse('Staff detail updated successfully', $staffDetail);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Staff detail not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to update staff detail', ['error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/staff-details/{id}
    public function destroy($id)
    {
        try {
            $staffDetail = StaffDetail::findOrFail($id);
            $this->staffDetailService->delete($staffDetail);

            return $this->successResponse('Staff detail deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Staff detail not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to delete staff detail', ['error' => $e->getMessage()], 500);
        }
    }
}
