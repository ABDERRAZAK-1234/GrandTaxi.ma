<?php

namespace App\Http\Controllers;

use App\Models\Taxi;
use App\Models\Trajet;
use Illuminate\Http\Request;

class TaxiController extends Controller
{
    public function index()
    {
        $taxis = Taxi::with(['driver', 'trajet'])->get();
        return response()->json($taxis);
    }

    public function show(Taxi $taxi)
    {
        $taxi->load(['driver', 'trajet']);
        return response()->json($taxi);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'matricule' => 'required|string|unique:taxis,matricule',
            'capacite' => 'required|integer|min:4',
            'statuts' => 'required|in:available,reserved,full,unavailable',
            'driver_id' => 'required|exists:users,id',
            'trajet_id' => 'required|exists:trajets,id',
        ]);

        $taxi = Taxi::create($validated);
        $taxi->load(['driver', 'trajet']);

        return response()->json([
            'message' => 'Taxi créé avec succès',
            'data' => $taxi
        ], 201);
    }

    public function update(Request $request, Taxi $taxi)
    {
        $validated = $request->validate([
            'matricule' => 'sometimes|string|unique:taxis,matricule,' . $taxi->id,
            'capacite' => 'sometimes|integer|min:1',
            'statuts' => 'sometimes|in:available,reserved,full,unavailable',
            'driver_id' => 'sometimes|exists:users,id',
            'trajet_id' => 'sometimes|exists:trajets,id',
        ]);

        $taxi->update($validated);
        $taxi->load(['driver', 'trajet']);

        return response()->json([
            'message' => 'Taxi mis à jour avec succès',
            'data' => $taxi
        ], 200);
    }

    public function destroy(Taxi $taxi)
    {
        $taxi->delete();
        return response()->json(['message' => 'Taxi supprimé avec succès']);
    }

    // taxis avec les taxi de ce trajet
    public function parTrajet(Trajet $trajet)
    {
        $taxis = Taxi::where('trajet_id', $trajet->id)
            ->where('statuts', 'available')
            ->withCount([
                'reservations as places_reservees' => function ($query) {
                    $query->where('statut', 'confirmed');
                }
            ])
            ->get()
            ->map(function ($taxi) {
                $taxi->places_restantes = $taxi->capacite - $taxi->places_reservees;
                return $taxi;
            });

        return response()->json($taxis);
    }

}
