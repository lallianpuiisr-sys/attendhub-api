<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffDetail extends Model
{
    protected $fillable = [
        'user_id',
        'position',
        'is_admin',
        'is_teacher',
        'is_receptionist',
        'is_approved',
        'phone_1',
        'phone_2',
        'approved_at',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_teacher' => 'boolean',
        'is_receptionist' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
