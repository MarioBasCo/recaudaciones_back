<?php

namespace App\Http\Controllers;

use App\Models\Cobro;
use App\Models\TicketNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CobroController extends Controller
{
    public function store(Request $request)
    {

        try {
            DB::beginTransaction();

            $currentYear = date('Y');

            $ticketNumber = TicketNumber::where('year', $currentYear)
                ->where('disabled', false)
                ->lockForUpdate() // Bloquear el registro para evitar conflictos de concurrencia
                ->firstOrFail();

            // Incrementar el número de ticket y actualizar el registro
            $ticketNumber->number += 1;
            $ticketNumber->save();

            $currentDateTime = Carbon::now();

            // Crear el registro de cobro con el número de ticket obtenido
            $cobro = new Cobro();
            $cobro->idUsuario = $request->idUsuario;
            $cobro->ticket_number = $ticketNumber->number;
            $cobro->idVehiculo = $request->idVehiculo;
            $cobro->valor = $request->valor;
            $cobro->turno_id = $request->turno_id;
            $cobro->fecha = $currentDateTime->toDateString();;
            $cobro->hora = $currentDateTime->toTimeString();;
            $cobro->save();

            DB::commit();

            return response()->json(['message' => 'Cobro registrado con éxito', 'cobro' => $cobro]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => 'Error al registrar el cobro', 'error' => $e->getMessage()], 500);
        }
    }

    public function reportePorFechas($fechaInicio, $fechaFin)
    {
        $sumaTotalPorTipo = DB::table('tipo_vehiculos')
            ->leftJoin('vehiculos', 'tipo_vehiculos.id', '=', 'vehiculos.idTipoVehiculo')
            ->leftJoin('cobros', 'cobros.idVehiculo', '=', 'vehiculos.id')
            ->whereBetween('cobros.fecha', [$fechaInicio, $fechaFin])
            ->groupBy('tipo_vehiculos.id', 'tipo_vehiculos.detalle')
            ->selectRaw('tipo_vehiculos.id, tipo_vehiculos.detalle, COALESCE(SUM(IFNULL(cobros.valor, 0)), 0) AS suma_total')
            ->get();

        $status = $sumaTotalPorTipo->contains('suma_total', '>', 0);

        return response()->json([
            'status' => $status,
            'data' => $sumaTotalPorTipo,
        ]);
    }


    public function historialPorFechas($fechaInicio, $fechaFin)
    {
        $informacion = DB::table('cobros')
            ->join('vehiculos', 'cobros.idVehiculo', '=', 'vehiculos.id')
            ->join('tipo_vehiculos', 'vehiculos.idTipoVehiculo', '=', 'tipo_vehiculos.id')
            ->join('users', 'cobros.idUsuario', '=', 'users.id')
            ->leftJoin('clientes', 'vehiculos.idCliente', '=', 'clientes.id')
            ->leftJoin('personas', function ($join) {
                $join->on('clientes.model_id', '=', 'personas.id')
                    ->where('clientes.model', '=', 'App\Models\Persona');
            })
            ->leftJoin('entidades', function ($join) {
                $join->on('clientes.model_id', '=', 'entidades.id')
                    ->where('clientes.model', '=', 'App\Models\Entidad');
            })
            ->select(
                'vehiculos.placa',
                'tipo_vehiculos.detalle AS tipo_vehiculo',
                'cobros.ticket_number',
                'cobros.valor',
                'cobros.fecha',
                'cobros.hora',
                'users.name AS usuario',
                DB::raw("COALESCE(CONCAT(personas.nombres, ' ', personas.apellidos), entidades.razon_social) AS cliente"),
                DB::raw("IFNULL(personas.identificacion, entidades.identificacion) AS identificacion")
            )
            ->whereBetween('cobros.fecha', [$fechaInicio, $fechaFin])
            ->orderBy('cobros.id')
            ->get();

        return $informacion;
    }
}
