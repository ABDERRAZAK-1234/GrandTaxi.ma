<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Taxi;
use App\Models\Trajet;
use Illuminate\Http\Request;

/**
 * Controller for the driver dashboard.
 * Returns all driver-related data in one consolidated endpoint.
 */
class DriverDashboardController extends Controller
{
    /**
     * POST /api/driver/reverse-trip
     */
    public function reverseTrip(Request $request)
    {
        $user = $request->user();

        // Sécurité — seulement le conducteur peut faire ça
        if ($user->role !== 'driver') {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        $taxi = Taxi::where('driver_id', $user->id)->first();

        if (!$taxi) {
            return response()->json(['message' => 'Aucun taxi trouvé pour ce conducteur.'], 404);
        }

        if (!$taxi->trajet_id) {
            return response()->json(['message' => 'Ce taxi n\'a pas de trajet assigné.'], 422);
        }

        $currentTrajet = Trajet::find($taxi->trajet_id);

        if (!$currentTrajet) {
            return response()->json(['message' => 'Le trajet assigné n\'existe plus.'], 422);
        }

        // Marquer toutes les réservations confirmées du trajet actuel comme terminées (completed)
        Reservation::where('taxi_id', $taxi->id)
            ->where('trajet_id', $currentTrajet->id)
            ->where('statut', 'confirmed')
            ->update(['statut' => 'completed']);

        // Trouver le trajet retour
        $reverseTrajet = Trajet::where('ville_depart_id', $currentTrajet->ville_arrivee_id)
            ->where('ville_arrivee_id', $currentTrajet->ville_depart_id)
            ->where('statut', 'actif')
            ->first();

        if (!$reverseTrajet) {
            return response()->json([
                'message' => 'Le trajet retour n\'existe pas dans le système. Contactez l\'administrateur.'
            ], 422);
        }

        // Mettre à jour le taxi — fin de queue (queue_joined_at = now())
        $taxi->update([
            'trajet_id' => $reverseTrajet->id,
            'statuts' => 'available',
            'queue_joined_at' => now(),
        ]);

        $taxi->load('trajet');

        return response()->json([
            'message' => 'Trajet inversé avec succès ! Vous êtes maintenant dans la file pour le trajet retour.',
            'taxi' => $taxi,
        ]);
    }

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
                'total_reservations' => 0,
                'confirmed' => 0,
                'places_occupees' => 0,
                'places_restantes' => 0,
                'revenus' => 0,
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
                    'total_reservations' => $reservations->count(),
                    'confirmed' => $confirmedReservations->count(),
                    'places_occupees' => $placesOccupees,
                    'places_restantes' => max(0, $taxi->capacite - $placesOccupees),
                    'revenus' => $confirmedReservations->sum('prix_total'),
                ];
            }

            return response()->json([
                'driver' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                ],
                'profile' => $user->driverProfile,
                'taxi' => $taxi,
                'reservations' => $reservations,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement du dashboard.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
