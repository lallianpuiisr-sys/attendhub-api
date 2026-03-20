<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'semester_number',
        'is_active',
    ];

    // belongs to course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}