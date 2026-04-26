<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce the "one driver = one taxi" rule at the database level.
 * First removes duplicate taxis (keeps the most recent one per driver),
 * reassigning reservations to the kept taxi, then adds a unique constraint.
 */
return new class extends Migration {
    public function up(): void
    {

        $duplicates = DB::table('taxis')
            ->select('driver_id', DB::raw('MAX(id) as keep_id'))
            ->groupBy('driver_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {

            $toDelete = DB::table('taxis')
                ->where('driver_id', $dup->driver_id)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');

            DB::table('reservations')
                ->whereIn('taxi_id', $toDelete)
                ->update(['taxi_id' => $dup->keep_id]);

            // Delete duplicate taxis
            DB::table('taxis')
                ->whereIn('id', $toDelete)
                ->delete();
        }

        Schema::table('taxis', function (Blueprint $table) {
            $table->unique('driver_id');
        });
    }

    public function down(): void
    {
        Schema::table('taxis', function (Blueprint $table) {
            $table->dropUnique(['driver_id']);
        });
    }
};
