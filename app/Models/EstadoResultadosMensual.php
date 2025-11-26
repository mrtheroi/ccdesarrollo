<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoResultadosMensual extends Model
{
    protected $table = 'vw_estado_resultados_mensual';
    public $timestamps = false;
    public $incrementing = false;

    protected $casts = [
        'mes' => 'date',
    ];
}
