<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'complaint_type',
        'date_of_class',
        'subject_id',
        'period_id',
        'reason',
        'file_url',
    ];

    protected $casts = [
        'date_of_class' => 'date',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }
}
