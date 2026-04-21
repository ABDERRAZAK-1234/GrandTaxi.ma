<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'nombre_place',
        'sieges',
        'bagage',
        'nombre_bagage',
        'prix_total',
        'statut',
        'user_id',
        'trajet_id',
        'taxi_id',
    ];
    protected $casts = [
        'sieges' => 'array',
        'bagage' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function taxi()
    {
        return $this->belongsTo(Taxi::class);
    }

    public function trajet()
    {
        return $this->belongsTo(Trajet::class);
    }

    public function paiement()
    {
        return $this->hasOne(Paiement::class);
    }




}
