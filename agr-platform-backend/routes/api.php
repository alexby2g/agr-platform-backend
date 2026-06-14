<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\EmpresaModuloController;
use App\Http\Controllers\Api\UsuarioSistemaController;
use App\Http\Controllers\Api\UsuarioModuloController;
use App\Http\Controllers\Api\ModuloController;

// AUREA BEAUTY
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\CitaController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\HistorialController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\ReporteController;

/*
|--------------------------------------------------------------------------
| API Routes - AGR Studio Platform + AUREA Beauty
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // ==========================
    // AUTH / AGR PLATFORM
    // ==========================
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/launcher', [AuthController::class, 'launcher']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('empresas', EmpresaController::class);

    Route::get('/empresas/{empresa}/modulos', [EmpresaModuloController::class, 'index']);
    Route::post('/empresas/{empresa}/modulos', [EmpresaModuloController::class, 'update']);

    Route::apiResource('usuarios-sistema', UsuarioSistemaController::class);

    Route::get('/usuarios-sistema/{usuario}/modulos', [UsuarioModuloController::class, 'index']);
    
    Route::post('/usuarios-sistema/{usuario}/modulos', [UsuarioModuloController::class, 'update']);
    
    Route::apiResource('modulos', ModuloController::class);

    Route::get('/user', function (Request $request) {
        return $request->user();

    });

    // ==========================
    // AUREA BEAUTY API ORIGINAL
    // ==========================

    Route::get('/dashboard', [CitaController::class, 'dashboard']);

    Route::get('/configuracion', [ConfiguracionController::class, 'index']);
    Route::put('/configuracion', [ConfiguracionController::class, 'update']);
    Route::post('/configuracion', [ConfiguracionController::class, 'update']);
    Route::get('/configuracion-publica', [ConfiguracionController::class, 'publica']);

    Route::get('/clientes', [ClienteController::class, 'index']);
    Route::post('/clientes', [ClienteController::class, 'store']);
    Route::put('/clientes/{id}', [ClienteController::class, 'update']);
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);
    Route::get('/clientes/historial/buscar', [ClienteController::class, 'historial']);

    Route::get('/empleados/activos', [EmpleadoController::class, 'activos']);
    Route::get('/empleados/comisiones', [EmpleadoController::class, 'comisiones']);
    Route::get('/empleados', [EmpleadoController::class, 'index']);
    Route::post('/empleados', [EmpleadoController::class, 'store']);
    Route::put('/empleados/{id}', [EmpleadoController::class, 'update']);
    Route::delete('/empleados/{id}', [EmpleadoController::class, 'destroy']);

    Route::get('/citas', [CitaController::class, 'index']);
    Route::post('/citas', [CitaController::class, 'store']);
    Route::put('/citas/{id}', [CitaController::class, 'update']);
    Route::put('/citas/finalizar/{id}', [CitaController::class, 'finalizar']);
    Route::put('/citas/{id}/finalizar', [CitaController::class, 'finalizar']);
    Route::delete('/citas/{id}', [CitaController::class, 'destroy']);

    Route::get('/pagos', [PagoController::class, 'index']);
    Route::post('/pagos', [PagoController::class, 'store']);
    Route::get('/pagos/historial/{cita_id}', [PagoController::class, 'historial']);
    Route::get('/pagos/factura/{id}', [PagoController::class, 'factura']);
    Route::delete('/pagos/{id}', [PagoController::class, 'destroy']);

    Route::get('/caja/diaria', [PagoController::class, 'cajaDiaria']);

    Route::get('/servicios', [ServicioController::class, 'index']);
    Route::post('/servicios/cargar-base', [ServicioController::class, 'cargarBase']);
    Route::post('/servicios', [ServicioController::class, 'store']);
    Route::put('/servicios/{id}', [ServicioController::class, 'update']);
    Route::delete('/servicios/{id}', [ServicioController::class, 'destroy']);

    Route::get('/notificaciones', [NotificacionController::class, 'index']);
    Route::put('/notificaciones/leer-todas', [NotificacionController::class, 'marcarTodasLeidas']);
    Route::put('/notificaciones/{id}/leer', [NotificacionController::class, 'marcarLeida']);
    Route::delete('/notificaciones/limpiar', [NotificacionController::class, 'limpiar']);

    Route::get('/historial/eliminados', [HistorialController::class, 'index']);
    Route::put('/historial/clientes/{id}/restaurar', [HistorialController::class, 'restaurarCliente']);
    Route::put('/historial/citas/{id}/restaurar', [HistorialController::class, 'restaurarCita']);
    Route::put('/historial/pagos/{id}/restaurar', [HistorialController::class, 'restaurarPago']);
    Route::put('/historial/restaurar-todo', [HistorialController::class, 'restaurarTodo']);
    Route::delete('/historial/clientes/{id}/eliminar', [HistorialController::class, 'eliminarClienteDefinitivo']);
    Route::delete('/historial/citas/{id}/eliminar', [HistorialController::class, 'eliminarCitaDefinitiva']);
    Route::delete('/historial/pagos/{id}/eliminar', [HistorialController::class, 'eliminarPagoDefinitivo']);
    Route::delete('/historial/limpiar', [HistorialController::class, 'limpiarHistorial']);

    Route::get('/reportes/extracto-mensual', [ReporteController::class, 'extractoMensual']);
    Route::get('/reportes/caja-diaria', [ReporteController::class, 'cajaDiaria']);
    Route::get('/reportes/empleados', [ReporteController::class, 'empleados']);

    // ==========================
    // ALIAS DE SEGURIDAD PARA FRONTEND SI ALGUNA VISTA LLAMA /api/apps/aurea/...
    // ==========================
    Route::prefix('apps/aurea')->group(function () {
        Route::get('/dashboard', [CitaController::class, 'dashboard']);
        Route::get('/configuracion', [ConfiguracionController::class, 'index']);
        Route::put('/configuracion', [ConfiguracionController::class, 'update']);
        Route::post('/configuracion', [ConfiguracionController::class, 'update']);
        Route::get('/clientes', [ClienteController::class, 'index']);
        Route::post('/clientes', [ClienteController::class, 'store']);
        Route::put('/clientes/{id}', [ClienteController::class, 'update']);
        Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);
        Route::get('/clientes/historial/buscar', [ClienteController::class, 'historial']);
        Route::get('/citas', [CitaController::class, 'index']);
        Route::post('/citas', [CitaController::class, 'store']);
        Route::put('/citas/{id}', [CitaController::class, 'update']);
        Route::put('/citas/finalizar/{id}', [CitaController::class, 'finalizar']);
        Route::delete('/citas/{id}', [CitaController::class, 'destroy']);
        Route::get('/pagos', [PagoController::class, 'index']);
        Route::post('/pagos', [PagoController::class, 'store']);
        Route::get('/servicios', [ServicioController::class, 'index']);
        Route::post('/servicios', [ServicioController::class, 'store']);
        Route::put('/servicios/{id}', [ServicioController::class, 'update']);
        Route::delete('/servicios/{id}', [ServicioController::class, 'destroy']);
        Route::get('/empleados', [EmpleadoController::class, 'index']);
        Route::get('/empleados/activos', [EmpleadoController::class, 'activos']);
        Route::get('/empleados/comisiones', [EmpleadoController::class, 'comisiones']);
        Route::post('/empleados', [EmpleadoController::class, 'store']);
        Route::put('/empleados/{id}', [EmpleadoController::class, 'update']);
        Route::delete('/empleados/{id}', [EmpleadoController::class, 'destroy']);
        Route::get('/caja/diaria', [PagoController::class, 'cajaDiaria']);
        Route::get('/historial/eliminados', [HistorialController::class, 'index']);
        Route::get('/reportes/caja-diaria', [ReporteController::class, 'cajaDiaria']);
        Route::get('/reportes/empleados', [ReporteController::class, 'empleados']);
    });
});
