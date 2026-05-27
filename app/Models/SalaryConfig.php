<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryConfig extends Model
{
    protected $fillable = [
        'employee_id', 'base_salary', 'extra_bonus', 'extra_bonus_reason',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'extra_bonus' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Sueldo total fijo = base + extra fijo por cargo
    public function totalFixed(): float
    {
        return floatval($this->base_salary) + floatval($this->extra_bonus);
    }

    // Costo por hora = sueldo_base / (26 días × horas_diarias)
    public function costPerHour(float $dailyHours): float
    {
        if ($dailyHours <= 0) return 0;
        return floatval($this->base_salary) / (26 * $dailyHours);
    }
}