<?php

namespace Database\Seeders;

use App\Models\Period;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courseId = 6;
        $semesterId = 34;

        $morningSubjects = [
            'Mobile Computing',
            'Major Project',
            'Management Information System',
        ];

        // Take the first three periods (morning) for this course+semester
        $periods = Period::query()
            ->where('course_id', $courseId)
            ->where('semester_id', $semesterId)
            ->orderBy('start_time')
            ->take(3)
            ->get();

        foreach ($morningSubjects as $index => $name) {
            $periodId = $periods[$index]->id ?? null;

            Subject::updateOrCreate(
                [
                    'course_id' => $courseId,
                    'semester_id' => $semesterId,
                    'period_id' => $periodId,
                    'name' => $name,
                ],
                [
                    'is_active' => true,
                ]
            );
        }
    }
}
