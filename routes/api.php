<?php   

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecenzijaController;
use App\Http\Controllers\StavkaRecenzijeController;
use App\Http\Controllers\NaucniRadController;

Route::apiResource('users', UserController::class);
Route::apiResource('recenzije', RecenzijaController::class);
Route::apiResource('stavke-recenzije', StavkaRecenzijeController::class);
Route::apiResource('naucniRadovi', NaucniRadController::class);



