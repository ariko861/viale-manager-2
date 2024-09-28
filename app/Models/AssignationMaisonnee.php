<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignationMaisonnee extends Model
{
    use HasFactory;

    protected $table = "sejours_in_maisonnees";

    public function planning(): BelongsTo
    {
        return $this->belongsTo(MaisonneesPlanning::class);
    }

}
