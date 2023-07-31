<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'arriendo_id',
        'fechaPago',
        'user_id',
        'monto',
        'observacion',
    ];

    public function arriendo()
    {
        return $this->belongsTo(Arriendo::class);
    }
}
