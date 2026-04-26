<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Taxi;

/**
 * Validation for a driver creating his own taxi.
 * Enforces: required image, one taxi per driver (checks auth user).
 */
class StoreTaxiDriverRequest extends FormRequest
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
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if ($user && Taxi::where('driver_id', $user->id)->exists()) {
                $validator->errors()->add(
                    'driver',
                    'Vous possédez déjà un taxi. Un conducteur ne peut avoir qu\'un seul taxi.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'image.required'    => 'La photo du taxi est obligatoire.',
            'image.image'       => 'Le fichier doit être une image.',
            'image.mimes'       => 'L\'image doit être au format jpeg, png, jpg ou webp.',
            'image.max'         => 'L\'image ne doit pas dépasser 2 Mo.',
            'matricule.unique'  => 'Ce matricule est déjà utilisé.',
        ];
    }
}
