<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'school_year_id',
        'teacher_id',
        'class_name',
        'grade_level',
        'section',
        'description',
    ];

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'class_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'class_id');
    }

    public function absenceReports(): HasMany
    {
        return $this->hasMany(AbsenceReport::class, 'class_id');
    }
}