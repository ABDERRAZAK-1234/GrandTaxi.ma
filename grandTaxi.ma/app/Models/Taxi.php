<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Taxi extends Model
{
    protected $fillable = ['matricule', 'capacite', 'status', 'driver_id'];

    public function chauffeur()
    {
        return $this->belongsTo(User::class);
    }
}


