<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\UserController;

Route::prefix('administrador')->name('administrador.')->middleware(['auth', 'verified', 'role:admin'])->group(function() {
    Route::get('inicio', [HomeController::class, 'stores'])->name('inicio');
    Route::get('sucursales', [StoreController::class, 'index'])->name('sucursales');
    Route::get('usuarios', [UserController::class, 'index'])->name('usuarios');
});

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('stores', [StoreController::class, 'stores']);
    Route::post('store', [StoreController::class, 'saveStore']);
    Route::put('store', [StoreController::class, 'editStore']);
    Route::delete('store/{id}', [StoreController::class, 'deleteStore']);
    Route::get('users', [UserController::class, 'users']);
    Route::post('user', [UserController::class, 'saveUser']);
    Route::put('user', [UserController::class, 'editUser']);
    Route::delete('user/{id}', [UserController::class, 'deleteUser']);
});