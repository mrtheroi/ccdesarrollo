<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentasClienteMensual extends Model
{
    protected $table = 'vw_ventas_cliente_mensual';
    public $timestamps = false;
    public $incrementing = false;

    protected $casts = [
        'mes' => 'date',
    ];
}
