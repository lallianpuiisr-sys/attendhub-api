<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title',
        'description',
        'is_active',
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