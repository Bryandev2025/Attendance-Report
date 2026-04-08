<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\StudentProfile;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'attendance_date' => ['required', 'date'],
        ]);

        $teacherId = $request->user()->id;
        $class = SchoolClass::query()->whereKey($data['class_id'])->firstOrFail();
        abort_unless($class->teacher_id === $teacherId, 403);

        $date = Carbon::parse($data['attendance_date'])->toDateString();

        $records = AttendanceRecord::query()
            ->with(['student.role', 'student.studentProfile'])
            ->where('class_id', $class->id)
            ->where('teacher_id', $teacherId)
            ->where('attendance_date', $date)
            ->orderBy('student_id')
            ->get();

        return response()->json(['data' => $records]);
    }

    public function mark(Request $request)
    {
        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'school_year_id' => ['required', 'integer', 'exists:school_years,id'],
            'attendance_date' => ['required', 'date'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:users,id'],
            'records.*.status' => ['required', Rule::in([
                AttendanceRecord::STATUS_PRESENT,
                AttendanceRecord::STATUS_ABSENT,
                AttendanceRecord::STATUS_LATE,
                AttendanceRecord::STATUS_EXCUSED,
            ])],
            'records.*.remarks' => ['nullable', 'string'],
        ]);

        $teacherId = $request->user()->id;
        $class = SchoolClass::query()->whereKey($data['class_id'])->firstOrFail();
        abort_unless($class->teacher_id === $teacherId, 403);

        $year = SchoolYear::query()->whereKey($data['school_year_id'])->firstOrFail();
        abort_unless($class->school_year_id === $year->id, 422);

        $date = Carbon::parse($data['attendance_date'])->toDateString();

        $saved = [];
        foreach ($data['records'] as $row) {
            $profile = StudentProfile::query()->where('user_id', $row['student_id'])->first();
            if (! $profile || (int) $profile->class_id !== (int) $class->id) {
                return response()->json([
                    'message' => "Student {$row['student_id']} is not in this class.",
                ], 422);
            }

            $record = AttendanceRecord::query()->updateOrCreate(
                [
                    'student_id' => $row['student_id'],
                    'class_id' => $class->id,
                    'attendance_date' => $date,
                ],
                [
                    'teacher_id' => $teacherId,
                    'school_year_id' => $year->id,
                    'status' => $row['status'],
                    'remarks' => $row['remarks'] ?? null,
                ]
            );

            $saved[] = $record;
        }

        AuditLogger::log($request->user(), 'attendance.mark', $class, "Marked attendance for {$date}");

        return response()->json([
            'data' => $saved,
        ], 201);
    }
}

