<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('taxis', function (Blueprint $table) {
            $table->timestamp('queue_joined_at')->nullable()->after('statuts');
        });

        // Initialize existing rows
        \Illuminate\Support\Facades\DB::statement('UPDATE taxis SET queue_joined_at = created_at');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxis', function (Blueprint $table) {
            $table->dropColumn('queue_joined_at');
        });
    }
};
