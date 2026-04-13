<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    private const ROLES = ['teacher', 'admin', 'receptionist'];

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'student_id',
        'phone',
        'avatar_url',
        'roll_no',
        'course_id',
        'semester_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('worker_roles', function (Builder $builder) {
            $builder->whereIn('role', self::ROLES);
        });
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'worker_id');
    }
}
