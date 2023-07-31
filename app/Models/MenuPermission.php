<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Spatie\Permission\Models\Permission;

class MenuPermission extends Model
{
    use HasFactory;

    protected $table = 'menu_permission';

    // Relación con el modelo Menu
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    // Relación con el modelo Permission
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
}
