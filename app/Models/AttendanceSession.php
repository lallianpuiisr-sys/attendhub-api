<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $fillable = [
        'course_id',
        'semester_id',
        'subject_id',
        'period_id',
        'starts_at',
        'ends_at',
        'created_by',
        'status',
        'qr_token',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
