<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Institution;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institution = Institution::updateOrCreate(
            [
                'name' => 'National Institute of Electronics and Information Technology, Aizawl',
            ],
            [
                'abbreviation' => 'NIELIT Aizawl',
                'description' => 'NIELIT Aizawl, formerly DOEACC Centre Aizawl, is located at Industrial Estate, Zuangtui, Aizawl and focuses on Information, Electronics and Communication Technology education and training.',
                'is_active' => true,
                'longitude' => 92.7404270,
                'latitude' => 23.7538364,
                'state' => 'Mizoram',
                'city' => 'Aizawl',
                'country' => 'India',
                'address' => 'Industrial Estate, Zuangtui, Aizawl, Mizoram - 796017',
            ],
        );

        $courseIds = Course::query()->pluck('id')->all();

        if ($courseIds) {
            $institution->courses()->syncWithoutDetaching(
                collect($courseIds)
                    ->mapWithKeys(fn (int $courseId) => [
                        $courseId => ['is_active' => true],
                    ])
                    ->all(),
            );
        }
    }
}
