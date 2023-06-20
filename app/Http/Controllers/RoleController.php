<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        try {
            $role = Role::get(['id', 'name']);
            $response = [];

            if ($role->isNotEmpty()) {
                $response = $this->generateResponse(true, 'Existen datos', $role);
            } else {
                $response = $this->generateResponse(false, 'No existen datos');
            }

            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json($this->generateResponse(false, $th->getMessage()), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validacion = $this->validateRole($request);
            if ($validacion !== null) {
                return $validacion;
            }

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => $request->guard_name
            ]);

            $response = $this->generateResponse(true, 'El Rol se registró con éxito', $role);
            return response()->json($response, 200);
        } catch (\Throwable $th) {
            return response()->json($this->generateResponse(false, $th->getMessage()), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                $response = $this->generateResponse(false, 'El Rol no existe.');
                return response()->json($response, 404);
            }

            $validacion = $this->validateRole($request, $id);
            if ($validacion !== null) {
                return $validacion;
            }

            $role->name = $request->input('name');
            $role->guard_name = $request->input('guard_name') ?? 'web';

            $role->save();

            $response = $this->generateResponse(true, 'El Rol se registró con éxito', $role);
            return response()->json($response, 200);
        } catch (\Throwable $th) {
            return response()->json($this->generateResponse(false, $th->getMessage()), 500);
        }
    }

    public function assignRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ], [
            'user_id.required' => 'El usuario es requerido',
            'user_id.exists' => 'El usuario no existe',
            'role_id.required' => 'El rol es requerido',
            'role_id.exists' => 'El rol no existe'
        ]);

        if ($validator->fails()) {
            $response = $this->generateResponse(false, 'Error de validación', $validator);
            return response()->json($response, 401);
        }

        $user = User::findOrFail($request->input('user_id'));
        $role = Role::findOrFail($request->input('role_id'));

        if ($user->syncRoles([$role])) {
            $response = $this->generateResponse(true, 'Se asignó el rol correctamente', $user);
            return response()->json($response, 200);
        }
    }

    private function validateRole(Request $request, $id = null)
    {
        $validateRol = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('roles')->ignore($id)
            ],
            'guard_name' => 'nullable'
        ], [
            'name.required' => 'El rol es requerido',
            'name.unique' => 'El nombre del rol ya existe'
        ]);

        if ($validateRol->fails()) {
            $response = $this->generateResponse(false, 'Error de validación', $validateRol);
            return response()->json($response, 401);
        }

        return null;
    }

    private function generateResponse($status, $message, $data = null)
    {
        $response = [
            'status' => $status,
            'message' => $message
        ];

        if ($status === false) {
            if (is_object($data) && method_exists($data, 'errors')) {
                $response['errors'] = $data->errors();
            }
        } else {
            $response['data'] = $data;
        }

        return $response;
    }
}
