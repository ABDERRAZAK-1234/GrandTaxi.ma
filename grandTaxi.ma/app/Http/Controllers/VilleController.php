<?php

namespace App\Http\Controllers;

use App\Models\Ville;
use Illuminate\Http\Request;

class VilleController extends Controller
{
    public function index()
    {
        $villes = Ville::all();
        return response()->json($villes);
    }
    // create cille
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|unique:villes,nom|max:100',
        ]);

        $ville = Ville::create($validated);

        return response()->json([
            'message' => 'Ville créée avec succès',
            'data' => $ville
        ], 201);
    }

    // update ville
    public function update(Request $request, Ville $ville)
    {
        $validated = $request->validate([
            'nom' => 'required|string|unique:villes,nom,' . $ville->id . '|max:100',
        ]);

        $ville->update($validated);

        return response()->json([
            'message' => 'Ville mise à jour avec succès',
            'data' => $ville
        ], 200);
    }

    // supp ville
    public function destroy(Ville $ville)
    {
        $ville->delete();
        return response()->json([
            'message' => 'Ville supprimée avec succès'
        ]);
    }

}
