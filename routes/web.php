<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CarneController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EscaneoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\UsuarioController;
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

    // Carné QR: mismo control de acceso que ver al alumno (AlumnoPolicy@view)
    Route::get('alumnos/{alumno}/carne', [CarneController::class, 'download'])->name('alumnos.carne');

    // Escaneo + Asistencias: solo profesor y admin
    Route::middleware('role:profesor|admin|superadmin')->group(function () {
        Route::get('escaneo', [EscaneoController::class, 'pantalla'])->name('escaneo.index');
        Route::post('escaneo', [EscaneoController::class, 'escanear'])->name('escaneo.escanear');
        Route::get('asistencias/hoy', [AsistenciaController::class, 'hoy'])->name('asistencias.hoy');

        // Sincronización offline (Fase 5): cache para escanear sin conexión
        Route::get('sync/descargar', [SyncController::class, 'descargar'])->name('sync.descargar');
        Route::post('sync/subir', [SyncController::class, 'subir'])->name('sync.subir');
    });

    // Reportes: solo admin
    Route::middleware('role:admin|superadmin')->prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteController::class, 'dashboard'])->name('dashboard');
        Route::get('/deudores', [ReporteController::class, 'deudores'])->name('deudores');
        Route::get('/deudores/exportar', [ReporteController::class, 'deudoresExport'])->name('deudores.export');
        Route::get('/asistencias', [ReporteController::class, 'asistencias'])->name('asistencias');
        Route::get('/asistencias/exportar', [ReporteController::class, 'asistenciasExport'])->name('asistencias.export');
        Route::get('/ingresos', [ReporteController::class, 'ingresos'])->name('ingresos');
        Route::get('/ingresos/exportar', [ReporteController::class, 'ingresosExport'])->name('ingresos.export');
    });

    // Gestión de usuarios: admin crea profesores, superadmin gestiona a todos (UserPolicy)
    Route::middleware('role:admin|superadmin')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
    });
});
