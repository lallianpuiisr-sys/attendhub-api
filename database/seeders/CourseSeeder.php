<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'title' => 'M.Tech',
                'description' => 'Computer Science & Engineering (Artificial Intelligence and Machine Learning)',
                'is_active' => true,
            ],
            [
                'title' => 'B.Tech',
                'description' => 'Computer Science & Engineering (Artificial Intelligence and Machine Learning)',
                'is_active' => true,
            ],
            [
                'title' => 'B.Tech',
                'description' => 'Computer Science & Engineering (Internet of Things)',
                'is_active' => true,
            ],
            [
                'title' => 'MCA',
                'description' => 'Master of Computer Application',
                'is_active' => true,
            ],
            [
                'title' => 'MSc (Electronics)',
                'description' => 'Master of Science in Electronics',

                'is_active' => true,
            ],
            [
                'title' => 'BCA',
                'description' => 'Bachelor of Computer Application',
                'is_active' => true,
            ],
            [
                'title' => 'DETE',
                'description' => 'Diploma in Electronics and Telecommunication Engineering',
                'is_active' => true,
            ],
            [
                'title' => 'DCSE',
                'description' => 'Diploma in Computer Science and Engineering',
                'is_active' => true,
            ],
            [
                'title' => 'NIELIT O-Level',
                'description' => 'NIELIT O-Level Course',
                'is_active' => true,
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}