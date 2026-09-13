<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OblastController;
use App\Http\Controllers\NaucniRadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecenzijaController;
use App\Http\Controllers\StatistikaController;

Route::post('/prijava', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/oblasti', [OblastController::class, 'index']);
Route::get('/radovi/objavljeni', [NaucniRadController::class, 'objavljeniRadovi']);
Route::get('/radovi/{id}', [NaucniRadController::class, 'show'])->where('id', '[0-9]+');
Route::get('/istrazivaci', [UserController::class, 'istrazivaci']);
Route::get('/istrazivaci/{id}', [UserController::class, 'profilIstrazivaca'])->where('id', '[0-9]+');
Route::get('/radovi/{id}/citati', [NaucniRadController::class, 'citati']);
Route::get('/radovi/{id}/spoljni-citati', [NaucniRadController::class, 'spoljniCitati']);
Route::get('/radovi/{id}/srodni-radovi', [NaucniRadController::class, 'srodniRadovi']);
Route::get('/radovi/{id}/istorija-citiranosti', [NaucniRadController::class, 'istorijaCitiranosti']);
Route::get('/radovi/{id}/verzije', [NaucniRadController::class, 'verzije']);
Route::get('/radovi/{id}/fajl', [NaucniRadController::class, 'preuzmiFajl']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/odjava', [AuthController::class, 'logout']);

    Route::middleware('role:Administrator')->group(function () {

        Route::post('/admin/korisnici', [AuthController::class, 'register'])->middleware('throttle:10,1');
        Route::get('/admin/korisnici', [UserController::class, 'index']);
        Route::get('/admin/korisnici/{id}', [UserController::class, 'show']);
        Route::put('/admin/korisnici/{id}', [UserController::class, 'update']);
        Route::put('/admin/korisnici/{id}/uloge', [UserController::class, 'dodeliUlogu']);
        Route::delete('/admin/korisnici/{id}', [UserController::class, 'destroy']);
        Route::get('/radovi', [NaucniRadController::class, 'index']);
        Route::delete('/radovi/{id}', [NaucniRadController::class, 'destroy']);
        Route::post('/radovi/{id}/recenzent', [RecenzijaController::class, 'dodeliRecenzenta']);
        Route::delete('/recenzije/{id}', [RecenzijaController::class, 'destroy']);
        Route::get('/statistika/radovi-po-statusu', [StatistikaController::class, 'radoviPoStatusu']);
        Route::get('/statistika/radovi-po-godini', [StatistikaController::class, 'radoviPoGodini']);

    });

    Route::middleware('role:Istraživač')->group(function () {

        Route::post('/radovi', [NaucniRadController::class, 'store']);
        Route::get('/radovi/moji', [NaucniRadController::class, 'mojiRadovi']);
        Route::put('/radovi/{id}', [NaucniRadController::class, 'update'])->where('id', '[0-9]+');
        Route::post('/radovi/{id}/verzija', [NaucniRadController::class, 'novaVerzija']);
        Route::get('/radovi/{id}/recenzije', [NaucniRadController::class, 'prikaziRecenziju']);

    });

    Route::middleware('role:Recenzent')->group(function () {
        Route::get('/recenzije/moje', [RecenzijaController::class, 'dodeljeniRadovi']);
        Route::post('/recenzije/{id}/stavke', [RecenzijaController::class, 'sacuvajStavkuRecenzije']);
    });
});
