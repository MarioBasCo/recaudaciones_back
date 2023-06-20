<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidad extends Model
{
    use HasFactory;
    protected $table = 'entidades';

    protected $fillable = [
        'identificacion',
        'razon_social',
        'celular',
        'direccion',
        'correo',
        'activo'
    ];

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'model_id')->where('model', 'entidades');
    }
}
