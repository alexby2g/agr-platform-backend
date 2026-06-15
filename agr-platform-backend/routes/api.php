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

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Solo el dueño de AGR Studio administra empresas, usuarios, módulos y permisos globales.
    Route::middleware('rol.sistema:super_admin')->group(function () {
        Route::apiResource('empresas', EmpresaController::class);

        Route::get('/empresas/{empresa}/modulos', [EmpresaModuloController::class, 'index']);
        Route::post('/empresas/{empresa}/modulos', [EmpresaModuloController::class, 'update']);

        Route::apiResource('usuarios-sistema', UsuarioSistemaController::class);

        Route::get('/usuarios-sistema/{usuario}/modulos', [UsuarioModuloController::class, 'index']);
        Route::post('/usuarios-sistema/{usuario}/modulos', [UsuarioModuloController::class, 'update']);

        Route::apiResource('modulos', ModuloController::class);
    });

    // ==========================
    // AUREA BEAUTY API ORIGINAL CON PERMISOS INTERNOS
    // ==========================

    Route::get('/dashboard', [CitaController::class, 'dashboard'])
        ->middleware('permiso.aurea:aurea.dashboard.ver');

    Route::get('/configuracion', [ConfiguracionController::class, 'index'])
        ->middleware('permiso.aurea:aurea.configuracion.ver');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])
        ->middleware('permiso.aurea:aurea.configuracion.editar');
    Route::post('/configuracion', [ConfiguracionController::class, 'update'])
        ->middleware('permiso.aurea:aurea.configuracion.editar');
    Route::get('/configuracion-publica', [ConfiguracionController::class, 'publica'])
        ->middleware('permiso.aurea:aurea.dashboard.ver');

    Route::get('/clientes', [ClienteController::class, 'index'])
        ->middleware('permiso.aurea:aurea.clientes.ver');
    Route::post('/clientes', [ClienteController::class, 'store'])
        ->middleware('permiso.aurea:aurea.clientes.crear');
    Route::put('/clientes/{id}', [ClienteController::class, 'update'])
        ->middleware('permiso.aurea:aurea.clientes.editar');
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])
        ->middleware('permiso.aurea:aurea.clientes.eliminar');
    Route::get('/clientes/historial/buscar', [ClienteController::class, 'historial'])
        ->middleware('permiso.aurea:aurea.clientes.historial');

    Route::get('/empleados/activos', [EmpleadoController::class, 'activos'])
        ->middleware('permiso.aurea:aurea.empleados.activos');
    Route::get('/empleados/comisiones', [EmpleadoController::class, 'comisiones'])
        ->middleware('permiso.aurea:aurea.empleados.comisiones');
    Route::get('/empleados', [EmpleadoController::class, 'index'])
        ->middleware('permiso.aurea:aurea.empleados.ver');
    Route::post('/empleados', [EmpleadoController::class, 'store'])
        ->middleware('permiso.aurea:aurea.empleados.crear');
    Route::put('/empleados/{id}', [EmpleadoController::class, 'update'])
        ->middleware('permiso.aurea:aurea.empleados.editar');
    Route::delete('/empleados/{id}', [EmpleadoController::class, 'destroy'])
        ->middleware('permiso.aurea:aurea.empleados.eliminar');

    Route::get('/citas', [CitaController::class, 'index'])
        ->middleware('permiso.aurea:aurea.citas.ver');
    Route::post('/citas', [CitaController::class, 'store'])
        ->middleware('permiso.aurea:aurea.citas.crear');
    Route::put('/citas/{id}', [CitaController::class, 'update'])
        ->middleware('permiso.aurea:aurea.citas.editar');
    Route::put('/citas/finalizar/{id}', [CitaController::class, 'finalizar'])
        ->middleware('permiso.aurea:aurea.citas.finalizar');
    Route::put('/citas/{id}/finalizar', [CitaController::class, 'finalizar'])
        ->middleware('permiso.aurea:aurea.citas.finalizar');
    Route::delete('/citas/{id}', [CitaController::class, 'destroy'])
        ->middleware('permiso.aurea:aurea.citas.eliminar');

    Route::get('/pagos', [PagoController::class, 'index'])
        ->middleware('permiso.aurea:aurea.pagos.ver');
    Route::post('/pagos', [PagoController::class, 'store'])
        ->middleware('permiso.aurea:aurea.pagos.crear');
    Route::get('/pagos/historial/{cita_id}', [PagoController::class, 'historial'])
        ->middleware('permiso.aurea:aurea.pagos.ver');
    Route::get('/pagos/factura/{id}', [PagoController::class, 'factura'])
        ->middleware('permiso.aurea:aurea.pagos.factura');
    Route::delete('/pagos/{id}', [PagoController::class, 'destroy'])
        ->middleware('permiso.aurea:aurea.pagos.eliminar');

    Route::get('/caja/diaria', [PagoController::class, 'cajaDiaria'])
        ->middleware('permiso.aurea:aurea.caja.ver');

    Route::get('/servicios', [ServicioController::class, 'index'])
        ->middleware('permiso.aurea:aurea.servicios.ver');
    Route::post('/servicios/cargar-base', [ServicioController::class, 'cargarBase'])
        ->middleware('permiso.aurea:aurea.servicios.crear');
    Route::post('/servicios', [ServicioController::class, 'store'])
        ->middleware('permiso.aurea:aurea.servicios.crear');
    Route::put('/servicios/{id}', [ServicioController::class, 'update'])
        ->middleware('permiso.aurea:aurea.servicios.editar');
    Route::delete('/servicios/{id}', [ServicioController::class, 'destroy'])
        ->middleware('permiso.aurea:aurea.servicios.eliminar');

    Route::get('/notificaciones', [NotificacionController::class, 'index'])
        ->middleware('permiso.aurea:aurea.notificaciones.ver');
    Route::put('/notificaciones/leer-todas', [NotificacionController::class, 'marcarTodasLeidas'])
        ->middleware('permiso.aurea:aurea.notificaciones.editar');
    Route::put('/notificaciones/{id}/leer', [NotificacionController::class, 'marcarLeida'])
        ->middleware('permiso.aurea:aurea.notificaciones.editar');
    Route::delete('/notificaciones/limpiar', [NotificacionController::class, 'limpiar'])
        ->middleware('permiso.aurea:aurea.notificaciones.editar');

    Route::get('/historial/eliminados', [HistorialController::class, 'index'])
        ->middleware('permiso.aurea:aurea.historial.ver');
    Route::put('/historial/clientes/{id}/restaurar', [HistorialController::class, 'restaurarCliente'])
        ->middleware('permiso.aurea:aurea.historial.restaurar');
    Route::put('/historial/citas/{id}/restaurar', [HistorialController::class, 'restaurarCita'])
        ->middleware('permiso.aurea:aurea.historial.restaurar');
    Route::put('/historial/pagos/{id}/restaurar', [HistorialController::class, 'restaurarPago'])
        ->middleware('permiso.aurea:aurea.historial.restaurar');
    Route::put('/historial/restaurar-todo', [HistorialController::class, 'restaurarTodo'])
        ->middleware('permiso.aurea:aurea.historial.restaurar');
    Route::delete('/historial/clientes/{id}/eliminar', [HistorialController::class, 'eliminarClienteDefinitivo'])
        ->middleware('permiso.aurea:aurea.historial.eliminar');
    Route::delete('/historial/citas/{id}/eliminar', [HistorialController::class, 'eliminarCitaDefinitiva'])
        ->middleware('permiso.aurea:aurea.historial.eliminar');
    Route::delete('/historial/pagos/{id}/eliminar', [HistorialController::class, 'eliminarPagoDefinitivo'])
        ->middleware('permiso.aurea:aurea.historial.eliminar');
    Route::delete('/historial/limpiar', [HistorialController::class, 'limpiarHistorial'])
        ->middleware('permiso.aurea:aurea.historial.eliminar');

    Route::get('/reportes/extracto-mensual', [ReporteController::class, 'extractoMensual'])
        ->middleware('permiso.aurea:aurea.reportes.ver');
    Route::get('/reportes/caja-diaria', [ReporteController::class, 'cajaDiaria'])
        ->middleware('permiso.aurea:aurea.reportes.ver');
    Route::get('/reportes/empleados', [ReporteController::class, 'empleados'])
        ->middleware('permiso.aurea:aurea.reportes.ver');

    // ==========================
    // ALIAS PARA FRONTEND SI ALGUNA VISTA LLAMA /api/apps/aurea/...
    // ==========================
    Route::prefix('apps/aurea')->group(function () {
        Route::get('/dashboard', [CitaController::class, 'dashboard'])->middleware('permiso.aurea:aurea.dashboard.ver');
        Route::get('/configuracion', [ConfiguracionController::class, 'index'])->middleware('permiso.aurea:aurea.configuracion.ver');
        Route::put('/configuracion', [ConfiguracionController::class, 'update'])->middleware('permiso.aurea:aurea.configuracion.editar');
        Route::post('/configuracion', [ConfiguracionController::class, 'update'])->middleware('permiso.aurea:aurea.configuracion.editar');
        Route::get('/clientes', [ClienteController::class, 'index'])->middleware('permiso.aurea:aurea.clientes.ver');
        Route::post('/clientes', [ClienteController::class, 'store'])->middleware('permiso.aurea:aurea.clientes.crear');
        Route::put('/clientes/{id}', [ClienteController::class, 'update'])->middleware('permiso.aurea:aurea.clientes.editar');
        Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])->middleware('permiso.aurea:aurea.clientes.eliminar');
        Route::get('/clientes/historial/buscar', [ClienteController::class, 'historial'])->middleware('permiso.aurea:aurea.clientes.historial');
        Route::get('/citas', [CitaController::class, 'index'])->middleware('permiso.aurea:aurea.citas.ver');
        Route::post('/citas', [CitaController::class, 'store'])->middleware('permiso.aurea:aurea.citas.crear');
        Route::put('/citas/{id}', [CitaController::class, 'update'])->middleware('permiso.aurea:aurea.citas.editar');
        Route::put('/citas/finalizar/{id}', [CitaController::class, 'finalizar'])->middleware('permiso.aurea:aurea.citas.finalizar');
        Route::delete('/citas/{id}', [CitaController::class, 'destroy'])->middleware('permiso.aurea:aurea.citas.eliminar');
        Route::get('/pagos', [PagoController::class, 'index'])->middleware('permiso.aurea:aurea.pagos.ver');
        Route::post('/pagos', [PagoController::class, 'store'])->middleware('permiso.aurea:aurea.pagos.crear');
        Route::get('/servicios', [ServicioController::class, 'index'])->middleware('permiso.aurea:aurea.servicios.ver');
        Route::post('/servicios', [ServicioController::class, 'store'])->middleware('permiso.aurea:aurea.servicios.crear');
        Route::put('/servicios/{id}', [ServicioController::class, 'update'])->middleware('permiso.aurea:aurea.servicios.editar');
        Route::delete('/servicios/{id}', [ServicioController::class, 'destroy'])->middleware('permiso.aurea:aurea.servicios.eliminar');
        Route::get('/empleados', [EmpleadoController::class, 'index'])->middleware('permiso.aurea:aurea.empleados.ver');
        Route::get('/empleados/activos', [EmpleadoController::class, 'activos'])->middleware('permiso.aurea:aurea.empleados.activos');
        Route::get('/empleados/comisiones', [EmpleadoController::class, 'comisiones'])->middleware('permiso.aurea:aurea.empleados.comisiones');
        Route::post('/empleados', [EmpleadoController::class, 'store'])->middleware('permiso.aurea:aurea.empleados.crear');
        Route::put('/empleados/{id}', [EmpleadoController::class, 'update'])->middleware('permiso.aurea:aurea.empleados.editar');
        Route::delete('/empleados/{id}', [EmpleadoController::class, 'destroy'])->middleware('permiso.aurea:aurea.empleados.eliminar');
        Route::get('/caja/diaria', [PagoController::class, 'cajaDiaria'])->middleware('permiso.aurea:aurea.caja.ver');
        Route::get('/historial/eliminados', [HistorialController::class, 'index'])->middleware('permiso.aurea:aurea.historial.ver');
        Route::get('/reportes/caja-diaria', [ReporteController::class, 'cajaDiaria'])->middleware('permiso.aurea:aurea.reportes.ver');
        Route::get('/reportes/empleados', [ReporteController::class, 'empleados'])->middleware('permiso.aurea:aurea.reportes.ver');
    });
});
