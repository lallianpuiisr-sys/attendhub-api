<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Semester;
use Illuminate\Support\Str;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {

            $title = strtolower($course->title);

            // decide number of semesters based on course title
            $totalSemesters = 0;

            if (str_contains($title, 'm.tech')) {
                $totalSemesters = 4;
            } elseif (str_contains($title, 'b.tech')) {
                $totalSemesters = 8;
            } elseif (str_contains($title, 'mca')) {
                $totalSemesters = 6;
            } elseif (str_contains($title, 'msc')) {
                $totalSemesters = 4;
            } elseif (str_contains($title, 'bca')) {
                $totalSemesters = 6;
            } elseif (str_contains($title, 'diploma')) {
                $totalSemesters = 6;
            } elseif (str_contains($title, 'o-level')) {
                $totalSemesters = 2;
            }

            // create semesters
            for ($i = 1; $i <= $totalSemesters; $i++) {
                Semester::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'semester_number' => $i,
                    ],
                    [
                        'title' => 'Semester ' . $i,
                        'description' => $course->title . ' - Semester ' . $i,
                        'static_qr_token' => Str::random(40),
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
