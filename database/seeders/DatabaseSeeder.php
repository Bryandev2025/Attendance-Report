<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Admin']
        );
        $teacherRole = Role::query()->firstOrCreate(
            ['name' => 'teacher'],
            ['display_name' => 'Teacher']
        );
        $studentRole = Role::query()->firstOrCreate(
            ['name' => 'student'],
            ['display_name' => 'Student']
        );

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'role_id' => $adminRole->id,
                'first_name' => 'System',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'status' => User::STATUS_ACTIVE,
            ]
        );

        $teacher = User::query()->updateOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'role_id' => $teacherRole->id,
                'first_name' => 'Default',
                'last_name' => 'Teacher',
                'password' => Hash::make('password'),
                'status' => User::STATUS_ACTIVE,
            ]
        );

        TeacherProfile::query()->updateOrCreate(
            ['user_id' => $teacher->id],
            [
                'employee_id' => 'T-0001',
                'contact_number' => '0000000000',
                'address' => 'School',
            ]
        );

        $year = SchoolYear::query()->firstOrCreate(
            ['name' => 'SY 2025-2026'],
            [
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
                'is_active' => true,
            ]
        );

        SchoolYear::query()->whereKeyNot($year->id)->update(['is_active' => false]);

        $class = SchoolClass::query()->firstOrCreate(
            ['school_year_id' => $year->id, 'class_name' => 'Advisory'],
            [
                'teacher_id' => $teacher->id,
                'grade_level' => '10',
                'section' => 'A',
                'description' => 'Default seeded class',
            ]
        );

        $student = User::query()->updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'role_id' => $studentRole->id,
                'first_name' => 'Default',
                'last_name' => 'Student',
                'password' => Hash::make('password'),
                'status' => User::STATUS_ACTIVE,
            ]
        );

        StudentProfile::query()->updateOrCreate(
            ['user_id' => $student->id],
            [
                'class_id' => $class->id,
                'student_number' => 'S-0001',
                'gender' => 'other',
                'contact_number' => '0000000000',
                'guardian_name' => 'Guardian',
                'guardian_contact_number' => '0000000000',
                'address' => 'Home',
            ]
        );
    }
}
