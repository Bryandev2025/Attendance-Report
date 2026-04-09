<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Subject::query()->orderBy('name')->get(),
        ]);
    }
}
