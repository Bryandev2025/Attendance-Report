<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsenceReport;
use App\Notifications\AbsenceReportReviewed;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AbsenceReportController extends Controller
{
    public function index(Request $request)
    {
        $query = AbsenceReport::query()
            ->with(['student.studentProfile', 'schoolClass.schoolYear', 'attendanceRecord', 'attachments', 'reviewedBy'])
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->integer('class_id'));
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->integer('student_id'));
        }

        return response()->json([
            'data' => $query->paginate(20),
        ]);
    }

    public function show(AbsenceReport $absenceReport)
    {
        return response()->json([
            'data' => $absenceReport->load(['student.studentProfile', 'schoolClass.schoolYear', 'attendanceRecord', 'attachments', 'reviewedBy']),
        ]);
    }

    public function approve(Request $request, AbsenceReport $absenceReport)
    {
        $data = $request->validate([
            'admin_remarks' => ['nullable', 'string'],
        ]);

        $absenceReport->status = AbsenceReport::STATUS_APPROVED;
        $absenceReport->reviewed_by = $request->user()->id;
        $absenceReport->reviewed_at = Carbon::now();
        $absenceReport->admin_remarks = $data['admin_remarks'] ?? null;
        $absenceReport->save();

        AuditLogger::log($request->user(), 'absence_report.approve', $absenceReport, 'Approved absence report (admin)');
        $absenceReport->student?->notify(new AbsenceReportReviewed($absenceReport));

        return response()->json([
            'data' => $absenceReport->load(['student', 'attachments', 'reviewedBy']),
        ]);
    }

    public function reject(Request $request, AbsenceReport $absenceReport)
    {
        $data = $request->validate([
            'admin_remarks' => ['required', 'string'],
        ]);

        $absenceReport->status = AbsenceReport::STATUS_REJECTED;
        $absenceReport->reviewed_by = $request->user()->id;
        $absenceReport->reviewed_at = Carbon::now();
        $absenceReport->admin_remarks = $data['admin_remarks'];
        $absenceReport->save();

        AuditLogger::log($request->user(), 'absence_report.reject', $absenceReport, 'Rejected absence report (admin)');
        $absenceReport->student?->notify(new AbsenceReportReviewed($absenceReport));

        return response()->json([
            'data' => $absenceReport->load(['student', 'attachments', 'reviewedBy']),
        ]);
    }
}

