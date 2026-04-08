<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class MyAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $studentId = $request->user()->id;

        $query = AttendanceRecord::query()
            ->with(['schoolClass.schoolYear', 'teacher'])
            ->where('student_id', $studentId)
            ->orderByDesc('attendance_date');

        if ($request->filled('school_year_id')) {
            $query->where('school_year_id', $request->integer('school_year_id'));
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->integer('class_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json([
            'data' => $query->paginate(20),
        ]);
    }
}

