<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * DriverProfile — linked 1:1 to a User with role=driver.
 * Contains driver-specific fields like cne and permis.
 */
class DriverProfile extends Model
{
    protected $table = 'driver_profiles';

    protected $fillable = [
        'user_id',
        'cne',
        'permis',
    ];

    /**
     * The driver this profile belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}