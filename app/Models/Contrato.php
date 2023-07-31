<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory;

    protected $table    = 'contratos';

    protected $fillable = [
        'user_id',
        'cv',
        'referencias',
        'mesesContrato',
        'fechaInicio',
        'fechaFin',
        'estado'
    ];

    public function usuario() {
        return $this->belongsTo(User::class);
    }
}
