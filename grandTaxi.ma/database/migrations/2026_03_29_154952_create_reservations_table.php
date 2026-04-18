<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->integer('nombre_place');
            $table->boolean('bagage');
            $table->decimal('prix_total', 8, 2);
            $table->enum('statut', ['ouvert', 'complet', 'termine'])->default('ouvert');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('trajet_id')->constrained('trajets');
            $table->foreignId('taxi_id')->constrained('taxis');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
