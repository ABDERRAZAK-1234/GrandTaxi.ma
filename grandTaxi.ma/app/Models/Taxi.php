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
        'image',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function trajet()
    {
        return $this->belongsTo(Trajet::class, 'trajet_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }


    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/taxi_default.png');
    }

    protected $appends = ['image_url'];

    public function driverProfile()
    {
        return $this->hasOne(DriverProfile::class);
    }

}


