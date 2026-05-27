<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalTier extends Model
{
    protected $fillable = [
        'employee_local_id',
        'owner_type', 'area', 'period_type', 'shift',
        'label', 'sales_goal', 'bonus_amount', 'bonus_pct', 'sort_order',
    ];

    protected $casts = [
        'sales_goal'        => 'decimal:2',
        'bonus_amount'      => 'decimal:2',
        'bonus_pct'         => 'decimal:4',
        'sort_order'        => 'integer',
        'employee_local_id' => 'integer',
    ];

    /** Calcula el bono según ventas: porcentaje si bonus_pct está definido, monto fijo si no */
    public function calculateBonus(float $ventas): float
    {
        if ($this->bonus_pct !== null && floatval($this->bonus_pct) > 0) {
            return round($ventas * floatval($this->bonus_pct) / 100, 2);
        }
        return floatval($this->bonus_amount);
    }

    // ── Relaciones ────────────────────────────────────────────────────
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_local_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────
    public function scopeGroup($q)            { return $q->where('owner_type', 'group'); }
    public function scopeIndividual($q)       { return $q->where('owner_type', 'individual'); }
    public function scopeWeekly($q)           { return $q->where('period_type', 'weekly'); }
    public function scopeMonthly($q)          { return $q->where('period_type', 'monthly'); }
    public function scopeVentas($q)           { return $q->where('area', 'ventas'); }
    public function scopeServicio($q)         { return $q->where('area', 'servicio_tecnico'); }
    public function scopeManana($q)           { return $q->where('shift', 'manana'); }
    public function scopeTarde($q)            { return $q->where('shift', 'tarde'); }
    public function scopeForEmployee($q, int $localId) {
        return $q->where('employee_local_id', $localId);
    }
    /** Tiers globales (sin empleado asignado) */
    public function scopeGlobal($q)           { return $q->whereNull('employee_local_id'); }

    // ── Helpers ───────────────────────────────────────────────────────

    /** Dado un monto, retorna el tier más alto alcanzado de una colección */
    public static function reached(float $amount, \Illuminate\Support\Collection $tiers): ?self
    {
        return $tiers
            ->where('sales_goal', '<=', $amount)
            ->sortByDesc('sales_goal')
            ->first();
    }
}