<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cobro;
use App\Models\Local;
use App\Models\TipoVehiculo;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function obtenerInformacionDashboard()
    {
        $clientesActivos = Cliente::where('estado', true)->count();
        $usuarios = User::count();
        $locales = Local::where('disponible', false)->count();
        $recaudacionDiaria = Cobro::whereDate('created_at', Carbon::today())->sum('valor');
        $recaudacionMensual = Cobro::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->sum('valor');

        // Obtener todos los tipos de vehículos
        $tiposVehiculos = TipoVehiculo::all();

        // Obtener la recaudación por tipo de vehículo solo para el mes actual
        $recaudacionPorTipoVehiculo = Cobro::leftJoin('vehiculos', 'cobros.idVehiculo', '=', 'vehiculos.id')
            ->leftJoin('tipo_vehiculos', 'vehiculos.idTipoVehiculo', '=', 'tipo_vehiculos.id')
            ->whereMonth('cobros.created_at', date('m'))
            ->whereYear('cobros.created_at', date('Y'))
            ->groupBy('tipo_vehiculos.id')
            ->selectRaw('tipo_vehiculos.id, MAX(tipo_vehiculos.detalle) as nombre, COALESCE(SUM(cobros.valor), 0) as suma')
            ->get();

        // Realizar una unión (join) de todos los tipos de vehículos con la recaudación por tipo de vehículo
        $recaudacionPorTipoVehiculo = $tiposVehiculos->map(function ($tipoVehiculo) use ($recaudacionPorTipoVehiculo) {
            $recaudacion = $recaudacionPorTipoVehiculo->firstWhere('id', $tipoVehiculo->id);
            return [
                'id' => $tipoVehiculo->id,
                'nombre' => $tipoVehiculo->detalle,
                'suma' => $recaudacion ? $recaudacion->suma : 0
            ];
        });

        $data = [
            'clientesActivos' => $clientesActivos,
            'usuarios' => $usuarios,
            'locales' => $locales,
            'recaudacionDiaria' => $recaudacionDiaria,
            'recaudacionMensual' => $recaudacionMensual,
            'recaudacionPorTipoVehiculo' => $recaudacionPorTipoVehiculo
        ];

        return response()->json($data);
    }
}
