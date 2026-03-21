<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title',
        'description',
        'static_qr_token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // A course has many semesters
    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }

    // A course has many users (students)
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
