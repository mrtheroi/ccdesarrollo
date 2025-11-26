<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'nombre_cliente',
        'rfc',
        'segmento',
        'estado',
        'ciudad',
        'limite_credito',
        'dias_credito',
    ];

    public function facturas()
    {
        return $this->hasMany(FacturaVenta::class, 'id_cliente');
    }
}
