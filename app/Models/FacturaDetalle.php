<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaDetalle extends Model
{
    protected $table = 'facturas_detalle';
    public $timestamps = false;
    public $incrementing = false; // no tenemos id_detalle

    protected $fillable = [
        'id_factura',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'importe',
    ];

    public function factura()
    {
        return $this->belongsTo(FacturaVenta::class, 'id_factura');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
