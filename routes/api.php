<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OblastController;
use App\Http\Controllers\NaucniRadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecenzijaController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
//1. Ovde mora da se doda poseban route za gledanje svih objavljenih radova url ?status=objavljen



Route::middleware('auth:sanctum')->group(function () {
    
    
    //Javne metode, dostupne svakome
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/oblasti', [OblastController::class, 'index']); //Primena?
    
    
    Route::middleware('role:Administrator')->group(function () {
        Route::get('/admin/korisnici', [UserController::class, 'index']); //Vidi sve korisnike
        Route::get('/admin/korisnici/{id}', [UserController::class, 'show']); //Vidi posebnog korisnika preko ID-a
        Route::put('/admin/korisnici/{id}', [UserController::class, 'update']); //Update profila korisnika
        Route::post('/admin/dodeli-ulogu', [UserController::class, 'dodeliUlogu']); //Izmena Uloge na nekom profilu
        Route::get('/naucniRadovi', [NaucniRadController::class, 'index']); //Prikaz svih naučnih radova, može i pretraga
    });


    Route::middleware('role:Istraživač')->group(function () {
        Route::post('/radovi/slanje', [NaucniRadController::class, 'store']); // Ovde ćemo ubaciti nasumičnu dodelu recenzenta 
        Route::get('/mojiRadovi', [NaucniRadController::class, 'mojiRadovi']); // Vidi svoje radove nezavisno od statusa
        Route::get('/rad/{id}/recenzija', [NaucniRadController::class, 'prikaziRecenziju']); // Vidi recenziju
    });


    Route::middleware('role:Recenzent')->group(function () {
        Route::get('/radovi/za-recenziju', [RecenzijaController::class, 'dodeljeniRadovi']);
        Route::post('/radovi/oceni', [RecenzijaController::class, 'sacuvajOcenu']); // Popunjava stavke i menja status rada
    });
});