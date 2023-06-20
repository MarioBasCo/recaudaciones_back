<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketNumber extends Model
{
    use HasFactory;

    protected $fillable = ['year', 'number', 'disabled'];
    public $timestamps = false;
}
