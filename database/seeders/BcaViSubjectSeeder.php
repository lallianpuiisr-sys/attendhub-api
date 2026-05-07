<?php

namespace Database\Seeders;

use App\Models\Period;
use App\Models\Subject;
use App\Models\Worker;
use Illuminate\Database\Seeder;

class BcaViSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courseId = 6;
        $semesterId = 36;

        $periods = Period::query()
            ->where('course_id', $courseId)
            ->where('semester_id', $semesterId)
            ->get()
            ->keyBy('name');

        $entries = [
            ['day' => 'monday', 'period' => 'Period 2', 'name' => 'Mobile Computing', 'code' => 'BCA/6/EC/32 c', 'teacher' => 'Chhungpuia', 'description' => 'Teacher: Chhungpuia | Room: SF Class Room 2'],
            ['day' => 'monday', 'period' => 'Period 3', 'name' => 'Management Information System', 'code' => 'BCA/6/EC/31 c', 'teacher' => 'Hruaitea', 'description' => 'Teacher: Hruaitea | Room: SF Class Room 2'],
            ['day' => 'tuesday', 'period' => 'Period 1', 'name' => 'Management Information System', 'code' => 'BCA/6/EC/31 c', 'teacher' => 'Hruaitea', 'description' => 'Teacher: Hruaitea | Room: SF Class Room 2'],
            ['day' => 'tuesday', 'period' => 'Period 2', 'name' => 'Library', 'code' => null, 'teacher' => 'Tpi', 'description' => 'Teacher: Tpi | Room: SF Class Room 2'],
            ['day' => 'tuesday', 'period' => 'Period 3', 'name' => 'Mobile Computing', 'code' => 'BCA/6/EC/32 c', 'teacher' => 'Chhungpuia', 'description' => 'Teacher: Chhungpuia | Room: SF Class Room 2'],
            ['day' => 'wednesday', 'period' => 'Period 1', 'name' => 'Management Information System', 'code' => 'BCA/6/EC/31 c', 'teacher' => 'Hruaitea', 'description' => 'Teacher: Hruaitea | Room: SF Class Room 2'],
            ['day' => 'wednesday', 'period' => 'Period 2', 'name' => 'Major Project', 'code' => 'BCA/6/EC/33', 'teacher' => 'Siama', 'description' => 'Teacher: Siama | Room: LF Lab 2'],
            ['day' => 'wednesday', 'period' => 'Period 3', 'name' => 'Major Project', 'code' => 'BCA/6/EC/33', 'teacher' => 'Siama', 'description' => 'Teacher: Siama | Room: LF Lab 2'],
            ['day' => 'thursday', 'period' => 'Period 2', 'name' => 'Management Information System', 'code' => 'BCA/6/EC/31 c', 'teacher' => 'Hruaitea', 'description' => 'Teacher: Hruaitea | Room: SF Class Room 2'],
            ['day' => 'thursday', 'period' => 'Period 3', 'name' => 'Mobile Computing', 'code' => 'BCA/6/EC/32 c', 'teacher' => 'Chhungpuia', 'description' => 'Teacher: Chhungpuia | Room: SF Class Room 2'],
            ['day' => 'friday', 'period' => 'Period 1', 'name' => 'Major Project', 'code' => 'BCA/6/EC/33', 'teacher' => 'Siama', 'description' => 'Teacher: Siama | Room: LF Lab 2'],
            ['day' => 'friday', 'period' => 'Period 2', 'name' => 'Major Project', 'code' => 'BCA/6/EC/33', 'teacher' => 'Siama', 'description' => 'Teacher: Siama | Room: LF Lab 2'],
            ['day' => 'friday', 'period' => 'Period 3', 'name' => 'Mobile Computing', 'code' => 'BCA/6/EC/32 c', 'teacher' => 'Chhungpuia', 'description' => 'Teacher: Chhungpuia | Room: SF Class Room 2'],
        ];

        $workerIds = Worker::query()
            ->whereIn('name', collect($entries)->pluck('teacher')->unique())
            ->pluck('id', 'name');

        Subject::query()
            ->where('course_id', $courseId)
            ->where('semester_id', $semesterId)
            ->delete();

        foreach ($entries as $entry) {
            $period = $periods->get($entry['period']);

            if (!$period) {
                continue;
            }

            Subject::create([
                'course_id' => $courseId,
                'semester_id' => $semesterId,
                'day_of_week' => $entry['day'],
                'period_id' => $period->id,
                'name' => $entry['name'],
                'code' => $entry['code'],
                'description' => $entry['description'],
                'worker_id' => $workerIds[$entry['teacher']] ?? null,
                'is_active' => true,
            ]);
        }
    }
}
