<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PagoController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// App (autenticado)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Alumnos: admin gestiona todos, padre ve/edita solo los suyos (controlado por Policy)
    Route::resource('alumnos', AlumnoController::class);

    // Pagos: admin registra, padre solo ve los de sus hijos (controlado por Policy)
    Route::resource('pagos', PagoController::class)->only(['index', 'create', 'store', 'show']);
});
