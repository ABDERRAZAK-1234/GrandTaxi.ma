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
        Schema::create('trajets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ville_depart_id')->constrained('villes')->onDelete('cascade');
            $table->foreignId('ville_arrivee_id')->constrained('villes')->onDelete('cascade');
            $table->decimal('prix', 8, 2);
            $table->enum('statut', ['actif', 'cloture'])->default('actif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trajets');
    }
};
