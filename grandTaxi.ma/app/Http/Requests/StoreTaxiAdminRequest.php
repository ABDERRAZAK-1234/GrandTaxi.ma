<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Taxi;

/**
 * Validation for admin creating a taxi for a driver.
 * Enforces: required image, unique driver_id (one taxi per driver).
 */
class StoreTaxiAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matricule'  => 'required|string|unique:taxis,matricule',
            'capacite'   => 'required|integer|min:4|max:8',
            'image'      => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'driver_id'  => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if (Taxi::where('driver_id', $value)->exists()) {
                        $fail('Ce conducteur possède déjà un taxi. Un conducteur ne peut avoir qu\'un seul taxi.');
                    }
                },
            ],
            'trajet_id'  => 'nullable|exists:trajets,id',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required'    => 'La photo du taxi est obligatoire.',
            'image.image'       => 'Le fichier doit être une image.',
            'image.mimes'       => 'L\'image doit être au format jpeg, png, jpg ou webp.',
            'image.max'         => 'L\'image ne doit pas dépasser 2 Mo.',
            'matricule.unique'  => 'Ce matricule est déjà utilisé.',
            'driver_id.exists'  => 'Le conducteur spécifié n\'existe pas.',
        ];
    }
}
