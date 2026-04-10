<?php

namespace App\Services;

use App\Models\StaffDetail;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;

class StaffDetailService
{
    public function validationRules(?int $staffDetailId = null, bool $isUpdate = false): array
    {
        $userRule = 'required|exists:users,id|unique:staff_details,user_id';

        if ($isUpdate) {
            $userRule = 'sometimes|required|exists:users,id|unique:staff_details,user_id,' . $staffDetailId;
        }

        return [
            'user_id' => $userRule,
            'position' => 'nullable|string|max:255',
            'is_admin' => 'boolean',
            'is_teacher' => 'boolean',
            'is_receptionist' => 'boolean',
            'is_approved' => 'boolean',
            'phone_1' => 'nullable|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'approved_at' => 'nullable|date',
            'approved_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ];
    }

    public function normalizePayload(array $validated, ?Authenticatable $actor = null): array
    {
        if (!array_key_exists('is_approved', $validated)) {
            return $validated;
        }

        if ((bool) $validated['is_approved']) {
            $validated['approved_at'] = $validated['approved_at'] ?? now();
            $validated['approved_by'] = $validated['approved_by'] ?? $actor?->getAuthIdentifier();

            return $validated;
        }

        $validated['approved_at'] = null;
        $validated['approved_by'] = null;

        return $validated;
    }

    public function listAll(): Collection
    {
        return StaffDetail::with(['user', 'approver'])->latest()->get();
    }

    public function findByIdOrFail(int $id): StaffDetail
    {
        return StaffDetail::with(['user', 'approver'])->findOrFail($id);
    }

    public function create(array $payload): StaffDetail
    {
        return StaffDetail::create($payload)->load(['user', 'approver']);
    }

    public function update(StaffDetail $staffDetail, array $payload): StaffDetail
    {
        $staffDetail->update($payload);

        return $staffDetail->load(['user', 'approver']);
    }

    public function delete(StaffDetail $staffDetail): void
    {
        $staffDetail->delete();
    }
}
