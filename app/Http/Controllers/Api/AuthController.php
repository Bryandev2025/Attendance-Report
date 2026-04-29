<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['nullable', 'email'],
            'login' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $credential = trim((string) ($data['email'] ?? $data['login'] ?? ''));

        if ($credential === '') {
            throw ValidationException::withMessages([
                'email' => ['Email or login is required.'],
            ]);
        }

        $with = [
            'role',
            'studentProfile.schoolClass.program',
            'studentProfile.schoolClass.teacher',
            'teacherProfile',
        ];

        /** @var User|null $user */
        $user = User::query()
            ->with($with)
            ->where('email', $credential)
            ->first();

        if (! $user && ! str_contains($credential, '@')) {
            $profile = StudentProfile::query()->where('student_number', $credential)->first();
            if ($profile) {
                $user = User::query()
                    ->with($with)
                    ->whereKey($profile->user_id)
                    ->first();
            }
        }

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->isActive()) {
            return response()->json(['message' => 'Account is inactive.'], 403);
        }

        $token = $user->createToken($data['device_name'] ?? 'api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }

    public function me(Request $request)
    {
        $user = $request->user()?->load([
            'role',
            'studentProfile.schoolClass.program',
            'studentProfile.schoolClass.teacher',
            'teacherProfile',
        ]);

        return response()->json(['user' => $user]);
    }
}

