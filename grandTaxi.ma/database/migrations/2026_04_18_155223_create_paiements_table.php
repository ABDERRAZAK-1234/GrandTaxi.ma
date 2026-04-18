<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {

            // change montant type (float -> decimal)
            $table->decimal('montant', 10, 2)->change();

            // add payment_intent_id
            $table->string('payment_intent_id')->nullable()->after('methode');

            // update statut enum
            $table->dropColumn('statut');
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->enum('statut', ['pending', 'confirmed', 'failed'])->default('pending');
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {

            $table->float('montant')->change();

            $table->dropColumn('payment_intent_id');

            $table->dropColumn('statut');
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->enum('statut', ['confirmed', 'failed'])->default('failed');
        });
    }
};
