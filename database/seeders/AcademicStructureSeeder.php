<?php

namespace Database\Seeders;

use App\Models\ClassSubjectTeacher;
use App\Models\Program;
use App\Models\ProgramCurriculum;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TimetableSlot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Programs (IT, BSEd Math, BSEd Social Studies), subjects, curriculum,
 * class programme linkage, teacher–subject assignments, and demo timetables.
 *
 * Subject names are inspired by typical SHS ICT strands and BSEd prospectuses (e.g. DepEd ICT, BSEd Math).
 */
class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $teacherRole = \App\Models\Role::query()->where('name', 'teacher')->first();
        if (! $teacherRole) {
            return;
        }

        $programs = [
            ['code' => 'it', 'name' => 'Information Technology', 'desc' => 'Four-year computing / ICT track (concepts from SHS ICT & programming strands).'],
            ['code' => 'ed_math', 'name' => 'Bachelor of Secondary Education (Mathematics)', 'desc' => 'Teacher-education program focused on mathematics pedagogy and content.'],
            ['code' => 'ed_social', 'name' => 'Bachelor of Secondary Education (Social Studies)', 'desc' => 'Teacher-education program for history, civics, geography, and related fields.'],
        ];
        foreach ($programs as $p) {
            Program::query()->firstOrCreate(
                ['code' => $p['code']],
                ['name' => $p['name'], 'duration_years' => 4, 'description' => $p['desc']]
            );
        }

        $progIt = Program::query()->where('code', 'it')->first();
        $progMath = Program::query()->where('code', 'ed_math')->first();
        $progSoc = Program::query()->where('code', 'ed_social')->first();

        foreach ([
            'IT-GEN-MATH' => 'General Mathematics',
            'IT-PROG1' => 'Programming Fundamentals',
            'IT-CSYS' => 'Computer Systems & Troubleshooting',
            'IT-COMM' => 'Oral Communication / English',
            'IT-PE1' => 'Physical Education & Health',
            'IT-STAT' => 'Statistics and Probability',
            'IT-WEB' => 'Web Technologies (HTML, CSS, JavaScript)',
            'IT-DB' => 'Database Systems',
            'IT-NET' => 'Networking Essentials',
            'IT-OOP' => 'Object-Oriented Programming',
            'IT-SE' => 'Software Engineering',
            'IT-SEC' => 'Cybersecurity Basics',
            'IT-CAP' => 'Capstone Project',
            'IT-MOB' => 'Mobile Application Development',
            'EDM-ALG' => 'College Algebra',
            'EDM-CALC-PREP' => 'Mathematics in the Modern World',
            'EDM-TP' => 'The Teaching Profession',
            'EDM-MR' => 'Logic and Mathematical Reasoning',
            'EDM-TRIG' => 'Trigonometry',
            'EDM-STAT' => 'Elementary Statistics and Probability',
            'EDM-LA' => 'Linear Algebra',
            'EDM-CALC1' => 'Calculus I with Analytic Geometry',
            'EDM-EDTEC' => 'Technology for Teaching and Learning',
            'EDM-CALC2' => 'Calculus II',
            'EDM-ASSESS' => 'Assessment of Student Learning',
            'EDS-WH' => 'World History',
            'EDS-PH' => 'Readings in Philippine History',
            'EDS-SOC' => 'Introduction to Sociology',
            'EDS-ECO' => 'Basic Economics',
            'EDS-POL' => 'Political Science & Governance',
            'EDS-GEO' => 'Geography, Society, and Culture',
            'EDS-RESEARCH' => 'Research in Social Studies',
            'EDS-CURR' => 'The Teacher and the School Curriculum',
            'EDM-PRAC' => 'Practice Teaching / Field Study',
            'EDS-CAP' => 'Social Studies Practicum & Immersion',
        ] as $code => $name) {
            Subject::query()->firstOrCreate(['code' => $code], ['name' => $name]);
        }

        $sid = fn (string $code) => Subject::query()->where('code', $code)->value('id');

        $attachCurriculum = function (int $programId, int $year, array $codes) {
            foreach ($codes as $order => $code) {
                $subId = Subject::query()->where('code', $code)->value('id');
                if (! $subId) {
                    continue;
                }
                ProgramCurriculum::query()->updateOrCreate(
                    ['program_id' => $programId, 'year_level' => $year, 'subject_id' => $subId],
                    ['sort_order' => $order]
                );
            }
        };

        if ($progIt) {
            $attachCurriculum($progIt->id, 1, ['IT-GEN-MATH', 'IT-PROG1', 'IT-CSYS', 'IT-COMM', 'IT-PE1']);
            $attachCurriculum($progIt->id, 2, ['IT-STAT', 'IT-WEB', 'IT-DB', 'IT-NET', 'IT-COMM']);
            $attachCurriculum($progIt->id, 3, ['IT-OOP', 'IT-SE', 'IT-SEC', 'IT-WEB', 'IT-DB']);
            $attachCurriculum($progIt->id, 4, ['IT-CAP', 'IT-MOB', 'IT-OOP', 'IT-SE', 'IT-SEC']);
        }
        if ($progMath) {
            $attachCurriculum($progMath->id, 1, ['EDM-ALG', 'EDM-CALC-PREP', 'EDM-TP', 'EDM-MR', 'IT-COMM']);
            $attachCurriculum($progMath->id, 2, ['EDM-TRIG', 'EDM-STAT', 'EDM-LA', 'EDM-CALC1', 'EDM-EDTEC']);
            $attachCurriculum($progMath->id, 3, ['EDM-CALC2', 'EDM-STAT', 'EDM-LA', 'EDM-ASSESS', 'EDM-ALG']);
            $attachCurriculum($progMath->id, 4, ['EDM-CALC2', 'EDM-ASSESS', 'EDM-EDTEC', 'EDM-PRAC', 'EDM-MR']);
        }
        if ($progSoc) {
            $attachCurriculum($progSoc->id, 1, ['EDS-WH', 'EDS-PH', 'EDS-SOC', 'EDM-TP', 'IT-COMM']);
            $attachCurriculum($progSoc->id, 2, ['EDS-ECO', 'EDS-POL', 'EDS-GEO', 'EDS-WH', 'EDM-EDTEC']);
            $attachCurriculum($progSoc->id, 3, ['EDS-RESEARCH', 'EDS-CURR', 'EDS-PH', 'EDS-SOC', 'EDS-POL']);
            $attachCurriculum($progSoc->id, 4, ['EDS-RESEARCH', 'EDS-CURR', 'EDS-GEO', 'EDS-ECO', 'EDS-CAP']);
        }

        $year = SchoolYear::query()->where('name', 'SY 2025-2026')->first();
        $mainTeacher = User::query()->where('email', 'teacher@example.com')->first();

        if (! $year || ! $mainTeacher || ! $progIt) {
            return;
        }

        $mathTeacher = User::query()->firstOrCreate(
            ['email' => 'teacher.math@example.com'],
            [
                'role_id' => $teacherRole->id,
                'first_name' => 'Maria',
                'last_name' => 'Santos (Math)',
                'password' => Hash::make('password'),
                'status' => User::STATUS_ACTIVE,
            ]
        );
        TeacherProfile::query()->firstOrCreate(
            ['user_id' => $mathTeacher->id],
            ['employee_id' => 'T-MATH-01', 'contact_number' => '0000000001', 'address' => 'School']
        );

        $socTeacher = User::query()->firstOrCreate(
            ['email' => 'teacher.social@example.com'],
            [
                'role_id' => $teacherRole->id,
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz (Social Studies)',
                'password' => Hash::make('password'),
                'status' => User::STATUS_ACTIVE,
            ]
        );
        TeacherProfile::query()->firstOrCreate(
            ['user_id' => $socTeacher->id],
            ['employee_id' => 'T-SOC-01', 'contact_number' => '0000000002', 'address' => 'School']
        );

        $itClass = SchoolClass::query()
            ->where('school_year_id', $year->id)
            ->where('class_name', 'Advisory')
            ->first();

        if ($itClass) {
            $itClass->update([
                'class_name' => 'IT — Year 1 — A',
                'program_id' => $progIt->id,
                'year_level' => 1,
                'grade_level' => 'Year 1',
                'section' => 'A',
                'description' => 'Information Technology cohort (seeded).',
            ]);
        }

        if ($itClass && $progMath) {
            SchoolClass::query()->firstOrCreate(
                [
                    'school_year_id' => $year->id,
                    'class_name' => 'BSEd Math — Year 1 — A',
                ],
                [
                    'program_id' => $progMath->id,
                    'year_level' => 1,
                    'teacher_id' => $mathTeacher->id,
                    'grade_level' => 'Year 1',
                    'section' => 'A',
                    'description' => 'Mathematics education cohort (seeded).',
                ]
            );
        }

        if ($progSoc) {
            SchoolClass::query()->firstOrCreate(
                [
                    'school_year_id' => $year->id,
                    'class_name' => 'BSEd Social Studies — Year 1 — A',
                ],
                [
                    'program_id' => $progSoc->id,
                    'year_level' => 1,
                    'teacher_id' => $socTeacher->id,
                    'grade_level' => 'Year 1',
                    'section' => 'A',
                    'description' => 'Social studies education cohort (seeded).',
                ]
            );
        }

        $itClass = SchoolClass::query()
            ->where('school_year_id', $year->id)
            ->where('class_name', 'IT — Year 1 — A')
            ->first();

        if (! $itClass) {
            return;
        }

        TimetableSlot::query()->where('class_id', $itClass->id)->delete();
        ClassSubjectTeacher::query()->where('class_id', $itClass->id)->delete();

        $assign = function (string $subjectCode, int $teacherId) use ($itClass) {
            ClassSubjectTeacher::query()->create([
                'class_id' => $itClass->id,
                'subject_id' => Subject::query()->where('code', $subjectCode)->value('id'),
                'teacher_id' => $teacherId,
            ]);
        };

        $assign('IT-PROG1', $mainTeacher->id);
        $assign('IT-CSYS', $mainTeacher->id);
        $assign('IT-GEN-MATH', $mainTeacher->id);
        $assign('IT-COMM', $socTeacher->id);
        $assign('IT-PE1', $socTeacher->id);

        $slots = [
            [1, '08:00', '09:00', 'IT-GEN-MATH', $mainTeacher->id, 'Room 101'],
            [1, '09:15', '10:15', 'IT-PROG1', $mainTeacher->id, 'Lab A'],
            [1, '10:30', '11:30', 'IT-CSYS', $mainTeacher->id, 'Lab A'],
            [2, '08:00', '09:00', 'IT-COMM', $socTeacher->id, 'Room 102'],
            [2, '09:15', '10:15', 'IT-PROG1', $mainTeacher->id, 'Lab A'],
            [2, '10:30', '11:30', 'IT-GEN-MATH', $mainTeacher->id, 'Room 101'],
            [3, '08:00', '09:00', 'IT-CSYS', $mainTeacher->id, 'Lab A'],
            [3, '09:15', '10:15', 'IT-PE1', $socTeacher->id, 'Gym'],
            [3, '10:30', '11:30', 'IT-PROG1', $mainTeacher->id, 'Lab A'],
            [4, '08:00', '09:00', 'IT-GEN-MATH', $mainTeacher->id, 'Room 101'],
            [4, '09:15', '10:15', 'IT-CSYS', $mainTeacher->id, 'Lab A'],
            [4, '10:30', '11:30', 'IT-COMM', $socTeacher->id, 'Room 102'],
            [5, '08:00', '09:30', 'IT-PROG1', $mainTeacher->id, 'Lab A'],
            [5, '09:45', '11:15', 'IT-PE1', $socTeacher->id, 'Field'],
        ];

        foreach ($slots as [$dow, $start, $end, $subCode, $tid, $room]) {
            TimetableSlot::query()->create([
                'class_id' => $itClass->id,
                'day_of_week' => $dow,
                'start_time' => $start,
                'end_time' => $end,
                'subject_id' => $sid($subCode),
                'teacher_id' => $tid,
                'room' => $room,
            ]);
        }

        // Simple timetable for BSEd Math class (same year)
        $mathClass = SchoolClass::query()
            ->where('school_year_id', $year->id)
            ->where('class_name', 'BSEd Math — Year 1 — A')
            ->first();
        if ($mathClass) {
            TimetableSlot::query()->where('class_id', $mathClass->id)->delete();
            ClassSubjectTeacher::query()->where('class_id', $mathClass->id)->delete();
            foreach (['EDM-ALG', 'EDM-CALC-PREP', 'EDM-TP'] as $idx => $code) {
                ClassSubjectTeacher::query()->create([
                    'class_id' => $mathClass->id,
                    'subject_id' => $sid($code),
                    'teacher_id' => $mathTeacher->id,
                ]);
            }
            ClassSubjectTeacher::query()->create([
                'class_id' => $mathClass->id,
                'subject_id' => $sid('EDM-MR'),
                'teacher_id' => $socTeacher->id,
            ]);
            ClassSubjectTeacher::query()->create([
                'class_id' => $mathClass->id,
                'subject_id' => $sid('IT-COMM'),
                'teacher_id' => $socTeacher->id,
            ]);

            TimetableSlot::query()->create([
                'class_id' => $mathClass->id,
                'day_of_week' => 1,
                'start_time' => '13:00',
                'end_time' => '14:30',
                'subject_id' => $sid('EDM-ALG'),
                'teacher_id' => $mathTeacher->id,
                'room' => 'Math Lab',
            ]);
            TimetableSlot::query()->create([
                'class_id' => $mathClass->id,
                'day_of_week' => 3,
                'start_time' => '13:00',
                'end_time' => '14:30',
                'subject_id' => $sid('EDM-MR'),
                'teacher_id' => $socTeacher->id,
                'room' => 'Room 201',
            ]);
        }
    }
}
