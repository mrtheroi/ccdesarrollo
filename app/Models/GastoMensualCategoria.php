<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GastoMensualCategoria extends Model
{
    protected $table = 'vw_gastos_mensuales_categoria';
    public $timestamps = false;
    public $incrementing = false;

    protected $casts = [
        'mes' => 'date',
    ];
}
