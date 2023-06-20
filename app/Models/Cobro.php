<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobro extends Model
{
    use HasFactory;

    protected $fillable = ['idUsuario', 'idVehiculo', 'ticket_number', 'valor', 'fecha', 'hora', 'turno_id'];
}
