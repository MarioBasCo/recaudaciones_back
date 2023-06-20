<?php

namespace App\Http\Controllers;

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
}
