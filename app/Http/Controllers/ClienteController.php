<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Persona;
use App\Models\Entidad;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function listarClientes()
    {
        $clientes = Cliente::with('modelo')->where('estado', 1)->get();
        $data = [];

        foreach ($clientes as $cliente) {
            $modelo = $cliente->modelo;
            $commonFields = [
                'cliente_id' => $cliente->id,
                'identificacion' => $modelo->identificacion,
                'direccion' => $modelo->direccion,
                'celular' => $modelo->celular,
                'correo' => $modelo->correo,
                'vehiculos' => $cliente->vehiculos->map(function ($vehiculo) {
                    return [
                        'id' => $vehiculo->id,
                        'placa' => $vehiculo->placa,
                        'idTipoVehiculo' => $vehiculo->tipoVehiculo->id,
                        'tipo_vehiculo' => $vehiculo->tipoVehiculo->detalle
                    ];
                })->all(),
            ];

            $extraFields = [];

            if ($modelo instanceof Persona) {
                $extraFields = [
                    'nombres' => $modelo->nombres,
                    'apellidos' => $modelo->apellidos,
                ];
            } elseif ($modelo instanceof Entidad) {
                $extraFields = [
                    'razon_social' => $modelo->razon_social,
                ];
            }

            $data[] = array_filter(array_merge($commonFields, $extraFields));
        }

        return response()->json($data);
    }

    public function crearCliente(Request $request)
    {
        $tipo = $request->input('tipo');

        $validacionCliente = $this->validarCliente($request, $tipo);
        if ($validacionCliente !== null) {
            return $validacionCliente;
        }

        $cliente = new Cliente();

        if ($tipo === 'persona') {
            $persona = Persona::create($this->getPersonaData($request));
            $cliente->model = Persona::class;
            $cliente->model_id = $persona->id;
        } elseif ($tipo === 'entidad') {
            $entidad = Entidad::create($this->getEntidadData($request));
            $cliente->model = Entidad::class;
            $cliente->model_id = $entidad->id;
        }

        $cliente->save();

        return response()->json([
            'message' => 'Cliente creado exitosamente',
            'data' => $cliente
        ]);
    }

    private function getPersonaData(Request $request)
    {
        return [
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'identificacion' => $request->identificacion,
            'direccion' => $request->direccion,
            'correo' => $request->correo,
            'celular' => $request->celular
        ];
    }

    private function getEntidadData(Request $request)
    {
        return [
            'razon_social' => $request->razon_social,
            'identificacion' => $request->identificacion,
            'direccion' => $request->direccion,
            'correo' => $request->correo,
            'celular' => $request->celular
        ];
    }

    public function editarCliente(Request $request, $clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);

        $tipoActual = $cliente->model;
        $modeloIdActual = $cliente->model_id;

        $tipo = $request->input('tipo');

        $validacion = $this->validarCliente($request, $tipo, $modeloIdActual, $tipoActual);
        if ($validacion !== null) {
            return $validacion;
        }

        // Obtener el nombre completo de la clase del modelo a partir del campo tipo de la solicitud
        $modeloClase = 'App\Models\\' . ucfirst($request->input('tipo'));

        // Verificar si el tipo actual ha cambiado
        if ($modeloClase !== $tipoActual) {
            // Eliminar el registro desvinculado según el tipo actual
            if ($tipoActual === 'App\Models\Persona') {
                Persona::destroy($modeloIdActual);
            } elseif ($tipoActual === 'App\Models\Entidad') {
                Entidad::destroy($modeloIdActual);
            }
        }

        if ($tipo === 'persona') {
            $data = $this->getPersonaData($request);

            if ($cliente->model !== Persona::class) {
                $persona = Persona::create($data);
                $cliente->model_id = $persona->id;
            } else {
                $persona = Persona::findOrFail($cliente->model_id);
                $persona->update($data);
            }
        } elseif ($tipo === 'entidad') {
            $data = $this->getEntidadData($request);

            if ($cliente->model !== Entidad::class) {
                $entidad = Entidad::create($data);
                $cliente->model_id = $entidad->id;
            } else {
                $entidad = Entidad::findOrFail($cliente->model_id);
                $entidad->update($data);
            }
        }

        $cliente->model = $modeloClase;
        $cliente->save();

        return response()->json([
            'message' => 'Cliente actualizado exitosamente',
            'data' => $cliente
        ]);
    }

    public function eliminarCliente($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);

        if ($cliente->model === Persona::class) {
            $persona = Persona::findOrFail($cliente->model_id);
            $persona->activo = false; // Desactivar la persona
            $persona->save();
        } elseif ($cliente->model === Entidad::class) {
            $entidad = Entidad::findOrFail($cliente->model_id);
            $entidad->activo = false; // Desactivar la entidad
            $entidad->save();
        }

        $cliente->estado = false;
        $cliente->save();

        return response()->json(['message' => 'Cliente eliminado exitosamente']);
    }

    private function validarCliente(Request $request, $tipo, $id = null, $model = null)
    {
        $tiposPermitidos = ['persona', 'entidad'];
        if (!in_array($tipo, $tiposPermitidos)) {
            return response()->json(['error' => 'Tipo de cliente no permitido'], 400);
        }

        $cambio = false;
        if ($model) {
            $modeloRequest = 'App\Models\\' . ucfirst($tipo);
            $cambio = $modeloRequest !== $model;
            $tipo = $cambio ? $tipo : strtolower(class_basename($model));
        }

        $reglas = [
            'identificacion' => [
                'required',
                'digits:' . ($tipo === 'persona' ? 10 : 13),
                !$cambio ? Rule::unique(($tipo === 'entidad' ? 'entidades' : 'personas'))->ignore($id) : Rule::unique(($tipo === 'entidad' ? 'entidades' : 'personas')),
            ],
        ];

        $mensajes = [
            'identificacion.required' => 'El campo número de identificación es requerido',
            'identificacion.unique' => 'El campo número de identificación existe en nuestros registros',
            'identificacion.digits' => 'El campo número de identificación debe tener ' . ($tipo === 'persona' ? '10' : '13') . ' dígitos',
        ];

        if ($tipo === 'persona') {
            $reglas['nombres'] = 'required';
            $reglas['apellidos'] = 'required';

            $mensajes['nombres.required'] = 'El campo nombre es requerido';
            $mensajes['apellidos.required'] = 'El campo apellido es requerido';
        } elseif ($tipo === 'entidad') {
            $reglas['razon_social'] = 'required';

            $mensajes['razon_social.required'] = 'El campo razón social es requerido';
        }

        $validator = Validator::make($request->all(), $reglas, $mensajes);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return null;
    }


    public function verificarExitenciaCedula($valor)
    {
        $existe = Persona::where('identificacion', $valor)->exists();
        return response()->json($existe);
    }

    public function verificarExistenciaRuc($valor)
    {
        $existe = Entidad::where('identificacion', $valor)->exists();
        return response()->json($existe);
    }
    
}
