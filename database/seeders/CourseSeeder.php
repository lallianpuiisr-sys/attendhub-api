<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use Illuminate\Support\Str;

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
                'static_qr_token' => Str::random(40),
                'is_active' => true,
            ],
            [
                'title' => 'B.Tech',
                'description' => 'Computer Science & Engineering (Artificial Intelligence and Machine Learning)',
                'static_qr_token' => Str::random(40),
                'is_active' => true,
            ],
            [
                'title' => 'B.Tech',
                'description' => 'Computer Science & Engineering (Internet of Things)',
                'static_qr_token' => Str::random(40),
                'is_active' => true,
            ],
            [
                'title' => 'MCA',
                'description' => 'Master of Computer Application',
                'static_qr_token' => Str::random(40),
                'is_active' => true,
            ],
            [
                'title' => 'MSc (Electronics)',
                'description' => 'Master of Science in Electronics',
                'static_qr_token' => Str::random(40),
                'is_active' => true,
            ],
            [
                'title' => 'BCA',
                'description' => 'Bachelor of Computer Application',
                'static_qr_token' => Str::random(40),
                'is_active' => true,
            ],
            [
                'title' => 'DETE',
                'description' => 'Diploma in Electronics and Telecommunication Engineering',
                'static_qr_token' => Str::random(40),
                'is_active' => true,
            ],
            [
                'title' => 'DCSE',
                'description' => 'Diploma in Computer Science and Engineering',
                'static_qr_token' => Str::random(40),
                'is_active' => true,
            ],
            [
                'title' => 'NIELIT O-Level',
                'description' => 'NIELIT O-Level Course',
                'static_qr_token' => Str::random(40),
                'is_active' => true,
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
