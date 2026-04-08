<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with(['role', 'studentProfile.schoolClass', 'teacherProfile']);

        if ($request->filled('role')) {
            $query->whereHas('role', fn ($q) => $q->where('name', $request->string('role')));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($w) use ($q) {
                $w->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        return response()->json([
            'data' => $query->orderBy('id', 'desc')->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'status' => ['nullable', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE])],

            'student_profile' => ['nullable', 'array'],
            'student_profile.class_id' => ['required_if:student_profile,array', 'integer', 'exists:classes,id'],
            'student_profile.student_number' => ['required_if:student_profile,array', 'string', 'max:255', 'unique:student_profiles,student_number'],
            'student_profile.gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'student_profile.birth_date' => ['nullable', 'date'],
            'student_profile.contact_number' => ['nullable', 'string', 'max:255'],
            'student_profile.guardian_name' => ['nullable', 'string', 'max:255'],
            'student_profile.guardian_contact_number' => ['nullable', 'string', 'max:255'],
            'student_profile.address' => ['nullable', 'string'],

            'teacher_profile' => ['nullable', 'array'],
            'teacher_profile.employee_id' => ['required_if:teacher_profile,array', 'string', 'max:255', 'unique:teacher_profiles,employee_id'],
            'teacher_profile.contact_number' => ['nullable', 'string', 'max:255'],
            'teacher_profile.address' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'role_id' => $data['role_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'status' => $data['status'] ?? User::STATUS_ACTIVE,
        ]);

        $role = Role::find($data['role_id']);
        if ($role?->name === 'student') {
            $profile = $data['student_profile'] ?? null;
            if ($profile) {
                StudentProfile::create(['user_id' => $user->id, ...$profile]);
            }
        }

        if ($role?->name === 'teacher') {
            $profile = $data['teacher_profile'] ?? null;
            if ($profile) {
                TeacherProfile::create(['user_id' => $user->id, ...$profile]);
            }
        }

        return response()->json([
            'data' => $user->load(['role', 'studentProfile.schoolClass', 'teacherProfile']),
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json([
            'data' => $user->load(['role', 'studentProfile.schoolClass', 'teacherProfile']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => ['sometimes', 'integer', 'exists:roles,id'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'status' => ['sometimes', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE])],

            'student_profile' => ['nullable', 'array'],
            'student_profile.class_id' => ['sometimes', 'integer', 'exists:classes,id'],
            'student_profile.student_number' => ['sometimes', 'string', 'max:255', Rule::unique('student_profiles', 'student_number')->ignore($user->studentProfile?->id)],
            'student_profile.gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'student_profile.birth_date' => ['nullable', 'date'],
            'student_profile.contact_number' => ['nullable', 'string', 'max:255'],
            'student_profile.guardian_name' => ['nullable', 'string', 'max:255'],
            'student_profile.guardian_contact_number' => ['nullable', 'string', 'max:255'],
            'student_profile.address' => ['nullable', 'string'],

            'teacher_profile' => ['nullable', 'array'],
            'teacher_profile.employee_id' => ['sometimes', 'string', 'max:255', Rule::unique('teacher_profiles', 'employee_id')->ignore($user->teacherProfile?->id)],
            'teacher_profile.contact_number' => ['nullable', 'string', 'max:255'],
            'teacher_profile.address' => ['nullable', 'string'],
        ]);

        if (array_key_exists('password', $data) && $data['password'] === null) {
            unset($data['password']);
        }

        $user->fill($data);
        $user->save();

        if (array_key_exists('student_profile', $data)) {
            $profileData = $data['student_profile'];
            if ($profileData === null) {
                $user->studentProfile()?->delete();
            } else {
                $user->studentProfile()->updateOrCreate(['user_id' => $user->id], $profileData);
            }
        }

        if (array_key_exists('teacher_profile', $data)) {
            $profileData = $data['teacher_profile'];
            if ($profileData === null) {
                $user->teacherProfile()?->delete();
            } else {
                $user->teacherProfile()->updateOrCreate(['user_id' => $user->id], $profileData);
            }
        }

        return response()->json([
            'data' => $user->load(['role', 'studentProfile.schoolClass', 'teacherProfile']),
        ]);
    }

    public function destroy(User $user)
    {
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['ok' => true]);
    }
}

