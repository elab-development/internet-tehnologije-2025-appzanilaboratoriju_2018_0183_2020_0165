<?php   

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NaucniRadController;

Route::apiResource('users', UserController::class);
Route::apiResource('naucniRadovi', NaucniRadController::class);



