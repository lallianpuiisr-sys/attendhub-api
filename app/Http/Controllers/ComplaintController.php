<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ComplaintController extends Controller
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

    // GET /api/complaints
    public function index()
    {
        try {
            $complaints = Complaint::with(['subject', 'period'])->latest()->get();

            return $this->successResponse('Complaints fetched successfully', $complaints);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch complaints', ['error' => $e->getMessage()], 500);
        }
    }

    // POST /api/complaints
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'complaint_type' => 'required|string|max:255',
                'date_of_class' => 'required|date',
                'subject_id' => 'required|exists:subjects,id',
                'period_id' => 'required|exists:periods,id',
                'reason' => 'required|string',
                'file_url' => 'nullable|string|max:2048',
            ]);

            $complaint = Complaint::create($validated);

            return $this->successResponse('Complaint created successfully', $complaint, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to create complaint', ['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/complaints/{id}
    public function show($id)
    {
        try {
            $complaint = Complaint::with(['subject', 'period'])->findOrFail($id);

            return $this->successResponse('Complaint fetched successfully', $complaint);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Complaint not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to fetch complaint', ['error' => $e->getMessage()], 500);
        }
    }

    // PUT /api/complaints/{id}
    public function update(Request $request, $id)
    {
        try {
            $complaint = Complaint::findOrFail($id);

            $validated = $request->validate([
                'complaint_type' => 'sometimes|required|string|max:255',
                'date_of_class' => 'sometimes|required|date',
                'subject_id' => 'sometimes|required|exists:subjects,id',
                'period_id' => 'sometimes|required|exists:periods,id',
                'reason' => 'sometimes|required|string',
                'file_url' => 'nullable|string|max:2048',
            ]);

            $complaint->update($validated);

            return $this->successResponse('Complaint updated successfully', $complaint);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Complaint not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to update complaint', ['error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/complaints/{id}
    public function destroy($id)
    {
        try {
            $complaint = Complaint::findOrFail($id);
            $complaint->delete();

            return $this->successResponse('Complaint deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Complaint not found', null, 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Failed to delete complaint', ['error' => $e->getMessage()], 500);
        }
    }
}
