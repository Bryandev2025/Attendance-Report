<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardStatsController extends Controller
{
    public function index(Request $request)
    {
        $teacherId = $request->user()->id;
        $data = $request->validate([
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $end = isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : Carbon::today()->endOfDay();
        $start = isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : Carbon::today()->subDays(6)->startOfDay();

        if ($start->diffInDays($end) > 31) {
            return response()->json([
                'message' => 'Date range must not exceed 31 days.',
            ], 422);
        }

        $query = AttendanceRecord::query()
            ->where('teacher_id', $teacherId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()]);

        if (! empty($data['class_id'])) {
            $class = SchoolClass::query()->whereKey($data['class_id'])->firstOrFail();
            abort_unless((int) $class->teacher_id === (int) $teacherId, 403);
            $query->where('class_id', (int) $data['class_id']);
        }

        $records = $query->get(['attendance_date', 'status']);

        $labels = [];
        $counts = [];
        $totalDays = $start->diffInDays($end) + 1;
        for ($i = 0; $i < $totalDays; $i++) {
            $day = $start->copy()->addDays($i);
            $labels[] = $day->format('M d');
            $counts[] = 0;
        }

        foreach ($records as $record) {
            $dayIndex = Carbon::parse($record->attendance_date)->diffInDays($start, false);
            if ($dayIndex >= 0 && $dayIndex < $totalDays) {
                $counts[$dayIndex]++;
            }
        }

        $statusCounts = [
            AttendanceRecord::STATUS_PRESENT => 0,
            AttendanceRecord::STATUS_ABSENT => 0,
            AttendanceRecord::STATUS_LATE => 0,
            AttendanceRecord::STATUS_EXCUSED => 0,
        ];

        foreach ($records as $record) {
            if (array_key_exists($record->status, $statusCounts)) {
                $statusCounts[$record->status]++;
            }
        }

        return response()->json([
            'data' => [
                'chart' => [
                    'labels' => $labels,
                    'values' => $counts,
                    'label' => 'Attendance records',
                ],
                'status_counts' => $statusCounts,
                'range' => [
                    'from' => $start->toDateString(),
                    'to' => $end->toDateString(),
                ],
            ],
        ]);
    }
}
