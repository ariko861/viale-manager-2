<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sejour extends Model
{
    use HasFactory;

    protected $fillable = ["reservation_id", "visitor_id", "confirmed", "remove_from_stats", "arrival_date", "departure_date", "remarques_visiteur", "remarques_accueil"];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}
