<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DriverProfile;
use App\Models\Taxi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Register a new user.
     * If role is 'driver', also creates a DriverProfile (cne, permis)
     * and optionally a Taxi (matricule, capacite).
     */
    public function register(Request $request)
    {

        $rules = [
            'nom'      => 'required|string|max:255',
            'prenom'   => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role'     => 'sometimes|in:user,driver',
        ];

        // if role is driver
        if ($request->role === 'driver') {
            $rules['cne']    = 'required|string|unique:driver_profiles,cne';
            $rules['permis'] = 'required|string|unique:driver_profiles,permis';
            $rules['taxi_matricule'] = 'nullable|string|unique:taxis,matricule';
            $rules['taxi_capacite']  = 'nullable|integer|min:4|max:8';
            $rules['taxi_trajet']    = 'required_with:taxi_matricule|exists:trajets,id';
            $rules['taxi_image']     = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
        }

        $request->validate($rules);

        // First user gets role admin
        if (User::count() == 0) {
            $role = 'admin';
        } else {
            $role = $request->role ?? 'user';
        }

        $user = DB::transaction(function () use ($request, $role) {

            // 1) Create the user
            $user = User::create([
                'nom'      => $request->nom,
                'prenom'   => $request->prenom,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $role,
            ]);

            // create the DriverProfile
            if ($role === 'driver') {
                DriverProfile::create([
                    'user_id' => $user->id,
                    'cne'     => $request->cne,
                    'permis'  => $request->permis,
                ]);


                if ($request->filled('taxi_matricule')) {
                    $imagePath = null;
                    if ($request->hasFile('taxi_image')) {
                        $imagePath = $request->file('taxi_image')->store('taxis', 'public');
                    }

                    Taxi::create([
                        'matricule'       => $request->taxi_matricule,
                        'capacite'        => $request->taxi_capacite ?? 6,
                        'statuts'         => 'available',
                        'driver_id'       => $user->id,
                        'trajet_id'       => $request->taxi_trajet,
                        'image'           => $imagePath,
                        'queue_joined_at' => now(),
                    ]);
                }
            }

            return $user;
        });

        // Load relationships for the response
        $user->load('driverProfile');

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Utilisateur créé avec succès',
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 201);
    }

    /**
     * Login an existing user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('driverProfile')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        if ($user->status === 'inactive') {
            return response()->json([
                'message' => 'Votre compte a été suspendu.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Utilisateur authentifié avec succès',
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }

    /**
     * Logout the user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Utilisateur déconnecté avec succès'
        ]);
    }
}
