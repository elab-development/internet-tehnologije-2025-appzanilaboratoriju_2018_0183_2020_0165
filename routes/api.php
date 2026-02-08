<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecenzijaController;
use App\Http\Controllers\StavkaRecenzijeController;
use App\Http\Controllers\NaucniRadController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OblastController;

// JAVNE RUTE
Route::post('/login', [AuthController::class, 'login']);

// ZAŠTIĆENE RUTE (Samo za ulogovane)
Route::group(['middleware' => ['auth:sanctum']], function () {
    
    Route::apiResource('users', UserController::class);
    Route::apiResource('recenzije', RecenzijaController::class);
    Route::apiResource('stavke-recenzije', StavkaRecenzijeController::class);
    Route::apiResource('naucniRadovi', NaucniRadController::class);
    Route::apiResource('oblasti', OblastController::class); // I oblasti su sada zaštićene

    Route::post('/logout', [AuthController::class, 'logout']);
});