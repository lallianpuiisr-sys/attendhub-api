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
        $periods = [
            ['name' => 'Period 1', 'start_time' => '09:30', 'end_time' => '10:30'],
            ['name' => 'Period 2', 'start_time' => '10:30', 'end_time' => '11:30'],
            ['name' => 'Period 3', 'start_time' => '11:30', 'end_time' => '12:30'],
            // Lunch: 12:30 - 13:00
            ['name' => 'Period 4', 'start_time' => '13:00', 'end_time' => '13:45'],
            ['name' => 'Period 5', 'start_time' => '13:45', 'end_time' => '14:30'],
            ['name' => 'Period 6', 'start_time' => '14:30', 'end_time' => '15:15'],
        ];

        $semesters = Semester::all();

        foreach ($semesters as $semester) {
            foreach ($periods as $index => $period) {
                Period::updateOrCreate(
                    [
                        'course_id' => $semester->course_id,
                        'semester_id' => $semester->id,
                        'name' => $period['name'],
                    ],
                    [
                        'start_time' => $period['start_time'],
                        'end_time' => $period['end_time'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
