<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traslado extends Model
{
    protected $table = 'traslados';

    protected $fillable = [
        'fecha','almacen_origen_id','almacen_origen_nombre',
        'almacen_destino_id','almacen_destino_nombre',
        'usuario','estado','observaciones','fecha_confirmacion',
        'odoo_picking_id',
    ];

    protected $casts = [
        'fecha'              => 'date',
        'fecha_confirmacion' => 'datetime',
        'odoo_picking_id'    => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(TrasladoItem::class);
    }
}