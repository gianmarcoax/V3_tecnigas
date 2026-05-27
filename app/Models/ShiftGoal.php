<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftGoal extends Model
{
    protected $fillable = [
        'shift', 'period_type', 'sales_goal', 'group_bonus',
    ];

    protected $casts = [
        'sales_goal'  => 'decimal:2',
        'group_bonus' => 'decimal:2',
    ];

    // Helpers rápidos
    public function scopeWeekly($query)
    {
        return $query->where('period_type', 'weekly');
    }

    public function scopeMonthly($query)
    {
        return $query->where('period_type', 'monthly');
    }
}