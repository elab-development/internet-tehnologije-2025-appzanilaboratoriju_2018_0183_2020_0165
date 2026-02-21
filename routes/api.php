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
    
    
    //Metode dostupne svakome ko je ulogovan
    Route::post('/logout', [AuthController::class, 'logout']); //
    Route::get('/oblasti', [OblastController::class, 'index']); //Primena?
    
    //ADMINISTRATOR FUNKCIJE
    Route::middleware('role:Administrator')->group(function () {

        Route::get('/admin/korisnici', [UserController::class, 'index']); //Vidi sve korisnike
        Route::get('/admin/korisnici/{id}', [UserController::class, 'show']); //Vidi posebnog korisnika preko ID-a
        Route::put('/admin/korisnici/{id}', [UserController::class, 'update']); //Update profila korisnika
        Route::post('/admin/dodeli-ulogu', [UserController::class, 'dodeliUlogu']); //Izmena Uloge na nekom profilu
        Route::get('/naucniRadovi', [NaucniRadController::class, 'index']); //Prikaz svih naučnih radova, može i pretraga

    });

    //ISTRAŽIVAČ FUNKCIJE
    Route::middleware('role:Istraživač')->group(function () {

        Route::post('/radovi/slanje', [NaucniRadController::class, 'store']); // Kreiranje rada, ovde ćemo ubaciti nasumičnu dodelu recenzenta.
        Route::get('/mojiRadovi', [NaucniRadController::class, 'mojiRadovi']); // Vidi svoje radove nezavisno od statusa.
        Route::get('/rad/{id}/recenzija', [NaucniRadController::class, 'prikaziRecenziju']); // Vidi recenziju

    });

    //RECENZENT FUNKCIJE
    Route::middleware('role:Recenzent')->group(function () {
        Route::get('/radovi/za-recenziju', [RecenzijaController::class, 'dodeljeniRadovi']); //Recenzent vidi sve radove koji su mi pridodati
        Route::post('/radovi/oceni', [RecenzijaController::class, 'sacuvajStavkuRecenzije']);  //Sacuvaj stavku Recenzije
    });
});