<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['ok' => true]));

Route::prefix('auth')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login'])->middleware('throttle:login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::get('/me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
    });
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/roles', [\App\Http\Controllers\Api\RoleController::class, 'index'])->middleware('role:admin');

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
        Route::apiResource('school-years', \App\Http\Controllers\Api\Admin\SchoolYearController::class);
        Route::post('school-years/{school_year}/set-active', [\App\Http\Controllers\Api\Admin\SchoolYearController::class, 'setActive']);
        Route::apiResource('classes', \App\Http\Controllers\Api\Admin\SchoolClassController::class);

        Route::get('audit-logs', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'index']);

        Route::get('absence-reports', [\App\Http\Controllers\Api\Admin\AbsenceReportController::class, 'index']);
        Route::get('absence-reports/{absence_report}', [\App\Http\Controllers\Api\Admin\AbsenceReportController::class, 'show']);
        Route::post('absence-reports/{absence_report}/approve', [\App\Http\Controllers\Api\Admin\AbsenceReportController::class, 'approve']);
        Route::post('absence-reports/{absence_report}/reject', [\App\Http\Controllers\Api\Admin\AbsenceReportController::class, 'reject']);
    });

    Route::prefix('teacher')->middleware('role:teacher')->group(function () {
        Route::get('classes', [\App\Http\Controllers\Api\Teacher\MyClassesController::class, 'index']);
        Route::post('attendance/mark', [\App\Http\Controllers\Api\Teacher\AttendanceController::class, 'mark']);
        Route::get('attendance', [\App\Http\Controllers\Api\Teacher\AttendanceController::class, 'index']);

        Route::post('attendance-sessions', [\App\Http\Controllers\Api\Teacher\AttendanceSessionController::class, 'store']);
        Route::post('attendance-sessions/{attendance_session}/close', [\App\Http\Controllers\Api\Teacher\AttendanceSessionController::class, 'close']);
        Route::get('attendance-sessions/{attendance_session}/qr', [\App\Http\Controllers\Api\Teacher\AttendanceSessionController::class, 'qr']);

        Route::get('absence-reports', [\App\Http\Controllers\Api\Teacher\AbsenceReportReviewController::class, 'index']);
        Route::post('absence-reports/{absence_report}/approve', [\App\Http\Controllers\Api\Teacher\AbsenceReportReviewController::class, 'approve']);
        Route::post('absence-reports/{absence_report}/reject', [\App\Http\Controllers\Api\Teacher\AbsenceReportReviewController::class, 'reject']);
    });

    Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('attendance', [\App\Http\Controllers\Api\Student\MyAttendanceController::class, 'index']);
        Route::get('absence-reports', [\App\Http\Controllers\Api\Student\MyAbsenceReportsController::class, 'index']);
        Route::post('absence-reports', [\App\Http\Controllers\Api\Student\MyAbsenceReportsController::class, 'store']);
        Route::get('absence-attachments/{attachment}', [\App\Http\Controllers\Api\Student\AbsenceAttachmentController::class, 'show']);

        Route::post('attendance-sessions/check-in', [\App\Http\Controllers\Api\Student\AttendanceSessionCheckInController::class, 'store']);
    });
});

