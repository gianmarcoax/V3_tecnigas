<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeGoal extends Model
{
    protected $fillable = [
        'employee_id', 'period_type', 'sales_goal', 'individual_bonus',
    ];

    protected $casts = [
        'sales_goal'       => 'decimal:2',
        'individual_bonus' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}