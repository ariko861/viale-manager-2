<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaisonneesPlanning extends Model
{
    use HasFactory;

    protected $table = "maisonnees_planning";

    public function assignations(): HasMany
    {
        return $this->hasMany(AssignationMaisonnee::class);
    }

}
