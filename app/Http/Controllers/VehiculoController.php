<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Cliente;
use App\Models\Persona;
use App\Models\Entidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VehiculoController extends Controller
{
    public function asignarVehiculo(Request $request, $cliente)
    {
        $cliente = Cliente::findOrFail($cliente);

        if (!$cliente) {
            return response()->json(['error' => 'El cliente no existe'], 404);
        }

        $validacion = $this->validarVehiculo($request);
        if ($validacion !== null) {
            return $validacion;
        }

        $vehiculo = new Vehiculo();
        $vehiculo->idCliente = $cliente;
        $vehiculo->idTipoVehiculo = $request->input('idTipoVehiculo');
        $vehiculo->placa = $request->input('placa');

        $cliente->vehiculos()->save($vehiculo);

        return response()->json([
            'message' => 'Vehículo asignado correctamente',
            'data' => $vehiculo->id
        ]);
    }

    public function editarVehiculo(Request $request, $clienteId, $vehiculoId)
    {
        $cliente = Cliente::findOrFail($clienteId);

        $vehiculo = $cliente->vehiculos()->findOrFail($vehiculoId);

        $validacion = $this->validarVehiculo($request, $vehiculoId);
        if ($validacion !== null) {
            return $validacion;
        }

        $vehiculo->idTipoVehiculo = $request->input('idTipoVehiculo');
        $vehiculo->placa = $request->input('placa');

        $vehiculo->save();

        return response()->json(['message' => 'Vehículo actualizado correctamente']);
    }

    public function eliminarVehiculo($clienteId, $vehiculoId)
    {
        $cliente = Cliente::findOrFail($clienteId);

        if (!$cliente) {
            return response()->json(['error' => 'El cliente no existe'], 404);
        }

        $vehiculo = $cliente->vehiculos()->where('id', $vehiculoId)->first();

        if (!$vehiculo) {
            return response()->json(['error' => 'El vehículo no existe'], 404);
        }

        $vehiculo->estado = 0;
        $vehiculo->save();

        return response()->json(['message' => 'Vehículo eliminado correctamente']);
    }

    private function validarVehiculo(Request $request, $id = null)
    {
        $mensajes = [
            'idTipoVehiculo.required' => 'El campo Tipo Vehículo es requerido',
            'idTipoVehiculo.exists' => 'El campo Tipo Vehículo no existe en nuestros registros',
            'placa.required' => 'El campo placa es requerido',
            'placa.unique' => 'El campo placa existe en nuestros registros',
        ];

        $validator = Validator::make($request->all(), [
            'idTipoVehiculo' => 'required|exists:tipo_vehiculos,id',
            'placa' => ['required', Rule::unique('vehiculos')->ignore($id)],
        ], $mensajes);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return null;
    }

    public function verificarExistencia($placa)
    {
        $existe = Vehiculo::where('placa', $placa)->exists();
        return response()->json($existe);
    }


    public function obtenerDatosPorPlaca($placa)
    {
        $vehiculo = Vehiculo::where('placa', $placa)->first();

        if (!$vehiculo) {
            return response()->json(['error' => 'No se encontró ningún vehículo con esa placa'], 404);
        }

        $vehiculo->makeHidden(['created_at', 'updated_at', 'estado']);

        if ($vehiculo->cliente) {
            $vehiculo->cliente->makeHidden(['created_at', 'updated_at', 'estado']);
            if ($vehiculo->cliente->modelo) {
                $vehiculo->cliente->modelo->makeHidden(['created_at', 'updated_at', 'activo']);
            }
        }

        if ($vehiculo->tipoVehiculo) {
            $vehiculo->tipoVehiculo->makeHidden(['created_at', 'updated_at', 'activo']);
        }

        return response()->json(['vehiculo' => $vehiculo]);
    }
}
