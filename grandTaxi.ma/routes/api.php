<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\VilleController;
use App\Http\Controllers\TrajetController;

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

// routes trajet
Route::get('/trajets', [TrajetController::class, 'index']);
Route::get('/trajets/{trajet}', [TrajetController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/trajets', [TrajetController::class, 'store']);
    Route::put('/admin/trajets/{trajet}', [TrajetController::class, 'update']);
    Route::delete('/admin/trajets/{trajet}', [TrajetController::class, 'destroy']);
    Route::patch('/admin/trajets/{trajet}/cloturer', [TrajetController::class, 'cloturer']);
});
