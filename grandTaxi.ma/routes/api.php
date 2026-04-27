<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BilletController;
use App\Http\Controllers\DriverDashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\TaxiController;
use App\Http\Controllers\TrajetController;
use App\Http\Controllers\VilleController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════
//  PUBLIC ROUTES (no auth required)
// ═══════════════════════════════════════════════════

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public read-only endpoints
Route::get('/villes', [VilleController::class, 'index']);
Route::get('/trajets', [TrajetController::class, 'index']);
Route::get('/trajets/{trajet}', [TrajetController::class, 'show']);
Route::get('/trajets/{trajet}/taxis', [TaxiController::class, 'parTrajet']);
Route::get('/taxis', [TaxiController::class, 'index']);
Route::get('/taxis/{taxi}', [TaxiController::class, 'show']);
Route::get('/taxis/{taxi}/sieges-occupes', [TaxiController::class, 'siegesOccupes']);
Route::get('/reservations/{reservation}/billet', [BilletController::class, 'telecharger']);

// ═══════════════════════════════════════════════════
//  AUTHENTICATED ROUTES
// ═══════════════════════════════════════════════════

Route::middleware('auth:sanctum')->group(function () {

    // Current user info
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $user->load('driverProfile');
        return $user;
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // ───────────────────────────────────────────
    //  ADMIN ROUTES — role:admin
    // ───────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // Villes CRUD
        Route::post('/villes', [VilleController::class, 'store']);
        Route::put('/villes/{ville}', [VilleController::class, 'update']);
        Route::delete('/villes/{ville}', [VilleController::class, 'destroy']);

        // Trajets CRUD
        Route::post('/trajets', [TrajetController::class, 'store']);
        Route::put('/trajets/{trajet}', [TrajetController::class, 'update']);
        Route::delete('/trajets/{trajet}', [TrajetController::class, 'destroy']);
        Route::patch('/trajets/{trajet}/cloturer', [TrajetController::class, 'cloturer']);

        // Taxis CRUD (admin can create taxi for any driver)
        Route::post('/taxis', [TaxiController::class, 'store']);
        Route::put('/taxis/{taxi}', [TaxiController::class, 'update']);
        Route::delete('/taxis/{taxi}', [TaxiController::class, 'destroy']);

        // All reservations
        Route::get('/reservations', [ReservationController::class, 'adminIndex']);

        // User management
        Route::get('/users', function () {
            return response()->json(User::with('driverProfile')->get());
        });

        Route::patch('/users/{id}/ban', function ($id) {
            $user = User::findOrFail($id);
            $user->update(['status' => 'inactive']);
            return response()->json(['message' => 'Utilisateur banni.']);
        });

        Route::patch('/users/{id}/unban', function ($id) {
            $user = User::findOrFail($id);
            $user->update(['status' => 'active']);
            return response()->json(['message' => 'Utilisateur débanni.']);
        });
    });

// driver routes
    Route::middleware('role:driver')->prefix('driver')->group(function () {

        // Driver dashboard
        Route::get('/dashboard', [DriverDashboardController::class, 'dashboard']);

        // Driver creates taxi
        Route::post('/taxi', [TaxiController::class, 'storeDriver']);
        
        // Driver reverses trip
        Route::post('/reverse-trip', [DriverDashboardController::class, 'reverseTrip']);
    });


    // 
    Route::middleware('role:user')->group(function () {

        // Reserve seats
        Route::post('/reserver', [ReservationController::class, 'reserver']);
        Route::post('/reserver/confirmer', [ReservationController::class, 'confirmerReservation']);

        // My reservations
        Route::get('/reservations', [ReservationController::class, 'index']);
        Route::get('/reservations/{reservation}', [ReservationController::class, 'show']);
    });
});
