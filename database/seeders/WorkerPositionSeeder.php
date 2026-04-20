<?php

namespace Database\Seeders;

use App\Models\WorkerPosition;
use Illuminate\Database\Seeder;

class WorkerPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workerPositions = [
            [
                'title' => 'faculty',
                'description' => 'Teaching staff responsible for delivering lectures, guiding students, and supporting academic activities.',
            ],
            [
                'title' => 'admin',
                'description' => 'Administrative staff responsible for managing institutional operations, records, approvals, and coordination tasks.',
            ],
            [
                'title' => 'receptionist',
                'description' => 'Front desk staff responsible for handling visitor support, student enquiries, calls, and basic office coordination.',
            ],
            [
                'title' => 'course coordinator',
                'description' => 'Academic coordinator responsible for course planning, timetable coordination, faculty communication, and student support.',
            ],
            [
                'title' => 'director',
                'description' => 'Senior institutional leader responsible for strategic direction, policy decisions, academic standards, and overall administration.',
            ],
            [
                'title' => 'academic head',
                'description' => 'Academic leader responsible for supervising curriculum delivery, academic performance, faculty coordination, and quality standards.',
            ],
            [
                'title' => 'head of department (hod)',
                'description' => 'Department head responsible for managing departmental faculty, subjects, academic planning, and student progress.',
            ],
        ];

        foreach ($workerPositions as $workerPosition) {
            WorkerPosition::updateOrCreate(
                ['title' => $workerPosition['title']],
                [
                    'description' => $workerPosition['description'],
                    'timestamp' => now(),
                    'is_active' => true,
                ]
            );
        }
    }
}
