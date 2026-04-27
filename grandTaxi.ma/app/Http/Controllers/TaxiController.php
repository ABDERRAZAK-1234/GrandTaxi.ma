<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaxiAdminRequest;
use App\Http\Requests\StoreTaxiDriverRequest;
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



    public function store(StoreTaxiAdminRequest $request)
    {
        try {
            $validated = $request->validated();

            // Upload image
            $file = $request->file('image');
            $name = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('taxis', $name, 'public');
            $validated['image'] = $path;


            $validated['statuts'] = $validated['statuts'] ?? 'available';
            $validated['queue_joined_at'] = now();

            $taxi = Taxi::create($validated);
            $taxi->load(['driver', 'trajet']);

            return response()->json([
                'message' => 'Taxi créé avec succès.',
                'data'    => $taxi,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du taxi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function storeDriver(StoreTaxiDriverRequest $request)
    {
        try {
            $validated = $request->validated();

            $file = $request->file('image');
            $name = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('taxis', $name, 'public');
            $validated['image'] = $path;

            $validated['driver_id'] = $request->user()->id;

            $validated['statuts'] = 'available';
            $validated['queue_joined_at'] = now();

            $taxi = Taxi::create($validated);
            $taxi->load(['driver', 'trajet']);

            return response()->json([
                'message' => 'Taxi créé avec succès.',
                'data'    => $taxi,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du taxi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, Taxi $taxi)
    {
        $validated = $request->validate([
            'matricule' => 'sometimes|string|unique:taxis,matricule,' . $taxi->id,
            'capacite'  => 'sometimes|integer|min:1',
            'statuts'   => 'sometimes|in:available,reserved,full,unavailable',
            'driver_id' => 'sometimes|exists:users,id',
            'trajet_id' => 'sometimes|exists:trajets,id',
            'image'     => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('taxis', $name, 'public');
            $validated['image'] = $path;
        }

        $taxi->update($validated);
        $taxi->load(['driver', 'trajet']);

        return response()->json([
            'message' => 'Taxi mis à jour avec succès.',
            'data'    => $taxi,
        ], 200);
    }


    public function destroy(Taxi $taxi)
    {
        $taxi->delete();
        return response()->json(['message' => 'Taxi supprimé avec succès.']);
    }


public function parTrajet(Trajet $trajet)
{
    $taxis = Taxi::where('trajet_id', $trajet->id)
        ->whereIn('statuts', ['available', 'reserved'])
        ->orderBy('queue_joined_at', 'asc')
        ->orderBy('id', 'asc')
        ->get()
        ->map(function ($taxi) use ($trajet) {
            $placesReservees = Reservation::where('taxi_id', $taxi->id)
                ->where('trajet_id', $trajet->id)
                ->where('statut', 'confirmed')
                ->sum('nombre_place');

            $taxi->places_reservees  = $placesReservees;
            $taxi->places_restantes  = $taxi->capacite - $placesReservees;
            return $taxi;
        })
        ->filter(fn($t) => $t->places_restantes > 0)
        ->values();

    // ─── Compute is_active_in_queue dynamically ───────────
    $activeFound = false;
    $taxis = $taxis->map(function ($taxi) use (&$activeFound) {
        if (!$activeFound) {
            $taxi->is_active_in_queue = true;
            $activeFound = true;
        } else {
            $taxi->is_active_in_queue = false;
        }
        return $taxi;
    });
    // ──────────────────────────────────────────────────────

    return response()->json($taxis->values());
}


    public function siegesOccupes(Taxi $taxi)
    {
        $siegesOccupes = Reservation::where('taxi_id', $taxi->id)
            ->where('statut', 'confirmed')
            ->whereNotNull('sieges')
            ->get()
            ->pluck('sieges')
            ->map(function ($siege) {
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
