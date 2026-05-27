<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrasladoItem extends Model
{
    protected $table = 'traslado_items';

    protected $fillable = [
        'traslado_id','producto_id','producto_nombre','cantidad','unidad',
    ];

    protected $casts = ['cantidad' => 'float'];

    public function traslado()
    {
        return $this->belongsTo(Traslado::class);
    }
}