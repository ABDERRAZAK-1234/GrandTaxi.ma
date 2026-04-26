<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Taxi;
use Illuminate\Http\Request;

/**
 * Controller for the driver dashboard.
 * Returns all driver-related data in one consolidated endpoint.
 */
class DriverDashboardController extends Controller
{
    /**
     * GET /api/driver/dashboard
     *
     * Returns driver info, profile, taxi, reservations, and stats.
     */
    public function dashboard(Request $request)
    {
        try {
            $user = $request->user();

            // Load driver profile
            $user->load('driverProfile');

            // Load driver's taxi with trajet
            $taxi = Taxi::with('trajet')
                ->where('driver_id', $user->id)
                ->first();

            $reservations = collect();
            $stats = [
                'total_reservations'  => 0,
                'confirmed'           => 0,
                'places_occupees'     => 0,
                'places_restantes'    => 0,
                'revenus'             => 0,
            ];

            if ($taxi) {
                // Load reservations for this taxi
                $reservations = Reservation::with('user')
                    ->where('taxi_id', $taxi->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $confirmedReservations = $reservations->where('statut', 'confirmed');

                $placesOccupees = $confirmedReservations->sum('nombre_place');

                $stats = [
                    'total_reservations'  => $reservations->count(),
                    'confirmed'           => $confirmedReservations->count(),
                    'places_occupees'     => $placesOccupees,
                    'places_restantes'    => max(0, $taxi->capacite - $placesOccupees),
                    'revenus'             => $confirmedReservations->sum('prix_total'),
                ];
            }

            return response()->json([
                'driver'       => [
                    'id'     => $user->id,
                    'nom'    => $user->nom,
                    'prenom' => $user->prenom,
                    'email'  => $user->email,
                ],
                'profile'      => $user->driverProfile,
                'taxi'         => $taxi,
                'reservations' => $reservations,
                'stats'        => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement du dashboard.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
