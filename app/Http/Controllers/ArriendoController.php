<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Arriendo;
use App\Models\Local;
use Illuminate\Support\Facades\DB;

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


    public function registrarArriendo(Request $request)
    {
        $validacion = $this->validarArriendo($request->all());
        if ($validacion !== null) {
            return $validacion;
        }

        $local = Local::find($request->input('local_id'));

        if ($local->disponible) {
            $arriendo = new Arriendo();
            $arriendo->local_id = $request->input('local_id');
            $arriendo->persona_id = $request->input('persona_id');
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

    private function validarArriendo(array $data)
    {
        $rules = [
            'local_id' => 'required|exists:locales,id',
            'persona_id' => 'required|exists:personas,id',
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
}
