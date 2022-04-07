<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::group(['prefix' => 'oauth', 'as' => 'auth.'], function () {
    Route::get('redirect', [AuthController::class, 'redirect'])->name('redirect');
    Route::get('callback', [AuthController::class, 'callback'])->name('callback');
});

Route::get('/', [AuthController::class, 'index'])->middleware(['auth'])->name('index');
