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
        'static_qr_token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // belongs to course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
