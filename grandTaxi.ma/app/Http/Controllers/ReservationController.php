<?php

namespace App\Http\Controllers;

use App\Events\ReservationCreated;
use App\Models\Paiement;
use App\Models\Reservation;
use App\Models\Taxi;
use App\Models\Trajet;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $reservations = Reservation::with(['trajet'])
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($reservations);
    }

    // créer reservation
    public function reserver(Request $request)
    {
        $request->validate([
            'trajet_id' => 'required|exists:trajets,id',
            'taxi_id' => 'required|exists:taxis,id',
            'nombre_place' => 'required|integer|min:1',
            'bagage' => 'required|boolean',
            'nombre_bagage' => 'required_if:bagage,true|integer|min:0|max:2',
            'sieges' => 'required|array|min:1',
            'sieges.*' => 'integer|min:1|max:6',
        ]);
        $siegesNums = $request->sieges;

        $trajet = Trajet::findOrFail($request->trajet_id);

        if ($trajet->statut === 'cloture') {
            return response()->json([
                'message' => 'Ce trajet est clôturé, réservation impossible.'
            ], 422);
        }

        $taxi = Taxi::findOrFail($request->taxi_id);

        // Verifier places disponibles
        $placesReservees = Reservation::where('taxi_id', $taxi->id)
            ->where('statut', 'confirmed')
            ->sum('nombre_place');

        $placesRestantes = $taxi->capacite - $placesReservees;

        if ($request->nombre_place > $placesRestantes) {
            return response()->json([
                'message' => 'Pas assez de places disponibles.',
                'places_restantes' => $placesRestantes,
            ], 422);
        }

        // Calculer prix total
        $nombreBagage = $request->bagage ? ($request->nombre_bagage ?? 0) : 0;
        $prixTotal = ($trajet->prix * $request->nombre_place) + (10 * $nombreBagage);


        // *********Crer Paiement avec Stripe**************
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $prixTotal * 100,
            'currency' => 'mad',
            'metadata' => [
                'trajet_id' => $request->trajet_id,
                'taxi_id' => $request->taxi_id,
                'user_id' => $request->user()->id,
                'sieges' => json_encode($siegesNums),
                'nombre_place' => count($siegesNums),
                'bagage' => $request->bagage ? '1' : '0',
                'nombre_bagage' => $nombreBagage,
            ],
        ]);

        return response()->json([
            'message' => 'Paiement initié — complétez le paiement pour confirmer la réservation',
            'client_secret' => $paymentIntent->client_secret,
            'montant' => $prixTotal,
            'details' => [
                'trajet_id' => $request->trajet_id,
                'taxi_id' => $request->taxi_id,
                'nombre_place' => $request->nombre_place,
                'bagage' => $request->bagage,
                'nombre_bagage' => $nombreBagage,
            ],
        ]);
    }

    // confirmer reservation
    public function confirmerReservation(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

        if ($paymentIntent->status !== 'succeeded') {
            return response()->json([
                'message' => 'Paiement non complété, réservation annulée.',
                'statut' => $paymentIntent->status,
            ], 422);
        }

        $meta = $paymentIntent->metadata;

        $reservation = Reservation::create([
            'trajet_id' => $meta->trajet_id,
            'taxi_id' => $meta->taxi_id,
            'user_id' => $meta->user_id,
            'nombre_place' => $meta->nombre_place,
            'sieges' => json_decode($meta->sieges),
            'bagage' => $meta->bagage === '1',
            'nombre_bagage' => $meta->nombre_bagage,
            'prix_total' => $paymentIntent->amount / 100,
            'statut' => 'confirmed',
        ]);

        $paiement = Paiement::create([
            'reservation_id' => $reservation->id,
            'user_id' => $meta->user_id,
            'montant' => $paymentIntent->amount / 100,
            'methode' => 'stripe',
            'statut' => 'paid',
            'stripe_payment_intent_id' => $paymentIntent->id,
            'stripe_client_secret' => $paymentIntent->client_secret,
        ]);

        broadcast(new ReservationCreated($reservation));

        // Mettre à jour statut taxi si complet
        $taxi = Taxi::find($meta->taxi_id);
        $placesReservees = Reservation::where('taxi_id', $meta->taxi_id)
            ->where('statut', 'confirmed')
            ->sum('nombre_place');

        if ($placesReservees >= $taxi->capacite) {
            $taxi->update(['statuts' => 'full']);
        }

        $reservation->load(['trajet', 'taxi']);

        return response()->json([
            'message' => 'Réservation confirmée avec succès',
            'reservation' => $reservation,
            'paiement' => $paiement,
            'billet_url' => "/api/reservations/{$reservation->id}/billet",
        ], 201);
    }

    // show reservations
    public function show(Request $request, Reservation $reservation)
    {
        // Verifer la reservation appartient a un utilisateur
        if ($reservation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $reservation->load(['trajet']);
        return response()->json($reservation);
    }

    public function adminIndex()
    {
        $reservations = Reservation::with(['user', 'trajet', 'taxi'])->get();
        return response()->json($reservations);
    }
}
