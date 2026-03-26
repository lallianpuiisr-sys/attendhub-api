<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'complaint_type',
        'date_of_class',
        'user_id',
        'subject_id',
        'period_id',
        'reason',
        'file_url',
        'status',
    ];

    protected $casts = [
        'date_of_class' => 'date',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }
}
