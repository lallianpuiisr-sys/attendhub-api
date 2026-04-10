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
        'geofence_latitude',
        'geofence_longitude',
        'geofence_radius_meters',
        'is_active',
    ];

    protected $casts = [
        'geofence_latitude' => 'float',
        'geofence_longitude' => 'float',
        'geofence_radius_meters' => 'integer',
        'is_active' => 'boolean',
    ];

    // belongs to course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
