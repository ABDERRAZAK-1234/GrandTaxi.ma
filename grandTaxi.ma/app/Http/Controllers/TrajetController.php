<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Trajet;

class TrajetController extends Controller
{
    public function index()
    {
        $trajets = Trajet::with(['villeDepart', 'villeArrivee'])
            ->where('statut', 'actif')
            ->get();

        return response()->json($trajets);
    }

    // create trajet
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ville_depart_id' => 'required|exists:villes,id',
            'ville_arrivee_id' => 'required|exists:villes,id|different:ville_depart_id',
            'prix' => 'required|numeric|min:0',
        ]);

        $trajet = Trajet::create($validated);
        $trajet->load(['villeDepart', 'villeArrivee']);

        return response()->json([
            'message' => 'Trajet créé avec succès',
            'data' => $trajet
        ], 201);
    }
    // show trajets
    public function show(Trajet $trajet)
    {
        $trajet->load(['villeDepart', 'villeArrivee']);
        return response()->json($trajet);
    }

    // update trajet
    public function update(Request $request, Trajet $trajet)
    {
        $validated = $request->validate([
            'ville_depart_id' => 'sometimes|exists:villes,id',
            'ville_arrivee_id' => 'sometimes|exists:villes,id|different:ville_depart_id',
            'prix' => 'sometimes|numeric|min:0',
        ]);

        $trajet->update($validated);
        $trajet->load(['villeDepart', 'villeArrivee']);

        return response()->json($trajet);
    }

    // delete trajet
    public function destroy(Trajet $trajet)
    {
        $trajet->delete();
        return response()->json([
            'message' => 'Trajet supprimé avec succès'
        ]);
    }

    // cloturer un trajet
    public function cloturer(Trajet $trajet)
    {
        $trajet->update(['statut' => 'cloture']);
        return response()->json([
            'message' => 'Trajet clôturé avec succès',
            'trajet' => $trajet
        ]);
    }
}
