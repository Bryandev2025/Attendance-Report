<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', \App\Http\Controllers\Api\HealthController::class);

Route::middleware('x-api-key')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login'])->middleware('throttle:login');
        Route::post('/student-invites/accept', [\App\Http\Controllers\Api\Auth\StudentInviteAuthController::class, 'accept'])->middleware('throttle:login');
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
            Route::get('/me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
        });
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::get('/roles', [\App\Http\Controllers\Api\RoleController::class, 'index'])->middleware('role:admin');

        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
            Route::get('users-export', [\App\Http\Controllers\Api\Admin\UserImportExportController::class, 'export']);
            Route::post('users-import', [\App\Http\Controllers\Api\Admin\UserImportExportController::class, 'import']);
            Route::get('student-invites', [\App\Http\Controllers\Api\Admin\StudentInviteController::class, 'index']);
            Route::post('student-invites/bulk-create', [\App\Http\Controllers\Api\Admin\StudentInviteController::class, 'bulkCreate']);
            Route::post('student-invites/{invite}/resend', [\App\Http\Controllers\Api\Admin\StudentInviteController::class, 'resend']);
            Route::post('users/{user}/resend-password-setup', [\App\Http\Controllers\Api\Admin\StudentInviteController::class, 'resendForUser']);

        Route::apiResource('school-years', \App\Http\Controllers\Api\Admin\SchoolYearController::class);
        Route::post('school-years/{school_year}/set-active', [\App\Http\Controllers\Api\Admin\SchoolYearController::class, 'setActive']);
        Route::apiResource('classes', \App\Http\Controllers\Api\Admin\SchoolClassController::class);

        Route::get('audit-logs', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'index']);

        Route::get('absence-reports', [\App\Http\Controllers\Api\Admin\AbsenceReportController::class, 'index']);
        Route::get('absence-reports/{absence_report}', [\App\Http\Controllers\Api\Admin\AbsenceReportController::class, 'show']);
        Route::post('absence-reports/{absence_report}/approve', [\App\Http\Controllers\Api\Admin\AbsenceReportController::class, 'approve']);
        Route::post('absence-reports/{absence_report}/reject', [\App\Http\Controllers\Api\Admin\AbsenceReportController::class, 'reject']);
        Route::get('absence-reports-export', [\App\Http\Controllers\Api\Admin\AbsenceReportExportController::class, 'export']);

        Route::get('announcements', [\App\Http\Controllers\Api\Admin\AnnouncementController::class, 'index']);
        Route::delete('announcements/{announcement}', [\App\Http\Controllers\Api\Admin\AnnouncementController::class, 'destroy']);

        Route::get('announcement-comments', [\App\Http\Controllers\Api\Admin\AnnouncementCommentController::class, 'index']);
        Route::delete('announcement-comments/{comment}', [\App\Http\Controllers\Api\Admin\AnnouncementCommentController::class, 'destroy']);

        Route::get('programs', [\App\Http\Controllers\Api\Admin\ProgramController::class, 'index']);
        Route::apiResource('subjects', \App\Http\Controllers\Api\Admin\SubjectController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('class-subject-teachers', [\App\Http\Controllers\Api\Admin\ClassSubjectTeacherController::class, 'index']);
        Route::post('class-subject-teachers', [\App\Http\Controllers\Api\Admin\ClassSubjectTeacherController::class, 'store']);
        Route::delete('class-subject-teachers/{classSubjectTeacher}', [\App\Http\Controllers\Api\Admin\ClassSubjectTeacherController::class, 'destroy']);
        Route::get('timetable-slots', [\App\Http\Controllers\Api\Admin\TimetableSlotController::class, 'index']);
        Route::post('timetable-slots', [\App\Http\Controllers\Api\Admin\TimetableSlotController::class, 'store']);
        Route::patch('timetable-slots/{timetableSlot}', [\App\Http\Controllers\Api\Admin\TimetableSlotController::class, 'update']);
        Route::delete('timetable-slots/{timetableSlot}', [\App\Http\Controllers\Api\Admin\TimetableSlotController::class, 'destroy']);
        });

        Route::prefix('teacher')->middleware('role:teacher')->group(function () {
        Route::get('my-teaching', [\App\Http\Controllers\Api\Teacher\TeacherTeachingController::class, 'index']);
        Route::get('classes', [\App\Http\Controllers\Api\Teacher\MyClassesController::class, 'index']);
        Route::get('classes/{class}/roster', [\App\Http\Controllers\Api\Teacher\ClassRosterController::class, 'index']);
        Route::post('attendance/mark', [\App\Http\Controllers\Api\Teacher\AttendanceController::class, 'mark']);
        Route::get('attendance', [\App\Http\Controllers\Api\Teacher\AttendanceController::class, 'index']);
        Route::get('attendance-export', [\App\Http\Controllers\Api\Teacher\AttendanceExportController::class, 'export']);
        Route::get('dashboard-stats', [\App\Http\Controllers\Api\Teacher\DashboardStatsController::class, 'index']);
        Route::get('qr-card', [\App\Http\Controllers\Api\Teacher\TeacherQrController::class, 'show']);
        Route::get('qr-image', [\App\Http\Controllers\Api\Teacher\TeacherQrController::class, 'qrImage']);

        Route::post('attendance-sessions', [\App\Http\Controllers\Api\Teacher\AttendanceSessionController::class, 'store']);
        Route::post('attendance-sessions/{attendance_session}/close', [\App\Http\Controllers\Api\Teacher\AttendanceSessionController::class, 'close']);
        Route::get('attendance-sessions/{attendance_session}/qr', [\App\Http\Controllers\Api\Teacher\AttendanceSessionController::class, 'qr']);

        Route::get('absence-reports', [\App\Http\Controllers\Api\Teacher\AbsenceReportReviewController::class, 'index']);
        Route::post('absence-reports/{absence_report}/approve', [\App\Http\Controllers\Api\Teacher\AbsenceReportReviewController::class, 'approve']);
        Route::post('absence-reports/{absence_report}/reject', [\App\Http\Controllers\Api\Teacher\AbsenceReportReviewController::class, 'reject']);

        Route::get('announcements', [\App\Http\Controllers\Api\Teacher\AnnouncementController::class, 'index']);
        Route::post('announcements', [\App\Http\Controllers\Api\Teacher\AnnouncementController::class, 'store']);
        Route::post('announcements/{announcement}/publish', [\App\Http\Controllers\Api\Teacher\AnnouncementController::class, 'publish']);
        Route::delete('announcements/{announcement}', [\App\Http\Controllers\Api\Teacher\AnnouncementController::class, 'destroy']);

        Route::get('announcement-comments', [\App\Http\Controllers\Api\Teacher\AnnouncementCommentController::class, 'index']);
        Route::post('announcement-comments/{comment}/hide', [\App\Http\Controllers\Api\Teacher\AnnouncementCommentController::class, 'hide']);
        Route::post('announcement-comments/{comment}/unhide', [\App\Http\Controllers\Api\Teacher\AnnouncementCommentController::class, 'unhide']);
        Route::delete('announcement-comments/{comment}', [\App\Http\Controllers\Api\Teacher\AnnouncementCommentController::class, 'destroy']);
        });

        Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('schedule', [\App\Http\Controllers\Api\Student\StudentScheduleController::class, 'index']);
        Route::get('my-subjects', [\App\Http\Controllers\Api\Student\StudentSubjectsController::class, 'index']);
        Route::get('qr-card', [\App\Http\Controllers\Api\Student\StudentQrController::class, 'show']);
        Route::get('qr-image', [\App\Http\Controllers\Api\Student\StudentQrController::class, 'qrImage']);
        Route::get('attendance', [\App\Http\Controllers\Api\Student\MyAttendanceController::class, 'index']);
        Route::get('absence-reports', [\App\Http\Controllers\Api\Student\MyAbsenceReportsController::class, 'index']);
        Route::post('absence-reports', [\App\Http\Controllers\Api\Student\MyAbsenceReportsController::class, 'store']);
        Route::get('absence-attachments/{attachment}', [\App\Http\Controllers\Api\Student\AbsenceAttachmentController::class, 'show']);

        Route::post('attendance-sessions/check-in', [\App\Http\Controllers\Api\Student\AttendanceSessionCheckInController::class, 'store']);
        Route::get('dashboard-stats', [\App\Http\Controllers\Api\Student\DashboardStatsController::class, 'index']);

        Route::get('announcements', [\App\Http\Controllers\Api\Student\AnnouncementController::class, 'index']);
        Route::post('announcements/{announcement}/read', [\App\Http\Controllers\Api\Student\AnnouncementController::class, 'markRead']);

        Route::get('announcements/{announcement}/comments', [\App\Http\Controllers\Api\Student\AnnouncementCommentController::class, 'index']);
        Route::post('announcements/{announcement}/comments', [\App\Http\Controllers\Api\Student\AnnouncementCommentController::class, 'store']);
        Route::put('announcement-comments/{comment}', [\App\Http\Controllers\Api\Student\AnnouncementCommentController::class, 'update']);
        Route::delete('announcement-comments/{comment}', [\App\Http\Controllers\Api\Student\AnnouncementCommentController::class, 'destroy']);
        });
    });
});

