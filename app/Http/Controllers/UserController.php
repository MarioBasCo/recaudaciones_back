<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Contrato;
use App\Models\Menu;
use App\Models\User;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('persona', 'roles')->get();

        $transformedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->name,
                'email' => $user->email,
                'persona_id' => $user->persona->id,
                'identificacion' => $user->persona->identificacion,
                'apellidos' => $user->persona->apellidos,
                'nombres' => $user->persona->nombres,
                'celular' => $user->persona->celular,
                'direccion' => $user->persona->direccion,
                'role_id' => $user->roles->first()->id,
                'name_role' => $user->roles->first()->name,
                'activo' => $user->persona->activo
            ];
        });

        // Convierte el array en una colección
        $coleccion = Collection::make($transformedUsers);

        // Filtra los elementos con la propiedad "activo" en true
        $resultado = $coleccion->filter(function ($elemento) {
            return $elemento['activo'] == true;
        });

        return response()->json($resultado);
    }

    public function store(UserRequest $request)
    {
        $requestData = $request->validated();

        // Verificar si el correo ya existe
        if (User::where('email', $requestData['email'])->exists()) {
            return response()->json(['error' => 'El correo ya está en uso.'], 422);
        }

        // Verificar si el name ya existe
        if (User::where('name', $requestData['name'])->exists()) {
            return response()->json(['error' => 'El nombre de usuario ya está en uso.'], 422);
        }

        // Verificar si la identificación ya existe
        if (Persona::where('identificacion', $requestData['persona']['identificacion'])->exists()) {
            return response()->json(['error' => 'La identificación ya está en uso.'], 422);
        }

        $persona = $this->createPersona($requestData['persona']);
        $user = $this->createUser($requestData, $persona);
        $this->assignRoleToUser($user, $requestData['role']);

        return response()->json($user, 201);
    }

    public function update(UserRequest $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'El usuario no existe.'], 404);
        }

        $requestData = $request->validated();
        $validator = $this->validateUserData($requestData, $user);

        if ($validator->fails()) {
            $response = $this->createResponse(false, 'Error de validación', $validator->errors());
            return response()->json($response, 401);
        }

        $this->updateUser($user, $requestData);
        $this->updatePersona($user->persona, $requestData['persona']);
        $this->assignRoleToUser($user, $requestData['role']);

        $response = $this->createResponse(true, 'Usuario actualizado correctamente');
        return response()->json($response, 200);
    }

    private function validateUserData($data, $user)
    {
        return Validator::make($data, [
            'email' => [
                Rule::unique('users')->ignore($user->id),
            ],
            'name' => [
                Rule::unique('users')->ignore($user->id),
            ],
            'persona.identificacion' => [
                Rule::unique('personas', 'identificacion')->ignore($user->persona_id),
            ],
        ]);
    }

    private function createPersona($data)
    {
        return Persona::create([
            'identificacion' => $data['identificacion'],
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'direccion' => $data['direccion'],
            'celular' => $data['celular'],
        ]);
    }

    private function createUser($data, $persona)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'persona_id' => $persona->id,
        ]);
    }

    private function updateUser($user, $data)
    {
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
    }

    private function updatePersona($persona, $data)
    {
        $persona->identificacion = $data['identificacion'];
        $persona->nombres = $data['nombres'];
        $persona->apellidos = $data['apellidos'];
        $persona->direccion = $data['direccion'];
        $persona->celular = $data['celular'];
        $persona->save();
    }

    private function assignRoleToUser($user, $roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }
    }

    private function createResponse($status, $message, $data = null)
    {
        $response = [
            'status' => $status,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $response;
    }


    public function eliminarUsuario($id)
    {
        // Buscar el usuario por su ID
        $usuario = User::findOrFail($id);

        // Desactivar la persona asociada
        $persona = Persona::find($usuario->persona_id);
        if ($persona) {
            $persona->activo = false;
            $persona->save();
        }

        // Desactivar el contrato asociado (si existe)
        $contrato = Contrato::where('user_id', $usuario->id)->first();
        if ($contrato) {
            $contrato->estado = false;
            $contrato->save();
        }

        return response()->json(['message' => 'Usuario eliminado exitosamente.']);
    }

    public function getUserMenusAndPermissions($id)
    {
        $user = User::findOrFail($id);

        $userRoles = $user->roles()->with('permissions')->first();
        $menuPermissions = collect($userRoles['permissions'])->pluck('name')->toArray();

        $menus = Menu::with('children')->whereNull('parent_id')->get();
        $menuData = $this->buildMenuData($menus);

        return response()->json([
            'rol' => [
                'id' => $userRoles->id,
                'name'=> $userRoles->name
            ],
            'permisos' => $menuPermissions,
            'menus' => $menuData
        ], 200);
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
