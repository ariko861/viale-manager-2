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
        Schema::create('assignations_maisonnees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sejour_id');
            $table->foreignId('house_id');
            $table->foreignId('planning_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignations_maisonnees');
    }
};
