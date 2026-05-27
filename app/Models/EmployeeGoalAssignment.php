<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeGoalAssignment extends Model
{
    protected $fillable = [
        'employee_id', 'period_type', 'area',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Retorna los tiers individuales que aplican a este empleado según su asignación
    public function tiers()
    {
        return GoalTier::where('owner_type', 'individual')
            ->where('area', $this->area)
            ->where('period_type', $this->period_type)
            ->orderByDesc('sales_goal')
            ->get();
    }
}