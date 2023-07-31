<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Arriendo;
use App\Models\Local;
use App\Models\Persona;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArriendoController extends Controller
{
    public function listarArriendos()
    {
        $arriendos = Arriendo::where('arriendos.estado', true)
            ->join('locales', 'arriendos.local_id', '=', 'locales.id')
            ->join('personas', 'arriendos.persona_id', '=', 'personas.id')
            ->select('arriendos.*', 'locales.codigo', DB::raw("CONCAT(personas.nombres, ' ', personas.apellidos) as arrendatario"))
            ->get();

        return $arriendos;
    }


    public function listarContratosActivos()
    {
        // Obtener la fecha actual y el mes en curso
        $fechaActual = Carbon::now();

        // Obtener todos los contratos activos
        $contratosActivos = Arriendo::where('estado', true)
            ->whereDate('fecha', '<=', $fechaActual)
            ->whereRaw("DATE_ADD(fecha, INTERVAL meses MONTH) >= '{$fechaActual->toDateString()}'")
            ->with('persona', 'local')
            ->get();

        // Construir la estructura de datos para el JSON
        $resultado = [];
        foreach ($contratosActivos as $contrato) {
            $arrendatario = $contrato->persona->nombres . ' ' . $contrato->persona->apellidos;
            $local = $contrato->local->detalle;
            $fechaFinContrato = Carbon::parse($contrato->fecha)->addMonths($contrato->meses)->format('Y-m-d');

            $resultado[] = [
                'id_arriendo' => $contrato->id,
                'arrendatario' => $arrendatario,
                'local' => $local,
                'fechaInicio' => $contrato->fecha,
                'fechaFin' => $fechaFinContrato,
            ];
        }

        // Retornar el resultado como respuesta JSON
        return response()->json($resultado);
    }


    public function buscarPersona($ced)
    {
        $persona = Persona::where('identificacion', $ced)
            ->where('activo', true)
            ->get()->first();

        return response()->json($persona);
    }

    public function listarLocales()
    {
        $locales = Local::where('disponible', true)
            ->where('estado', true)->get();

        return response()->json($locales);
    }

    public function registrarArriendo(Request $request)
    {
        $validacion = $this->validarArriendo($request->all());
        if ($validacion !== null) {
            return $validacion;
        }

        $local = Local::find($request->input('local_id'));

        if ($local->disponible) {
            $personaId = $request->input('persona_id');
            $persona = Persona::find($personaId);

            if (!$persona) {
                // La persona no existe, así que la creamos
                $nuevaPersona = new Persona();
                $nuevaPersona->identificacion = $request->input('identificacion');
                $nuevaPersona->nombres = $request->input('nombres');
                $nuevaPersona->apellidos = $request->input('apellidos');
                $nuevaPersona->direccion = $request->input('direccion');
                $nuevaPersona->correo = $request->input('correo');
                $nuevaPersona->celular = $request->input('celular');
                $nuevaPersona->save();

                // Obtener el ID de la nueva persona creada
                $personaId = $nuevaPersona->id;
            } else {
                // La persona existe, actualizamos los campos deseados
                $persona->direccion = $request->input('direccion');
                $persona->correo = $request->input('correo');
                $persona->celular = $request->input('celular');
                $persona->save();
            }

            $arriendo = new Arriendo();
            $arriendo->local_id = $request->input('local_id');
            $arriendo->persona_id = $personaId;
            $arriendo->valorArriendo = $request->input('valorArriendo');
            $arriendo->fecha = $request->input('fecha');
            $arriendo->meses = $request->input('meses');
            $arriendo->save();
            $local->disponible = false;
            $local->save();

            return response()->json([
                'message' => 'Arriendo registrado exitosamente.',
                'arriendo' => $arriendo,
            ], 201);
        } else {
            return response()->json([
                'message' => 'El local se encuentra en uso.',
            ], 401);
        }
    }

    private function validarArriendo(array $data)
    {
        $rules = [
            'local_id' => 'required|exists:locales,id',
            //'persona_id' => 'required|exists:personas,id',
            'valorArriendo' => 'required|numeric',
            'fecha' => 'required|date',
            'meses' => 'required|numeric',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 401);
        }

        return null;
    }


    public function actualizarArriendo(Request $request, $id)
    {
        // Buscar el arriendo a actualizar
        $arriendo = Arriendo::find($id);

        // Comprobar si el arriendo no existe
        if (!$arriendo) {
            return response()->json([
                'message' => 'Arriendo no encontrado.',
            ], 404);
        }

        $validacion = $this->validarArriendo($request->all());
        if ($validacion !== null) {
            return $validacion;
        }

        // Actualizar los campos del arriendo
        $arriendo->local_id = $request->input('local_id');
        $arriendo->persona_id = $request->input('persona_id');
        $arriendo->valorArriendo = $request->input('valorArriendo');
        $arriendo->fecha = $request->input('fecha');
        $arriendo->meses = $request->input('meses');
        $arriendo->save();

        return response()->json([
            'message' => 'Arriendo actualizado exitosamente.',
            'arriendo' => $arriendo,
        ], 200);
    }

    public function eliminarArriendo($arriendoId)
    {
        $arriendo = Arriendo::find($arriendoId);

        if ($arriendo && $arriendo->estado) {
            $arriendo->update(['estado' => false]);
            return response()->json([
                'message' => 'El registro fue eliminado correctamente.',
            ], 200);
            DB::beginTransaction();

            try {
                $arriendo->update(['estado' => false]);

                $local = $arriendo->local;

                if ($local && !$local->disponible) {
                    $local->update(['disponible' => true]);

                    return response()->json([
                        'message' => 'El registro fue eliminado correctamente.',
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'No se pudo encontrar el local o esta disponible.',
                    ], 400);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'message' => 'No se pudo eliminar el registro.',
                ], 500);
            }
        } else {
            return response()->json([
                'message' => 'No se pudo encontrar el registro.',
            ], 404);
        }
    }
}
