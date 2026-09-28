<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Web de Algoritmo Framework
|--------------------------------------------------------------------------
|
| Todas las rutas siguen el estándar RESTful y la arquitectura oficial:
| FormRequest -> Controller -> ADO -> BLL -> DAL -> DB
|
*/

// Asistente de Instalación y Conexión Web (tipo Moodle)
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('index');
    Route::get('/database', [InstallController::class, 'databaseForm'])->name('database');
    Route::post('/database/test', [InstallController::class, 'testDatabase'])->name('database.test');
    Route::post('/database', [InstallController::class, 'saveDatabase'])->name('database.save');
    Route::get('/setup', [InstallController::class, 'setupForm'])->name('setup');
    Route::post('/setup', [InstallController::class, 'executeSetup'])->name('setup.execute');
    Route::get('/finish', [InstallController::class, 'finish'])->name('finish');
});

// Rutas Públicas / Autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Rutas Protegidas bajo Autenticación
Route::middleware('auth')->group(function () {
    // Dashboard Central
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', fn() => view('dashboard.index'))->name('dashboard');

    // Módulo Empresas
    Route::get('/empresas/datatable', [EmpresaController::class, 'dataTable'])->name('empresas.datatable');
    Route::patch('/empresas/{empresa}/toggle-status', [EmpresaController::class, 'toggleStatus'])->name('empresas.toggleStatus');
    Route::resource('empresas', EmpresaController::class);

    // Módulo Usuarios
    Route::get('/usuarios/datatable', [UsuarioController::class, 'dataTable'])->name('usuarios.datatable');
    Route::patch('/usuarios/{usuario}/toggle-status', [UsuarioController::class, 'toggleStatus'])->name('usuarios.toggleStatus');
    Route::get('/usuarios/{usuario}/cambiar-password', [UsuarioController::class, 'showCambiarPassword'])->name('usuarios.cambiar-password');
    Route::post('/usuarios/{usuario}/cambiar-password', [UsuarioController::class, 'cambiarPassword'])->name('usuarios.cambiar-password.post');
    Route::resource('usuarios', UsuarioController::class);
});