<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'administrador']);
        $userRole = Role::create(['name' => 'usuario']);

        $permissions = Permission::all();

        $adminRole->syncPermissions($permissions);
        $exepcionesUser = [
            'listar usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
            'eliminar clientes'
        ];
        
        $per_user = $permissions->except($exepcionesUser);
        
        $userRole->syncPermissions($per_user);
    }
}
