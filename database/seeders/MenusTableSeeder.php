<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\MenuPermission;
use Spatie\Permission\Models\Permission;

class MenusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definir los menús y sus permisos en un solo arreglo
        $menusWithPermissions = [
            [
                'title' => 'Dashboard',
                'url' => '/dashboard',
                'icon' => 'pie_chart',
                'submenus' => [],
                'permissions' => ['ver_info_resumida', 'ver_recaudacion_mensual', 'ver_recaudacion_tipo_vehiculos'],
            ],
            [
                'title' => 'Mantenedores',
                'url' => null,
                'icon' => 'dashboard',
                'submenus' => [
                    [
                        'title' => 'Clientes',
                        'url' => '/maintainers/clients',
                        'icon' => 'fiber_manual_record',
                        'permissions' => ['listar_clientes', 'crear clientes', 'editar_clientes', 'eliminar_clientes'],
                    ],
                    [
                        'title' => 'Usuarios',
                        'url' => '/maintainers/users',
                        'icon' => 'fiber_manual_record',
                        'permissions' => ['listar_usuarios', 'crear_usuarios', 'editar_usuarios', 'eliminar_usuarios'],
                    ],
                    [
                        'title' => 'Roles',
                        'url' => '/maintainers/roles',
                        'icon' => 'fiber_manual_record',
                        'permissions' => ['listar_roles', 'crear_roles', 'editar_roles', 'eliminar_roles'],
                    ],
                    [
                        'title' => 'Contratos',
                        'url' => '/maintainers/contracts',
                        'icon' => 'fiber_manual_record',
                        'permissions' => ['listar_contractos', 'crear_contractos',  'eliminar_contractos'],
                    ],
                    [
                        'title' => 'Parámetros',
                        'url' => '/maintainers/params',
                        'icon' => 'fiber_manual_record',
                        'permissions' => ['listar_tipo_vehiculo', 'crear_tipo_vehiculo', 'editar_tipo_vehiculo',   'eliminar_tipo_vehiculo'],
                    ],
                ],
            ],
            [
                'title' => 'Procesos',
                'url' => null,
                'icon' => 'desktop_windows',
                'submenus' => [
                    [
                        'title' => 'Cobros Garita',
                        'url' => '/processes/turns',
                        'icon' => 'fiber_manual_record',
                        'permissions' => [],
                    ],
                    [
                        'title' => 'Cierre Turno',
                        'url' => '/processes/close-turns',
                        'icon' => 'fiber_manual_record',
                        'permissions' => [],
                    ],
                ],
                'permissions' => ['cobrar_garita'],
            ],
            [
                'title' => 'Locales',
                'url' => '/rent',
                'icon' => 'local_convenience_store',
                'submenus' => [],
                'permissions' => ['listar_arriendos', 'registrar_arriendos', 'eliminar_arriendos', 'listar_pagos', 'registrar_pago', 'notificar_pago'],
            ],
            [
                'title' => 'Reportes',
                'url' => null,
                'icon' => 'local_convenience_store',
                'submenus' => [
                    [
                        'title' => 'Cobros Garita',
                        'url' => '/reports/charges',
                        'icon' => 'fiber_manual_record',
                        'permissions' => ['visualizar_graficos_y_tablas'],
                    ],
                    [
                        'title' => 'Historial Cobros',
                        'url' => '/reports/record-charges',
                        'icon' => 'fiber_manual_record',
                        'permissions' => [
                            'visualizar_historial_general', 'visualizar_historial_detallado', 'descargar_reporte_general', 'descargar_reporte_detallado'
                        ],
                    ],
                    [
                        'title' => 'Cobro Locales',
                        'url' => '/reports/shop-charges',
                        'icon' => 'fiber_manual_record',
                        'permissions' => ['listar_reporte_cobro_locales'],
                    ],
                ],
            ],
        ];


        // Extraer todos los nombres de permisos en un solo array
        $allPermissions = [];
        $allPermissions = [];
        foreach ($menusWithPermissions as $menuData) {
            // Verificar si la clave 'permissions' está definida en el arreglo
            if (isset($menuData['permissions'])) {
                $allPermissions = array_merge($allPermissions, $menuData['permissions']);
            }

            foreach ($menuData['submenus'] as $submenuData) {
                // Verificar si la clave 'permissions' está definida en el submenú
                if (isset($submenuData['permissions'])) {
                    $allPermissions = array_merge($allPermissions, $submenuData['permissions']);
                }
            }
        }

        // Crear los permisos si aún no existen en la base de datos
        foreach ($allPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if (!$permission) {
                Permission::create(['name' => $permissionName]);
            }
        }

        // Crear los menús y asociar los permisos
        foreach ($menusWithPermissions as $menuData) {
            $menu = Menu::firstOrCreate(['title' => $menuData['title']], [
                'url' => $menuData['url'],
                'icon' => $menuData['icon'],
            ]);

            // Asociar los permisos directamente al menú (sin submenús)
            if (isset($menuData['permissions']) && is_array($menuData['permissions'])) {
                foreach ($menuData['permissions'] as $permission) {
                    $perm = Permission::where('name', $permission)->first();
                    if ($perm) {
                        $menuPermission = new MenuPermission(['permission_id' => $perm->id]);
                        $menu->permissions()->save($menuPermission);
                    }
                }
            }

            // Asociar los submenús
            foreach ($menuData['submenus'] as $submenuData) {
                $submenu = $menu->children()->firstOrCreate(['title' => $submenuData['title']], [
                    'url' => $submenuData['url'],
                    'icon' => $submenuData['icon'],
                ]);

                // Asociar los permisos al submenú, si es necesario
                if (isset($submenuData['permissions']) && is_array($submenuData['permissions'])) {
                    foreach ($submenuData['permissions'] as $permission) {
                        $perm = Permission::where('name', $permission)->first();
                        if ($perm) {
                            $submenuPermission = new MenuPermission(['permission_id' => $perm->id]);
                            $submenu->permissions()->save($submenuPermission);
                        }
                    }
                }
            }
        }
    }
}
