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
        $operatorRole = Role::create(['name' => 'operador']);

        $permissionsAdmin = Permission::all();

        $adminRole->syncPermissions($permissionsAdmin);
        
        $operadorPermissions = [
            'listar_clientes', 'crear clientes', 'editar_clientes', 'eliminar_clientes',
            'cobrar_garita',
            'listar_arriendos', 'registrar_arriendos', 'eliminar_arriendos', 'listar_pagos', 'registrar_pago', 'notificar_pago',
        ];
        $permissionsOperator = Permission::whereIn('name', $operadorPermissions)->get();
        $operatorRole->syncPermissions($permissionsOperator);        
    }
}
