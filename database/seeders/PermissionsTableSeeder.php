<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionNames = [
            'listar usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
            'listar clientes',
            'crear clientes',
            'editar clientes',
            'eliminar clientes',
            'cobrar garita'
        ];

        foreach ($permissionNames as $name) {
            Permission::create(['name' => $name]);
        }
    }
}
