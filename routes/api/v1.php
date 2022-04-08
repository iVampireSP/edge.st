<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;


// Product
Route::group(['prefix' => 'products', 'as' => 'product.'], function () {
    Route::get('/', [Controllers\Product\IndexController::class, 'index'])->name('index');
    Route::get('/{products}', [Controllers\Product\IndexController::class, 'show'])->name('show');
    Route::post('/{products}', [Controllers\Product\IndexController::class, 'store'])->name('store');
});


// Billing
Route::group(['prefix' => 'billing', 'as' => 'billings.'], function () {
    Route::get('/orders', [Controllers\Billing\OrderController::class, 'index'])->name('index');
    Route::get('/orders/{order_id}', [Controllers\Billing\OrderController::class, 'show'])->name('show');
    Route::post('/orders/{order_id}', [Controllers\Billing\OrderController::class, 'show'])->name('pay');
    Route::delete('/orders/{order_id}', [Controllers\Billing\OrderController::class, 'cancel'])->name('cancel');

    // invoices
    Route::get('/invoices', [Controllers\Billing\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice_id}', [Controllers\Billing\InvoiceController::class, 'show'])->name('invoices.show');
});

// User
Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
    Route::get('/tasks', [Controllers\User\TaskController::class, 'index'])->name('index');
});

// TunnelController
// Route::group(['prefix' => 'tunnel', 'as' => 'tunnel',],function () { 
//     Route::get('/', Controllers\Network\TunnelController::class, 'index')->name('index');
//     Route::post('/', Controllers\Network\TunnelController::class, 'store')->name('store');
//     Route::get('/{tunnel}', Controllers\Network\TunnelController::class, 'show')->name('show');
//     Route::delete('/{tunnel}', Controllers\Network\TunnelController::class, 'destroy')->name('destroy');
// });

// Pterodactyl Server API
Route::group(['prefix' => 'pterodactyl', 'as' => 'pterodactyl.'], function () {
    Route::get('/', [Controllers\Pterodactyl\ServerController::class, 'index'])->name('index');
    Route::get('/create', [Controllers\Pterodactyl\ServerController::class, 'create'])->name('create');
    Route::post('/', [Controllers\Pterodactyl\ServerController::class, 'store'])->name('store');
    Route::get('/{service}', [Controllers\Pterodactyl\ServerController::class, 'show'])->name('show');
    Route::delete('/{service}', [Controllers\Pterodactyl\ServerController::class, 'destroy'])->name('destroy');

});

