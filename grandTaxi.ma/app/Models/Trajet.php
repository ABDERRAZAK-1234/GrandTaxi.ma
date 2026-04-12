<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trajet extends Model
{
    protected $fillable = [
        'ville_depart_id',
        'ville_arrivee_id',
        'prix',
        'statut',
    ];

    // ville depart
    public function villeDepart()
    {
        return $this->belongsTo(Ville::class, 'ville_depart_id');
    }

    // ville arrivee
    public function villeArrivee()
    {
        return $this->belongsTo(Ville::class, 'ville_arrivee_id');
    }

    // les taxi de ce trajet
    public function taxis()
    {
        return $this->hasMany(Taxi::class);
    }
}
