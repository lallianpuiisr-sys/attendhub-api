<?php

namespace Database\Seeders;

use App\Models\Period;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPeriods = [
            ['name' => 'Period 1', 'start_time' => '09:30', 'end_time' => '10:30'],
            ['name' => 'Period 2', 'start_time' => '10:30', 'end_time' => '11:30'],
            ['name' => 'Period 3', 'start_time' => '11:30', 'end_time' => '12:30'],
            ['name' => 'Period 4', 'start_time' => '13:00', 'end_time' => '13:45'],
            ['name' => 'Period 5', 'start_time' => '13:45', 'end_time' => '14:30'],
            ['name' => 'Period 6', 'start_time' => '14:30', 'end_time' => '15:15'],
        ];

        $customPeriodsBySemester = [
            36 => [
                ['name' => 'Period 1', 'start_time' => '09:30', 'end_time' => '10:30'],
                ['name' => 'Period 2', 'start_time' => '10:30', 'end_time' => '11:30'],
                ['name' => 'Period 3', 'start_time' => '11:30', 'end_time' => '12:30'],
            ],
        ];

        $semesters = Semester::query()
            ->select(['id', 'course_id'])
            ->orderBy('id')
            ->get();

        foreach ($semesters as $semester) {
            $periods = $customPeriodsBySemester[$semester->id] ?? $defaultPeriods;
            $periodNames = collect($periods)->pluck('name')->all();

            Period::query()
                ->where('course_id', $semester->course_id)
                ->where('semester_id', $semester->id)
                ->whereNotIn('name', $periodNames)
                ->delete();

            foreach ($periods as $period) {
                Period::updateOrCreate(
                    [
                        'course_id' => $semester->course_id,
                        'semester_id' => $semester->id,
                        'name' => $period['name'],
                    ],
                    [
                        'start_time' => $period['start_time'],
                        'end_time' => $period['end_time'],
                        'scan_window_minutes' => $period['scan_window_minutes'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
