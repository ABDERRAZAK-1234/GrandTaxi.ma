<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BilletController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\TaxiController;
use App\Http\Controllers\TrajetController;
use App\Http\Controllers\VilleController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



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
Route::get('/trajets/{trajet}/taxis', [TaxiController::class, 'parTrajet']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/trajets', [TrajetController::class, 'store']);
    Route::put('/admin/trajets/{trajet}', [TrajetController::class, 'update']);
    Route::delete('/admin/trajets/{trajet}', [TrajetController::class, 'destroy']);
    Route::patch('/admin/trajets/{trajet}/cloturer', [TrajetController::class, 'cloturer']);
});

// routes taxi
Route::get('/taxis', [TaxiController::class, 'index']);
Route::get('/taxis/{taxi}', [TaxiController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/taxis', [TaxiController::class, 'store']);
    Route::put('/admin/taxis/{taxi}', [TaxiController::class, 'update']);
    Route::delete('/admin/taxis/{taxi}', [TaxiController::class, 'destroy']);
});

Route::get('/taxis/{taxi}/sieges-occupes', [TaxiController::class, 'siegesOccupes']);

// routes reservations
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show']);
    Route::post('/reserver', [ReservationController::class, 'reserver']);
    Route::post('/reserver/confirmer', [ReservationController::class, 'confirmerReservation']);

    // Admin
    Route::get('/admin/reservations', [ReservationController::class, 'adminIndex']);
});


// route de billet
Route::get('/reservations/{reservation}/billet', [BilletController::class, 'telecharger']);

// Admin users list
Route::get('/admin/users', function() {
    return response()->json(User::all());
})->middleware('auth:sanctum');
// Bannir
Route::patch('/admin/users/{id}/ban', function($id) {
    $user = User::findOrFail($id);
    $user->update(['status' => 'inactive']);
    return response()->json(['message' => 'Utilisateur banni']);
})->middleware('auth:sanctum');

// Débannir
Route::patch('/admin/users/{id}/unban', function($id) {
    $user = User::findOrFail($id);
    $user->update(['status' => 'active']);
    return response()->json(['message' => 'Utilisateur débanni']);
})->middleware('auth:sanctum');
