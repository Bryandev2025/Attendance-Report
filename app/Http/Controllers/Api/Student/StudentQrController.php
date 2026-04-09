<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentQrController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $profile = $user->studentProfile()->with('schoolClass.program')->first();

        if (! $profile) {
            return response()->json(['message' => 'Student profile not found.'], 404);
        }

        return response()->json([
            'data' => [
                'student_number' => $profile->student_number,
                'full_name' => $user->full_name,
                'program_name' => $profile->schoolClass?->program?->name,
                'year_level' => $profile->schoolClass?->year_level,
                'class_name' => $profile->schoolClass?->class_name,
                'section' => $profile->schoolClass?->section,
                'qr_payload' => 'SARS_STUDENT:'.$profile->qr_public_token,
            ],
        ]);
    }
}
