<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecenzijaController;
use App\Http\Controllers\StavkaRecenzijeController;
use App\Http\Controllers\NaucniRadController;
use App\Http\Controllers\AuthController;

// JAVNE RUTE (Dostupne svima)
Route::post('/login', [AuthController::class, 'login']);

// ZAŠTIĆENE RUTE (Zahtev 10 - samo za ulogovane korisnike)
Route::group(['middleware' => ['auth:sanctum']], function () {
    
    // Tvoji postojeći resursi su sada zaštićeni
    Route::apiResource('users', UserController::class);
    Route::apiResource('recenzije', RecenzijaController::class);
    Route::apiResource('stavke-recenzije', StavkaRecenzijeController::class);
    Route::apiResource('naucniRadovi', NaucniRadController::class);

    // Logout funkcija koju imaju samo ulogovani
    Route::post('/logout', [AuthController::class, 'logout']);
});