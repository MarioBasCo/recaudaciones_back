<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'identificacion',
        'apellidos',
        'nombres',
        'celular',
        'direccion',
        'correo',
        'activo'
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'model_id')->where('model', 'personas');
    }
}
