<?php

namespace App\Models;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    
    public function menus()
    {
        return $this->hasMany(MenuPermission::class, 'permission_id');
    }
}