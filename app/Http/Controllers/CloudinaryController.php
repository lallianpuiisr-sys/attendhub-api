<?php

namespace App\Http\Controllers;

use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Throwable;

class CloudinaryController extends Controller
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

    public function uploadAvatar(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Unauthorized', null, 401);
            }

            $validated = $request->validate([
                'avatar' => 'required|image|max:5120',
            ]);

            $cloudUrl = config('cloudinary.cloud_url');

            if (!$cloudUrl) {
                return $this->errorResponse('Cloudinary is not configured.', null, 500);
            }

            $cloudinary = new Cloudinary($cloudUrl);
            $upload = $cloudinary->uploadApi()->upload(
                $validated['avatar']->getRealPath(),
                [
                    'folder' => 'avatars',
                    'public_id' => 'user_' . $user->id . '_' . time(),
                    'resource_type' => 'image',
                ]
            );

            $avatarUrl = $upload['secure_url'] ?? $upload['url'] ?? null;

            if (!$avatarUrl) {
                return $this->errorResponse('Avatar upload failed.', null, 500);
            }

            $user->avatar_url = $avatarUrl;
            $user->save();

            return $this->successResponse('Avatar uploaded successfully', [
                'avatar_url' => $avatarUrl,
                'public_id' => $upload['public_id'] ?? null,
                'user' => $user->fresh(['course', 'semester']),
            ]);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return $this->errorResponse('Avatar upload failed', ['error' => $e->getMessage()], 500);
        }
    }
}
