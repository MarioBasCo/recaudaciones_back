<?php

namespace App\Http\Controllers;

use App\Models\TipoVehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoVehiculoController extends Controller
{
    public function listarTipos()
    {
        $data = TipoVehiculo::where('activo', true)->get();
        return response()->json($data);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'detalle' => 'required',
            'valor' => 'required',
        ], [
            'detalle.required' => 'El detalle es requerido',
            'valor.required' => 'El valor es requerido'
        ]);

        if ($validator->fails()) {
            $response = $this->createResponse(false, 'Error de validación', $validator->errors());
            return response()->json($response, 401);
        }

        $tipo = TipoVehiculo::create([
            'detalle' => $request->detalle,
            'valor' => $request->valor
        ]);

        $response = $this->createResponse(true, 'El Tipo de vehículo se registró con éxito', $tipo);
        return response()->json($response, 200);
    }

    public function update(Request $request, $tipoId)
    {
        $tipo = TipoVehiculo::find($tipoId);

        if (!$tipo) {
            $response = $this->createResponse(false, 'El Tipo de vehículo no existe.');
            return response()->json($response, 404);
        }

        $validator = Validator::make($request->all(), [
            'detalle' => 'required',
            'valor' => 'required',
        ], [
            'detalle.required' => 'El detalle es requerido',
            'valor.required' => 'El valor es requerido'
        ]);

        if ($validator->fails()) {
            $response = $this->createResponse(false, 'Error de validación', $validator->errors());
            return response()->json($response, 401);
        }

        $tipo->detalle = $request->detalle;
        $tipo->valor = $request->valor;
        $tipo->save();

        $response = $this->createResponse(true, 'El Tipo de vehículo se actualizó con éxito', $tipo);
        return response()->json($response, 200);
    }

    public function destroy($tipoId)
    {
        $tipo = TipoVehiculo::find($tipoId);

        if (!$tipo) {
            $response = $this->createResponse(false, 'El Tipo de vehículo no existe.');
            return response()->json($response, 404);
        }

        $tipo->activo = 0;
        $tipo->save();

        $response = $this->createResponse(true, 'El Tipo de vehículo fue eliminado.');
        return response()->json($response, 200);
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
}
