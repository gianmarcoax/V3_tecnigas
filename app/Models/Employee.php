<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'odoo_id', 'name', 'department', 'shift', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function salaryConfig()
    {
        return $this->hasOne(SalaryConfig::class);
    }

    public function goalAssignments()
    {
        return $this->hasMany(EmployeeGoalAssignment::class);
    }

    public function weeklyAssignment()
    {
        return $this->hasOne(EmployeeGoalAssignment::class)->where('period_type', 'weekly');
    }

    public function monthlyAssignment()
    {
        return $this->hasOne(EmployeeGoalAssignment::class)->where('period_type', 'monthly');
    }

    public function justifications()
    {
        return $this->hasMany(AttendanceJustification::class);
    }

    // Puestos excluidos de remuneración
    public static function excludedJobs(): array
    {
        return ['administrador', 'gerente', 'practicante'];
    }

    public function scopeRemunerables($query)
    {
        return $query->where('active', true);
        // El filtro por puesto se hace desde Odoo al sincronizar
    }
}