<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Turno;
use App\Models\Cobro;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TurnoController extends Controller
{
    public function index()
    {
        //
    }

    public function aperturar(Request $request)
    {
        if (!$this->validarUserId($request->user_id)) {
            return response()->json(['message' => 'El user_id proporcionado no es válido.'], 400);
        }

        $fechaActual = Carbon::now(); // Obtener la fecha actual

        // Verificar si ya existe un turno abierto para el usuario
        $turnoAbierto = Turno::where('user_id', $request->user_id)
            ->where('cerrado', false)
            ->first();

        if ($turnoAbierto) {
            return response()->json(['message' => 'Ya existe un turno abierto para este usuario.'], 400);
        }

        // Crear un nuevo turno
        $turno = Turno::create([
            'user_id' => $request->user_id,
            'inicio' => $fechaActual,
            'fin' => null,
            'cerrado' => false,
            'recaudado' => 0,
            'observacion' => null,
            'estado' => true,
        ]);

        return response()->json(['message' => 'Turno aperturado correctamente.', 'turno' => $turno], 200);
    }

    public function cerrarTurno(Request $request, $id)
    {
        $turno = Turno::findOrFail($id);

        /*  if (!$this->validarUserId($request->user_id)) {
            return response()->json(['message' => 'El user_id proporcionado no es válido.'], 400);
        } */

        // Verificar si el turno ya está cerrado
        if ($turno->cerrado) {
            return response()->json(['message' => 'El turno ya está cerrado.'], 400);
        }

        // Calcular el monto recaudado sumando los registros relacionados en la tabla cobros
        $montoRecaudado = Cobro::where('turno_id', $turno->id)->sum('valor');

        // Actualizar los campos del turno
        $turno->fin = Carbon::now();
        $turno->cerrado = true;
        $turno->recaudado = $montoRecaudado;
        $turno->observacion = $request->observacion;
        $turno->save();

        return response()->json(['message' => 'Turno cerrado correctamente.', 'turno' => $turno], 200);
    }

    private function validarUserId($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        return true;
    }

    public function turnoAbierto(Request $request)
    {
        $usuarioId = $request->user_id;

        // Obtener el único turno abierto para el usuario
        $turnoAbierto = Turno::where('user_id', $usuarioId)
            ->where('cerrado', false)
            ->first();

        if (!$turnoAbierto) {
            return response()->json(['message' => 'No hay turno abierto para este usuario.'], 404);
        }

        return response()->json(['turno' => $turnoAbierto], 200);
    }


    public function listarTotales($turnoId)
    {
        $sumaTotalPorTipo = DB::table('tipo_vehiculos')
            ->leftJoin('vehiculos', function ($join) {
                $join->on('tipo_vehiculos.id', '=', 'vehiculos.idTipoVehiculo');
            })
            ->leftJoin('cobros', function ($join) use ($turnoId) {
                $join->on('cobros.idVehiculo', '=', 'vehiculos.id')
                    ->where('cobros.turno_id', '=', $turnoId);
            })
            ->groupBy('tipo_vehiculos.id', 'tipo_vehiculos.detalle')
            ->selectRaw('tipo_vehiculos.id, tipo_vehiculos.detalle, COALESCE(SUM(IFNULL(cobros.valor, 0)), 0) AS suma_total')
            ->get();

        return $sumaTotalPorTipo;
    }
}
