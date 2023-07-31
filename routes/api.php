<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\TipoVehiculoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ArriendoController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\CobroController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\PermissionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */

Route::post('/login', [AuthController::class, 'login']);
Route::get('/menu/{id}', [UserController::class, 'getUserMenusAndPermissions']);
Route::get('user/role/{id}', [RoleController::class, 'roleByUser']);

Route::get('/clientes', [ClienteController::class, 'listarClientes']);
Route::post('/clientes', [ClienteController::class, 'crearCliente']);
Route::put('/clientes/{clienteId}', [ClienteController::class, 'editarCliente']);
Route::delete('/clientes/{clienteId}', [ClienteController::class, 'eliminarCliente']);

Route::post('clientes/{cliente}/vehiculos', [VehiculoController::class, 'asignarVehiculo']);
Route::put('clientes/{cliente}/vehiculos/{vehiculo}', [VehiculoController::class, 'editarVehiculo']);
Route::delete('clientes/{cliente}/vehiculos/{vehiculo}', [VehiculoController::class, 'eliminarVehiculo']);

Route::get('/clientes/existe/cedula/{identificacion}', [ClienteController::class, 'verificarExitenciaCedula']);
Route::get('/clientes/existe/ruc/{identificacion}', [ClienteController::class, 'verificarExistenciaRuc']);

Route::get('/vehiculos/{placa}', [VehiculoController::class, 'verificarExistencia']);
Route::get('/vehiculos/datos/{placa}', [VehiculoController::class, 'obtenerDatosPorPlaca']);


Route::get('/tiposvehiculos', [TipoVehiculoController::class, 'listarTipos']);
Route::post('/tiposvehiculos', [TipoVehiculoController::class, 'create']);
Route::put('/tiposvehiculos/{tipoId}', [TipoVehiculoController::class, 'update']);
Route::delete('/tiposvehiculos/{tipoId}', [TipoVehiculoController::class, 'destroy']);


Route::get('/roles', [RoleController::class, 'index']);
Route::post('/roles', [RoleController::class, 'store']);
Route::put('/roles/{id}', [RoleController::class, 'update']);
Route::post('/roles_asignacion', [RoleController::class, 'assignRole']);


Route::get('/menu-opciones', [PermissionController::class, 'listPermission']);


/* Route::get('menus', [MenuController::class, 'index']);
Route::post('menus', [MenuController::class, 'store']);
Route::get('menus/{id}', [MenuController::class, 'show']);
Route::put('menus/{id}', [MenuController::class, 'update']);
Route::delete('menus/{id}', [MenuController::class, 'destroy']); */


Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'eliminarUsuario']);


Route::get('/contratos', [ContratoController::class, 'index']);
Route::get('/users/sincontratos', [ContratoController::class, 'usersWithoutContract']);
Route::post('/contratos', [ContratoController::class, 'store']);
Route::delete('contratos/{id}', [ContratoController::class, 'destroy']);


Route::get('/personas/buscar/{ced}', [ArriendoController::class, 'buscarPersona']);
Route::get('/arriendos/locales', [ArriendoController::class, 'listarLocales']);
Route::get('/arriendos', [ArriendoController::class, 'listarArriendos']);
Route::get('/arriendos/contratos', [ArriendoController::class, 'listarContratosActivos']);
Route::post('/arriendos', [ArriendoController::class, 'registrarArriendo']);
Route::put('/arriendos/{id}', [ArriendoController::class, 'index']);
Route::delete('/arriendos/{id}', [ArriendoController::class, 'eliminarArriendo']);

Route::post('arriendos/pagos', [PagoController::class, 'store']);
Route::get('arriendos/pagos/mensuales', [PagoController::class, 'listarPagosMensuales']);
Route::post('/enviar-correo', [MailController::class, 'enviarCorreo']);


Route::get('/turnos/{turno_id}', [TurnoController::class, 'listarTotales']);
Route::post('/turnos/apertura', [TurnoController::class, 'aperturar']);
Route::post('/turnos/cierre/{turno_id}', [TurnoController::class, 'cerrarTurno']);
Route::post('/turnos/abierto', [TurnoController::class, 'turnoAbierto']);

Route::post('/cobro', [CobroController::class, 'store']);


Route::get('/reporte/garita/{inicio}/{fin}', [CobroController::class, 'reportePorFechas']);
Route::get('/reporte/historial/{inicio}/{fin}', [CobroController::class, 'historialPorFechas']);
Route::get('reporte/pagos/locales/{inicio}/{fin}', [PagoController::class, 'listarPagosMensualesPorRangoFecha']);


Route::get('/logo', [ImageController::class, 'show']);
Route::put('/logo/{id}', [ImageController::class, 'update']);


Route::get('/dashboard', [DashboardController::class, 'obtenerInformacionDashboard']);