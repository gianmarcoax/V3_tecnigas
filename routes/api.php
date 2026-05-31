<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RemuneracionController;
use App\Http\Controllers\EmpleadoController;

//Controladores
use App\Http\Controllers\VentasController;
use App\Http\Controllers\AsistenciasController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\TrasladoController;

// Empleados
Route::get('/empleados',        [EmpleadoController::class, 'index']);
Route::post('/empleados/sync',  [EmpleadoController::class, 'sync']);
Route::put('/empleados/{employee}', [EmpleadoController::class, 'update']);

// Remuneración
Route::get('/remuneracion/config',           [RemuneracionController::class, 'config']);
Route::get('/remuneracion/empleados',        [RemuneracionController::class, 'empleados']);
Route::get('/remuneracion/justificaciones',  [RemuneracionController::class, 'justificaciones']);
Route::post('/remuneracion/salarios',        [RemuneracionController::class, 'saveSalarios']);
Route::post('/remuneracion/metas',           [RemuneracionController::class, 'saveMetas']);
Route::post('/remuneracion/justificacion',   [RemuneracionController::class, 'saveJustificacion']);

//ventas
Route::get('/ventas/ranking', [VentasController::class, 'ranking']);
Route::get('/ventas/detail',  [VentasController::class, 'detail']);
Route::get('/ventas/export',  [VentasController::class, 'export']);
Route::get('/ventas/debug',   [VentasController::class, 'debug']); // ← TEMPORAL diagnóstico

//Remuneración
Route::get('/remuneracion/semana',  [RemuneracionController::class, 'semana']);
Route::get('/remuneracion/detalle', [RemuneracionController::class, 'detalle']);

// Nómina mensual
Route::get('/nomina/mes',       [RemuneracionController::class, 'nominaMes']);
Route::post('/nomina/entrega',  [RemuneracionController::class, 'markDelivery']);
Route::get('/nomina/emp-mes',   [RemuneracionController::class, 'empMes']);

// Orden y Limpieza
Route::get('/orden-limpieza/config',  [RemuneracionController::class, 'olConfig']);
Route::post('/orden-limpieza/config', [RemuneracionController::class, 'saveOlConfig']);
Route::get('/orden-limpieza/scores',  [RemuneracionController::class, 'olScores']);
Route::post('/orden-limpieza/score',  [RemuneracionController::class, 'saveOlScore']);


//Asistencias
Route::get('/asistencias/vivo',                              [AsistenciasController::class, 'vivo']);
Route::get('/asistencias/semana',                            [AsistenciasController::class, 'semana']);
Route::get('/asistencias/horarios',                          [AsistenciasController::class, 'horarios']);
Route::get('/asistencias/empleados-calendario',              [AsistenciasController::class, 'empleadosConCalendario']);
Route::get('/asistencias/calendarios/{calendarId}/lineas',   [AsistenciasController::class, 'calendarioLineas']);
Route::put('/asistencias/horarios/{lineId}',                 [AsistenciasController::class, 'updateHorario']);
Route::post('/asistencias/horarios/{calendarId}/lineas',     [AsistenciasController::class, 'createHorarioLine']);
Route::delete('/asistencias/horarios/lineas/{lineId}',       [AsistenciasController::class, 'deleteHorarioLine']);

// RECEPCIÓN DE PRODUCTOS
Route::get('/recepcion/resumen',      [RecepcionController::class, 'resumen']);
Route::get('/recepcion/historial',    [RecepcionController::class, 'historial']);
Route::get('/recepcion/productos',    [RecepcionController::class, 'productos']);
Route::get('/recepcion/proveedores',  [RecepcionController::class, 'proveedores']);
Route::get('/recepcion/ubicaciones',  [RecepcionController::class, 'ubicaciones']);
Route::get('/recepcion/export-bartender', [RecepcionController::class, 'exportBartender']);
Route::get('/recepcion/imagenes',         [RecepcionController::class, 'imagenes']);
Route::get('/recepcion/{id}',             [RecepcionController::class, 'show']);
Route::post('/recepcion',             [RecepcionController::class, 'store']);
Route::put('/recepcion/{id}',         [RecepcionController::class, 'update']);
Route::delete('/recepcion/{id}',      [RecepcionController::class, 'destroy']);

//traslado
Route::get('/traslado/productos',  [TrasladoController::class, 'productos']);
Route::get('/traslado/almacenes',  [TrasladoController::class, 'almacenes']);
Route::get('/traslado/historial',  [TrasladoController::class, 'historial']);
Route::get('/traslado/resumen',    [TrasladoController::class, 'resumen']);
Route::get('/traslado/imagenes',   [TrasladoController::class, 'imagenes']);
Route::get('/traslado/{id}',       [TrasladoController::class, 'show']);
Route::post('/traslado',           [TrasladoController::class, 'store']);
Route::post('/traslado/{id}/confirm', [TrasladoController::class, 'confirm']);
Route::put('/traslado/{id}',       [TrasladoController::class, 'update']);
Route::delete('/traslado/{id}',    [TrasladoController::class, 'destroy']);
