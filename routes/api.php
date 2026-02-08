<?php   

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecenzijaController;
use App\Http\Controllers\StavkaRecenzijeController;
use App\Http\Controllers\NaucniRadController;
use App\Http\Controllers\AuthController;

Route::apiResource('users', UserController::class);
Route::apiResource('recenzije', RecenzijaController::class);
Route::apiResource('stavke-recenzije', StavkaRecenzijeController::class);
Route::apiResource('naucniRadovi', NaucniRadController::class);
Route::post('/login', [AuthController::class, 'login']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
}); // ovo je funkcija koju imaju samo ulogovani ljudi



