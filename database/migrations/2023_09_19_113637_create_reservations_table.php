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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->boolean('authorize_edition')->default(true);
            $table->uuid('link_token')->unique();
            $table->integer('max_days_change');
            $table->integer('max_visitors');
            $table->text('remarques_visiteur')->nullable();
            $table->text('remarques_accueil')->nullable();
            $table->timestamps();
            $table->timestamp('confirmed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
