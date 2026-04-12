<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Taxi extends Model
{
    protected $fillable = [
        'matricule',
        'capacite',
        'statuts',
        'driver_id',
        'trajet_id',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function trajet()
    {
        return $this->belongsTo(Trajet::class, 'trajet_id');
    }
}


