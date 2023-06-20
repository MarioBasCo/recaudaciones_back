<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    public function modelo()
    {
        return $this->morphTo('modelo', 'model', 'model_id');
    }

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'idCliente');
    }
}
