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
        'queue_joined_at',
    ];

    /**
     * Append the computed image_url to JSON responses.
     */
    protected $appends = ['image_url'];


    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * The trajet this taxi is assigned to.
     */
    public function trajet()
    {
        return $this->belongsTo(Trajet::class, 'trajet_id');
    }

    /**
     * All reservations for this taxi.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }


    /**
     * Full URL for the taxi image.
     * Returns default image if none uploaded.
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/taxi_default.png');
    }
}
