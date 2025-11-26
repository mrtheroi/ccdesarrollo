<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $table = 'gastos';
    protected $primaryKey = 'id_gasto';
    public $timestamps = false;

    protected $fillable = [
        'id_gasto',
        'fecha',
        'categoria',
        'descripcion',
        'monto',
        'proveedor',
        'forma_pago',
        'centro_costo',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];
}
