<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OblastController;
use App\Http\Controllers\RadController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RecenzijaController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {
    

    Route::post('/logout', [AuthController::class, 'logout']);

 
    Route::get('/oblasti', [OblastController::class, 'index']);


    Route::middleware('role:Admin')->group(function () {
        Route::get('/admin/korisnici', [AdminController::class, 'index']);
        Route::put('/admin/korisnici/{id}', [AdminController::class, 'update']);
        Route::post('/admin/dodeli-ulogu', [AdminController::class, 'dodeliUlogu']);
        Route::get('/admin/svi-radovi', [RadController::class, 'index']); // Vidi sve radove
    });


    Route::middleware('role:Istrazivac')->group(function () {
        Route::post('/radovi/slanje', [RadController::class, 'store']); // Ovde ćemo ubaciti nasumičnu dodelu
        Route::get('/moji-radovi', [RadController::class, 'mojiRadovi']); // Vidi statuse svojih radova
        Route::get('/rad/{id}/recenzija', [RadController::class, 'prikaziRecenziju']); // Vidi dobijenu ocenu
    });


    Route::middleware('role:Recenzent')->group(function () {
        Route::get('/radovi/za-recenziju', [RecenzijaController::class, 'dodeljeniRadovi']);
        Route::post('/radovi/oceni', [RecenzijaController::class, 'sacuvajOcenu']); // Popunjava stavke i menja status rada
    });
});