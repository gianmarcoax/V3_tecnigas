<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TardinessConfig extends Model
{
    protected $fillable = [
        'threshold_minutes', 'deduction_amount',
    ];

    protected $casts = [
        'threshold_minutes' => 'integer',
        'deduction_amount'  => 'decimal:2',
    ];

    // Obtener la config global (siempre hay una sola fila)
    public static function current(): self
    {
        return self::firstOrCreate([], [
            'threshold_minutes' => 10,
            'deduction_amount'  => null,
        ]);
    }
    protected $table = 'tardiness_config';
}