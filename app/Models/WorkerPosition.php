<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerPosition extends Model
{
    protected $table = 'worker_position';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'timestamp',
        'is_active',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'is_active' => 'boolean',
    ];
}
