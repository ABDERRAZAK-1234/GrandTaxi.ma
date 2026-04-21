<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BilletController extends Controller
{
    public function telecharger(Request $request, Reservation $reservation)
    {
        // Authentifier via query token si pas de session
        if ($request->has('token')) {
            $tokenValue = $request->query('token');
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenValue);

            if (!$accessToken) {
                return response()->json(['message' => 'Non autorisé'], 401);
            }

            $user = $accessToken->tokenable;
        } else {
            $user = $request->user();
        }

        if (!$user || $reservation->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        if ($reservation->statut !== 'confirmed') {
            return response()->json(['message' => 'Réservation non confirmée'], 422);
        }

        $reservation->load(['user', 'trajet.villeDepart', 'trajet.villeArrivee', 'taxi', 'paiement']);

        $pdf = Pdf::loadView('pdf.billet', compact('reservation'))
            ->setPaper('a5', 'portrait');

        return $pdf->download("billet-grandtaxi-{$reservation->id}.pdf");
    }
}
