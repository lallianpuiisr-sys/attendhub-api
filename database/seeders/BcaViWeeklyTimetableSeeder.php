<?php

namespace Database\Seeders;

use App\Models\Period;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class BcaViWeeklyTimetableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courseId = 6;
        $semesterId = 36;

        $periodDefinitions = [
            ['name' => 'Period 1', 'start_time' => '09:30', 'end_time' => '10:30'],
            ['name' => 'Period 2', 'start_time' => '10:30', 'end_time' => '11:30'],
            ['name' => 'Period 3', 'start_time' => '11:30', 'end_time' => '12:30'],
            ['name' => 'Period 4', 'start_time' => '13:00', 'end_time' => '14:00'],
            ['name' => 'Period 5', 'start_time' => '14:00', 'end_time' => '15:00'],
        ];

        $periods = [];

        foreach ($periodDefinitions as $definition) {
            $period = Period::updateOrCreate(
                [
                    'course_id' => $courseId,
                    'semester_id' => $semesterId,
                    'name' => $definition['name'],
                ],
                [
                    'start_time' => $definition['start_time'],
                    'end_time' => $definition['end_time'],
                    'is_active' => true,
                ]
            );

            $periods[$definition['name']] = $period;
        }

        $entries = [
            ['day' => 'monday', 'period' => 'Period 2', 'name' => 'Mobile Computing', 'code' => 'BCA/6/EC/32 c', 'description' => 'Teacher: Chhungpuia | Room: SF Class Room 2'],
            ['day' => 'monday', 'period' => 'Period 3', 'name' => 'Management Information System', 'code' => 'BCA/6/EC/31 c', 'description' => 'Teacher: Hruaitea | Room: SF Class Room 2'],
            ['day' => 'tuesday', 'period' => 'Period 1', 'name' => 'Management Information System', 'code' => 'BCA/6/EC/31 c', 'description' => 'Teacher: Hruaitea | Room: SF Class Room 2'],
            ['day' => 'tuesday', 'period' => 'Period 2', 'name' => 'Library', 'code' => null, 'description' => 'Teacher: Tpi | Room: SF Class Room 2'],
            ['day' => 'tuesday', 'period' => 'Period 3', 'name' => 'Mobile Computing', 'code' => 'BCA/6/EC/32 c', 'description' => 'Teacher: Chhungpuia | Room: SF Class Room 2'],
            ['day' => 'wednesday', 'period' => 'Period 1', 'name' => 'Management Information System', 'code' => 'BCA/6/EC/31 c', 'description' => 'Teacher: Hruaitea | Room: SF Class Room 2'],
            ['day' => 'wednesday', 'period' => 'Period 2', 'name' => 'Major Project', 'code' => 'BCA/6/EC/33', 'description' => 'Teacher: Siama | Room: LF Lab 2'],
            ['day' => 'wednesday', 'period' => 'Period 3', 'name' => 'Major Project', 'code' => 'BCA/6/EC/33', 'description' => 'Teacher: Siama | Room: LF Lab 2'],
            ['day' => 'thursday', 'period' => 'Period 2', 'name' => 'Management Information System', 'code' => 'BCA/6/EC/31 c', 'description' => 'Teacher: Hruaitea | Room: SF Class Room 2'],
            ['day' => 'thursday', 'period' => 'Period 3', 'name' => 'Mobile Computing', 'code' => 'BCA/6/EC/32 c', 'description' => 'Teacher: Chhungpuia | Room: SF Class Room 2'],
            ['day' => 'friday', 'period' => 'Period 1', 'name' => 'Major Project', 'code' => 'BCA/6/EC/33', 'description' => 'Teacher: Siama | Room: LF Lab 2'],
            ['day' => 'friday', 'period' => 'Period 2', 'name' => 'Major Project', 'code' => 'BCA/6/EC/33', 'description' => 'Teacher: Siama | Room: LF Lab 2'],
            ['day' => 'friday', 'period' => 'Period 3', 'name' => 'Mobile Computing', 'code' => 'BCA/6/EC/32 c', 'description' => 'Teacher: Chhungpuia | Room: SF Class Room 2'],
        ];

        foreach ($entries as $entry) {
            Subject::updateOrCreate(
                [
                    'course_id' => $courseId,
                    'semester_id' => $semesterId,
                    'day_of_week' => $entry['day'],
                    'period_id' => $periods[$entry['period']]->id,
                ],
                [
                    'name' => $entry['name'],
                    'code' => $entry['code'],
                    'description' => $entry['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
