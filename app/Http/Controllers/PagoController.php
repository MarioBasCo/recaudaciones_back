<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Pago;
use App\Models\Arriendo;

class PagoController extends Controller
{
    public function listarPagosMensuales()
    {
        // Obtener la fecha actual y el mes en curso
        $fechaActual = Carbon::now();

        // Obtener los contratos vigentes
        $contratosVigentes = Arriendo::whereDate('fecha', '<=', $fechaActual)
            ->whereRaw("DATE_ADD(fecha, INTERVAL meses MONTH) >= '{$fechaActual->toDateString()}'")
            ->with('persona', 'local')
            ->get();

        // Recorrer los contratos vigentes y calcular la información adicional
        $pagosMensuales = [];
        foreach ($contratosVigentes as $contrato) {
            $arrendatario = $contrato->persona->nombres . ' ' . $contrato->persona->apellidos;
            $correo = $contrato->persona->correo;
            $local = $contrato->local->detalle;
            $valorArriendo = $contrato->valorArriendo;

            // Obtener los pagos asociados al contrato para el mes en curso
            $pagos = Pago::where('arriendo_id', $contrato->id)
                ->whereYear('fechaPago', $fechaActual->year)
                ->whereMonth('fechaPago', $fechaActual->month)
                ->get();

            $ultimoPago = null;
            $totalAbonos = 0;

            // Calcular información adicional si hay pagos asociados
            if ($pagos->isNotEmpty()) {
                $ultimoPago = $pagos->max('fechaPago');
                $totalAbonos = $pagos->sum('monto');

                // Ajustar el porcentaje para que no supere el 100%
                $porcentajeAbonado = min(100, ($totalAbonos / $valorArriendo) * 100);
            } else {
                // Si no hay pagos, el porcentaje es 0%
                $porcentajeAbonado = 0;
            }

            $pagosMensuales[] = [
                'arrendatario' => $arrendatario,
                'correo' => $correo,
                'local' => $local,
                'porcentaje' => $porcentajeAbonado,
                'valorArriendo' => floatval($valorArriendo),
                'sumaAbonos' => $totalAbonos,
                'ultimoPago' => $ultimoPago,
            ];
        }

        // Retornar los pagos mensuales de los contratos vigentes con la información detallada
        return response()->json($pagosMensuales);
    }



    public function listarPagosMensualesPorRangoFecha($fechaInicio, $fechaFin)
    {
        // Convertir las fechas a objetos Carbon
        $fechaInicio = Carbon::parse($fechaInicio);
        $fechaFin = Carbon::parse($fechaFin);

        // Obtener los pagos dentro del rango de fechas
        $pagos = Pago::whereBetween('fechaPago', [$fechaInicio, $fechaFin])
            ->with('arriendo.persona', 'arriendo.local')
            ->get();

        // Agrupar los pagos por mes y arriendo_id
        $pagosAgrupados = $pagos->groupBy(function ($pago) {
            return Carbon::parse($pago->fechaPago)->format('Y-m');
        })->map(function ($pagosPorMes) {
            return $pagosPorMes->groupBy('arriendo_id');
        });

        // Construir la estructura de datos para el JSON anidado
        $resultado = [];
        foreach ($pagosAgrupados as $mes => $pagosPorMes) {
            foreach ($pagosPorMes as $arriendoId => $pagosPorArriendo) {
                $arrendatario = $pagosPorArriendo->first()->arriendo->persona->nombres . ' ' . $pagosPorArriendo->first()->arriendo->persona->apellidos;
                $local = $pagosPorArriendo->first()->arriendo->local->detalle;
                $valorArriendo = $pagosPorArriendo->first()->arriendo->valorArriendo;

                $totalAbonos = 0;
                $detallePagos = [];

                foreach ($pagosPorArriendo as $pago) {
                    $totalAbonos += $pago->monto;
                    $detallePagos[] = [
                        'fechaPago' => $pago->fechaPago,
                        'monto' => $pago->monto,
                    ];
                }

                $porcentajeAbonado = ($totalAbonos / $valorArriendo) * 100;

                $resultado[] = [
                    'mes' => $mes,
                    'arrendatario' => $arrendatario,
                    'local' => $local,
                    'valorArriendo' => $valorArriendo,
                    'porcentaje' => $porcentajeAbonado,
                    'totalAbonos' => $totalAbonos,
                    'detallePagos' => $detallePagos,
                ];
            }
        }

        // Retornar el resultado como respuesta JSON
        return response()->json($resultado);
    }



    public function store(Request $request)
    {
        // Validar los datos enviados por el formulario
        $validator = Validator::make($request->all(), [
            'arriendo_id' => 'required',
            'user_id' => 'required',
            'monto' => 'required|numeric',
        ]);

        // Verificar si la validación falla y devolver una respuesta JSON con los errores
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Verificar si el usuario tiene abonos en el mes actual
        $abonosMesActual = Pago::where('arriendo_id', $request->arriendo_id)
            ->whereYear('fechaPago', Carbon::now()->year)
            ->whereMonth('fechaPago', Carbon::now()->month)
            ->sum('monto');

        // Obtener el contrato relacionado con el pago
        $contrato = Arriendo::find($request->arriendo_id);

        // Verificar si el pago excede el valor de arriendo o ya ha cubierto el total
        if ($abonosMesActual + $request->monto > $contrato->valorArriendo) {
            return response()->json(['error' => 'El monto del pago excede el valor de arriendo o ya ha cubierto el total.'], 400);
        }

        $currentDateTime = Carbon::now();

        // Crear un nuevo objeto de pago y asignar los valores recibidos del formulario
        $pago = new Pago;
        $pago->arriendo_id = $request->arriendo_id;
        $pago->fechaPago = $currentDateTime->toDateString();
        $pago->user_id = $request->user_id;
        $pago->monto = $request->monto;
        $pago->observacion = $request->observacion;

        // Guardar el pago en la base de datos
        $pago->save();

        // Retornar una respuesta JSON con un mensaje de éxito
        return response()->json(['message' => 'Pago registrado correctamente.'], 201);
    }
}
