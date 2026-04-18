<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use Illuminate\Http\Request;
use Stripe\Stripe;
// use Stripe\PaymentIntent;

class PaiementController extends Controller
{
    // historique paiement
    public function index(Request $request)
    {
        $paiements = Paiement::with('reservation')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($paiements);
    }

    // detail d'un paiement
    public function show(Request $request, Paiement $paiement)
    {
        if ($paiement->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json($paiement->load('reservation'));
    }

    // tous les paiements (admin)
    public function adminIndex()
    {
        $paiements = Paiement::with(['user', 'reservation'])->get();
        return response()->json($paiements);
    }

    // rembourser (admin)
    public function rembourser(Request $request, Paiement $paiement)
    {
        if ($paiement->statut !== 'paid') {
            return response()->json([
                'message' => 'Ce paiement ne peut pas être remboursé.'
            ], 422);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        \Stripe\Refund::create([
            'payment_intent' => $paiement->stripe_payment_intent_id,
        ]);

        $paiement->update(['statut' => 'unpaid']);

        return response()->json([
            'message' => 'Remboursement effectué avec succès',
            'paiement' => $paiement,
        ]);
    }
}
