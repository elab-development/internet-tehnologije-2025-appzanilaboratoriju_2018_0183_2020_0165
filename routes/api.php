<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecenzijaController;
use App\Http\Controllers\StavkaRecenzijeController;
use App\Http\Controllers\NaucniRadController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OblastController;

// --- JAVNE RUTE (Dostupne svima) ---
Route::post('/login', [AuthController::class, 'login']);
Route::get('/naucniRadovi', [NaucniRadController::class, 'index']);



// --- ZAŠTIĆENE RUTE ---
Route::group(['middleware' => ['auth:sanctum']], function () {
    

    Route::apiResource('naucniRadovi', NaucniRadController::class)->except(['index', 'show']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('recenzije', RecenzijaController::class);
    Route::apiResource('stavke-recenzije', StavkaRecenzijeController::class);
    Route::apiResource('oblasti', OblastController::class);

    Route::post('/logout', [AuthController::class, 'logout']);
});