<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\User\InventoryController;
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\User\SaleController;

Route::prefix('usuario')->name('usuario.')->middleware(['auth', 'verified', 'role:user'])->group(function() {
    Route::get('inicio', [HomeController::class, 'users'])->name('inicio');
    Route::get('productos', [ProductController::class, 'index'])->name('productos');
    Route::get('inventario', [InventoryController::class, 'index'])->name('inventario');
});

Route::prefix('user')->middleware('auth')->group(function () {
    Route::get('products', [ProductController::class, 'products']);
    Route::post('product', [ProductController::class, 'saveProduct']);
    Route::put('product', [ProductController::class, 'editProduct']);
    Route::delete('product/{id}', [ProductController::class, 'deleteProduct']);
    Route::get('inventories', [InventoryController::class, 'inventories']);
    Route::post('inventory', [InventoryController::class, 'saveInventory']);
    Route::put('inventory', [InventoryController::class, 'editInventory']);
    Route::delete('inventory/{id}', [InventoryController::class, 'deleteInventory']);
    Route::post('registerSale', [SaleController::class, 'registerSale']);
});