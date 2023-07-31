<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contrato;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContratoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contratos = Contrato::join('users', 'contratos.user_id', '=', 'users.id')
            ->join('personas', 'users.persona_id', '=', 'personas.id')
            ->select('contratos.id', DB::raw("CONCAT(personas.nombres, ' ', personas.apellidos) as trabajador"), 'contratos.mesesContrato', 'contratos.fechaInicio', 'contratos.fechaFin', 'contratos.cv', 'contratos.referencias', 'contratos.estado')
            ->get();

        return response()->json($contratos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        // Validar los campos del formulario
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'cv' => 'nullable',
            'referencias' => 'nullable',
            'mesesContrato' => 'required|integer',
            'fechaInicio' => 'nullable|date',
            'fechaFin' => 'nullable|date',
        ]);

        // Verificar si la validación falla
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 401);
        }

        // Verificar si el usuario ya tiene un contrato activo
        $existeContrato = Contrato::where('user_id', $data['user_id'])->where('estado', true)->first();

        if ($existeContrato) {
            return response()->json(['error' => "El usuario cuenta con un contrato Activo."]);
        }


        if (key_exists('cv', $data)) $data['cv'] = json_encode($data['cv']);
        if (key_exists('referencias', $data)) $data['referencias'] = json_encode($data['referencias']);

        $fechaFin =  \Carbon\Carbon::parse($data['fechaInicio'])->addMonths($data['mesesContrato'])->format("Y-m-d H:i:s");
        $data['fechaFin'] = $fechaFin;

        $contrato = new Contrato;
        $contrato->user_id = $data['user_id'];
        $contrato->cv = $data['cv'];
        $contrato->referencias = $data['referencias'];
        $contrato->mesesContrato = $data['mesesContrato'];
        $contrato->fechaInicio = $data['fechaInicio'];
        $contrato->fechaFin = $data['fechaFin'];
        $contrato->estado = true; // Establecer el estado activo

        // Guardar el contrato en la base de datos
        $contrato->save();

        return response()->json(['message' => "El contrato se registró con éxito."]);
    }


    public function usersWithoutContract()
    {
        $users = User::whereDoesntHave('contrato')
            ->orWhereHas('contrato', function ($query) {
                $query->where('estado', 0);
            })
            ->leftJoin('personas', 'users.persona_id', '=', 'personas.id')
            ->select('users.id', DB::raw("CONCAT(personas.nombres, ' ', personas.apellidos) as empleado"))
            ->get();

        return $users;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Buscar el contrato por su ID
        $contrato = Contrato::findOrFail($id);

        // Desactivar el contrato
        $contrato->estado = false;
        $contrato->save();

        // Acceder al usuario asociado y desactivar la persona
        $usuario = User::find($contrato->user_id);
        if ($usuario) {
            $persona = Persona::find($usuario->persona_id);
            if ($persona) {
                $persona->activo = false;
                $persona->save();
            }
        }

        return response()->json(['message' => "Contrato eliminado exitosamente."]);
    }
}
