<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoCliente extends Model
{
    protected $table = 'pagos_clientes';
    protected $primaryKey = 'id_pago';
    public $timestamps = false;

    protected $fillable = [
        'id_pago',
        'id_factura',
        'fecha_pago',
        'monto_pagado',
        'medio_pago',
        'referencia',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
    ];

    public function factura()
    {
        return $this->belongsTo(FacturaVenta::class, 'id_factura');
    }
}
