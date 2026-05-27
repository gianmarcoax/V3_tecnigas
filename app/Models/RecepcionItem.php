<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecepcionItem extends Model
{
    protected $table = 'recepcion_items';

    protected $fillable = [
        'recepcion_id',
        'producto_id',
        'producto_nombre',
        'default_code',
        'cantidad',
        'tickets',
        'costo',
        'list_price',
        'subtotal',
    ];

    protected $casts = [
        'cantidad'   => 'float',
        'tickets'    => 'integer',
        'costo'      => 'float',
        'list_price' => 'float',
        'subtotal'   => 'float',
    ];

    public function recepcion()
    {
        return $this->belongsTo(Recepcion::class);
    }
}
