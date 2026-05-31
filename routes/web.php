<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

//controladores
use App\Http\Controllers\VentasController;
use App\Http\Controllers\RemuneracionController;
use App\Http\Controllers\AsistenciasController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\TrasladoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

//VENTAS
Route::get('/ventas', [VentasController::class, 'index'])->middleware(['auth', 'role'])->name('ventas');

//Remuneracion
Route::get('/remuneracion', [RemuneracionController::class, 'index'])->middleware(['auth', 'role'])->name('remuneracion');

//Asistencias
Route::get('/asistencias', [AsistenciasController::class, 'index'])->middleware(['auth', 'role'])->name('asistencias');

//RECEPCIÓN DE PRODUCTOS
// web.php — solo la vista
Route::get('/recepcion', [RecepcionController::class, 'index'])->middleware(['auth', 'role:almacen'])->name('recepcion');

//traslado
Route::get('/traslado', [TrasladoController::class, 'index'])->middleware(['auth', 'role:almacen'])->name('traslado');