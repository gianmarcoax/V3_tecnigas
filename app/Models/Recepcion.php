<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recepcion extends Model
{
    protected $table = 'recepciones';

    protected $fillable = [
        'fecha',
        'proveedor_id',
        'proveedor_nombre',
        'documento',
        'usuario',
        'subtotal',
        'igv',
        'total',
        'observaciones',
        'location_dest_id',
        'odoo_picking_id',
    ];

    protected $casts = [
        'fecha'           => 'date',
        'subtotal'        => 'float',
        'igv'             => 'float',
        'total'           => 'float',
        'location_dest_id'=> 'integer',
        'odoo_picking_id' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(RecepcionItem::class);
    }
}