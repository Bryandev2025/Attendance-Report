<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_user(): void
    {
        $role = Role::query()->create(['name' => 'student', 'display_name' => 'Student']);
        $user = User::query()->create([
            'role_id' => $role->id,
            'first_name' => 'A',
            'last_name' => 'B',
            'email' => 's@example.com',
            'password' => Hash::make('password'),
            'status' => User::STATUS_ACTIVE,
        ]);

        $res = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $res->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'email', 'role_id', 'first_name', 'last_name', 'full_name'],
            ]);
    }

    public function test_student_can_login_with_student_number(): void
    {
        $teacherRole = Role::query()->create(['name' => 'teacher', 'display_name' => 'Teacher']);
        $studentRole = Role::query()->create(['name' => 'student', 'display_name' => 'Student']);

        $teacher = User::query()->create([
            'role_id' => $teacherRole->id,
            'first_name' => 'Tea',
            'last_name' => 'Cher',
            'email' => 't-auth@example.com',
            'password' => Hash::make('password'),
            'status' => User::STATUS_ACTIVE,
        ]);

        $year = SchoolYear::query()->create([
            'name' => 'SY Auth',
            'start_date' => '2025-06-01',
            'end_date' => '2026-03-31',
            'is_active' => true,
        ]);

        $class = SchoolClass::query()->create([
            'school_year_id' => $year->id,
            'teacher_id' => $teacher->id,
            'class_name' => 'DemoAuth',
            'grade_level' => '1',
            'section' => 'A',
        ]);

        $user = User::query()->create([
            'role_id' => $studentRole->id,
            'first_name' => 'Pat',
            'last_name' => 'Lee',
            'email' => 'pat@example.com',
            'password' => Hash::make('password'),
            'status' => User::STATUS_ACTIVE,
        ]);

        StudentProfile::query()->create([
            'user_id' => $user->id,
            'class_id' => $class->id,
            'student_number' => 'STU-9001',
        ]);

        $this->postJson('/api/auth/login', [
            'login' => 'STU-9001',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'email', 'role_id', 'first_name', 'last_name', 'full_name'],
            ]);
    }

    public function test_me_requires_auth(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }
}

