<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'inicio', 'fin', 'cerrado', 'recaudado', 'observacion', 'estado'];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
