<?php   

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecenzijaController;
use App\Http\Controllers\StavkaRecenzijeController;

Route::apiResource('users', UserController::class);
Route::apiResource('recenzije', RecenzijaController::class);
Route::apiResource('stavke-recenzije', StavkaRecenzijeController::class);



