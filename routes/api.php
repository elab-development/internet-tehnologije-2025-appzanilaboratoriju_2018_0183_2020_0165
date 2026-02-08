<?php   

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecenzijaController;

Route::apiResource('users', UserController::class);
Route::apiResource('recenzije', RecenzijaController::class);



