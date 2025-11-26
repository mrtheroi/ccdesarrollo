<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaVenta extends Model
{
    protected $table = 'facturas_venta';
    protected $primaryKey = 'id_factura';
    public $timestamps = false;

    protected $fillable = [
        'id_factura',
        'folio',
        'id_cliente',
        'fecha_emision',
        'fecha_vencimiento',
        'estatus',
        'subtotal',
        'iva',
        'total',
        'moneda',
        'forma_pago',
        'uso_cfdi',
    ];

    protected $casts = [
        'fecha_emision'     => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function detalles()
    {
        return $this->hasMany(FacturaDetalle::class, 'id_factura');
    }

    public function pagos()
    {
        return $this->hasMany(PagoCliente::class, 'id_factura');
    }
}
