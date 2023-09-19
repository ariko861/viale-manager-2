<?php

namespace App\Models;

use App\Filament\Resources\RoomResource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function getRemarques(): ?string
    {
        $remarques_visiteurs = $this->reservation?->remarques_visiteur ?? "Pas de remarque";
        $remarques_accueil = $this->reservation?->remarques_accueil ?? "Pas de remarque";
        return "Remarques visiteur: {$remarques_visiteurs}\nRemarques accueil: {$remarques_accueil}";
    }

    public function scopeWithoutPast(Builder $query): void {
        $query->where('departure_date', '>=', Carbon::today())->orWhereNull('departure_date');
    }
}
