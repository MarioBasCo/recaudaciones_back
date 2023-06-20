<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Local extends Model
{
    protected $table = 'locales';
    protected $fillable = ['codigo', 'detalle', 'disponible', 'estado'];

    // Relación con la tabla "arriendos"
    public function arriendos()
    {
        return $this->hasMany(Arriendo::class);
    }
}
