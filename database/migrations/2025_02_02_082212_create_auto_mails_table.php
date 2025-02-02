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
        Schema::create('auto_mails', function (Blueprint $table) {
            $table->id();
            $table->string('sujet');
            $table->text("body");
            $table->string('type');
            $table->integer('time_delta')->comment("donne le nombre de jour de différence avec l'évènement visé")->nullable();
            $table->boolean('actif')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_mails');
    }
};
