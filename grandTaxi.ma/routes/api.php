<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\VilleController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// routes of ville
Route::get('/villes', [VilleController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/villes', [VilleController::class, 'store']);
    Route::put('/admin/villes/{ville}', [VilleController::class, 'update']);
    Route::delete('/admin/villes/{ville}', [VilleController::class, 'destroy']);
});
