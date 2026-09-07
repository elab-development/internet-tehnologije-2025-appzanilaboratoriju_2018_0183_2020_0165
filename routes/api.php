<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OblastController;
use App\Http\Controllers\NaucniRadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecenzijaController;


Route::post('/prijava', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/oblasti', [OblastController::class, 'index']); //Dobra primena da bi korisnik video koje sve oblasti mi imamo u bazi
Route::get('/radovi/objavljeni', [NaucniRadController::class, 'objavljeniRadovi']); //Prikaz svih radova koji su objavljeni (Dostupni posetiocu)



Route::middleware('auth:sanctum')->group(function () {


    //Metode dostupne svakome ko je ulogovan
    Route::post('/odjava', [AuthController::class, 'logout']); //

    //ADMINISTRATOR FUNKCIJE
    Route::middleware('role:Administrator')->group(function () {

        Route::post('/admin/korisnici', [AuthController::class, 'register'])->middleware('throttle:10,1'); //Registruj novog korisnika
        Route::get('/admin/korisnici', [UserController::class, 'index']); //Vidi sve korisnike
        Route::get('/admin/korisnici/{id}', [UserController::class, 'show']); //Vidi posebnog korisnika preko ID-a
        Route::put('/admin/korisnici/{id}', [UserController::class, 'update']); //Update profila korisnika
        Route::put('/admin/korisnici/{id}/uloge', [UserController::class, 'dodeliUlogu']); //Izmena Uloge na nekom profilu
        Route::get('/radovi', [NaucniRadController::class, 'index']); //Prikaz svih naučnih radova, može i pretraga

    });

    //ISTRAŽIVAČ FUNKCIJE
    Route::middleware('role:Istraživač')->group(function () {

        Route::post('/radovi', [NaucniRadController::class, 'store']); // Kreiranje rada, ovde ćemo ubaciti nasumičnu dodelu recenzenta.
        Route::get('/radovi/moji', [NaucniRadController::class, 'mojiRadovi']); // Vidi svoje radove nezavisno od statusa.
        Route::get('/radovi/{id}/recenzije', [NaucniRadController::class, 'prikaziRecenziju']); // Vidi recenziju
        Route::get('/istrazivaci', [UserController::class, 'istrazivaci']);

    });

    //RECENZENT FUNKCIJE
    Route::middleware('role:Recenzent')->group(function () {
        Route::get('/recenzije/moje', [RecenzijaController::class, 'dodeljeniRadovi']); //Recenzent vidi sve radove koji su mi pridodati
        Route::post('/recenzije/{id}/stavke', [RecenzijaController::class, 'sacuvajStavkuRecenzije']);  //!!!!!TESTIRAJ KROZ POSTMAN!!!!!
    });
});