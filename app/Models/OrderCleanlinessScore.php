<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCleanlinessScore extends Model
{
    protected $table = 'order_cleanliness_scores';

    protected $fillable = [
        'employee_local_id',
        'date',
        'score',
        'created_by',
    ];

    protected $casts = [
        'score' => 'float',
        'date'  => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_local_id');
    }
}
