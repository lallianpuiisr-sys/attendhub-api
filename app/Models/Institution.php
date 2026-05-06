<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $fillable = [
        'name',
        'abbreviation',
        'description',
        'is_active',
        'longitude',
        'latitude',
        'state',
        'city',
        'country',
        'address',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'longitude' => 'float',
        'latitude' => 'float',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class)
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
