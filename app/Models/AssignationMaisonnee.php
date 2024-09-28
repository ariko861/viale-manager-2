<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignationMaisonnee extends Model
{
    use HasFactory;

    protected $table = "assignations_maisonnees";

    protected $fillable = ['sejour_id', 'house_id', 'planning_id'];

    public $timestamps = false;

    public function planning(): BelongsTo
    {
        return $this->belongsTo(MaisonneesPlanning::class, 'planning_id');
    }

    public function sejour(): BelongsTo
    {
        return $this->belongsTo(Sejour::class);
    }

    public function getTitleAttribute(): string
    {
        return $this->sejour?->visitor?->full_name;
    }

}
