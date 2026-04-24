<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
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
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,jfif,avif|max:2048',
        ]);

        // Image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('taxis', $name, 'public');
            $validated['image'] = $path;
        }


        $user = $request->user();

        if ($user->role === 'driver') {
            $validated['driver_id'] = $user->id;
        } else {
            $request->validate([
                'driver_id' => 'required|exists:users,id',
            ]);
            $validated['driver_id'] = $request->driver_id;
        }

        $taxi = Taxi::create($validated);
        $taxi->load(['driver', 'trajet']);

        return response()->json([
            'message' => 'Taxi créé avec succès',
            'data' => $taxi,
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
            ->whereIn('statuts', ['available', 'reserved'])
            ->get()
            ->map(function ($taxi) {
                // sum des places reservées (pas count des reservations)
                $placesReservees = Reservation::where('taxi_id', $taxi->id)
                    ->where('statut', 'confirmed')
                    ->sum('nombre_place');

                $taxi->places_reservees = $placesReservees;
                $taxi->places_restantes = $taxi->capacite - $placesReservees;
                return $taxi;
            });

        return response()->json($taxis);
    }

    // sieges occupes
    public function siegesOccupes(Taxi $taxi)
    {
        $siegesOccupes = Reservation::where('taxi_id', $taxi->id)
            ->where('statut', 'confirmed')
            ->whereNotNull('sieges')
            ->get()
            ->pluck('sieges')
            ->map(function ($siege) {
                // Parser si c'est un string JSON
                $parsed = is_string($siege) ? json_decode($siege, true) : $siege;
                return $parsed;
            })
            ->flatten()
            ->map(fn($s) => (int) $s)
            ->unique()
            ->values();

        return response()->json([
            'sieges_occupes' => $siegesOccupes
        ]);
    }

}
