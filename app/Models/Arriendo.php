<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arriendo extends Model
{
    protected $table = 'arriendos';
    protected $fillable = ['local_id', 'cliente_id', 'valorArriendo', 'fecha', 'Meses', 'estado'];

    // Relación con la tabla "locales"
    public function local()
    {
        return $this->belongsTo(Local::class);
    }

    // Relación con la tabla "personas"
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    // Relación con la tabla "pagos"
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
