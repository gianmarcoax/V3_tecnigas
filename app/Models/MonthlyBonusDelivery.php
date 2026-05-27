<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyBonusDelivery extends Model
{
    protected $fillable = [
        'employee_local_id', 'week_start',
        'bonus_amount', 'delivered', 'delivered_at', 'delivered_by',
    ];

    protected $casts = [
        'week_start'   => 'date',
        'delivered'    => 'boolean',
        'delivered_at' => 'datetime',
        'bonus_amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_local_id');
    }

    public function deliveredByUser()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }
}
