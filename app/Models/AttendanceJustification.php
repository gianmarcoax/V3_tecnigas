<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceJustification extends Model
{
    protected $fillable = [
        'employee_id', 'date', 'type', 'justified', 'reason', 'created_by',
    ];

    protected $casts = [
        'date'      => 'date',
        'justified' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}