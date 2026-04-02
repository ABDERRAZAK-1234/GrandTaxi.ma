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
            $table->string('ville_depart');
            $table->string('ville_arrive');
            $table->dateTime('date_depart');
            $table->float('prix');
            $table->enum('statuts', ['ouvert', 'complet', 'termine']);
            $table->foreignId('taxi_id')->constrained('taxis');
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
