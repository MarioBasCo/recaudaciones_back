<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuPermission;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{

    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create([
            'name' => $request->input('name'),
        ]);

        return response()->json([
            'message' => 'Permission created successfully.',
            'permission' => $permission,
        ]);
    }

    public function editPermission(Request $request, $permissionId)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permissionId,
        ]);

        $permission = Permission::findOrFail($permissionId);

        $permission->name = $request->input('name');
        $permission->save();

        return response()->json([
            'message' => 'Permission updated successfully.',
            'permission' => $permission,
        ]);
    }

    public function deletePermission($permissionId)
    {
        $permission = Permission::findOrFail($permissionId);

        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted successfully.',
        ]);
    }

    public function listPermission()
    {
        $menus = Menu::with('children')->whereNull('parent_id')->get();

        // Personalizar la estructura de la respuesta JSON
        $menuData = $this->buildMenuData($menus);

        return response()->json($menuData);
    }

    private function buildMenuData($menus)
    {
        $menuData = [];

        foreach ($menus as $menu) {
            $menuInfo = [
                'id' => $menu->id,
                'title' => $menu->title,
                'url' => $menu->url,
                'icon' => $menu->icon,
                'permissions' => $menu->permissions->map(function ($menuPermission) {
                    $permission = $menuPermission->permission;
                    return ['id' => $permission->id, 'name' => $permission->name];
                }),
            ];

            // Recursivamente obtener los submenús y sus permisos
            if ($menu->children->isNotEmpty()) {
                $menuInfo['children'] = $this->buildMenuData($menu->children);
            }

            $menuData[] = $menuInfo;
        }

        return $menuData;
    }
}
