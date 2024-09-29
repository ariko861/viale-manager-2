<?php

namespace App\Models;

use App\Filament\Resources\RoomResource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sejour extends Model
{
    use HasFactory;

    protected $fillable = ["reservation_id", "visitor_id", "price", "confirmed", "remove_from_stats", "arrival_date", "departure_date", "remarques_visiteur", "remarques_accueil"];

    protected $casts = [
        'arrival_date' => 'date:Y-m-d',
        'departure_date' => 'date:Y-m-d',
    ];

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

    public function accompagnants(): HasMany
    {
        return $this->hasMany(self::class, 'reservation_id', 'reservation_id');
    }

    public function getNuitsAttribute(): int
    {
        $arrival_date = Carbon::parse($this->arrival_date);
        $departure_date = Carbon::parse($this->departure_date);
        return $departure_date->diffInDays($arrival_date, absolute: true);
    }

    public function getTotalPriceAttribute(): float
    {
        return $this->nuits * ($this->price ?? 0 );
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

    /**
     *
     * Récupère les séjours présents entre deux dates
     *
     * @param Builder $query
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return void
     */
    public function scopeWithinDates(Builder $query, Carbon|string $startDate, Carbon|string $endDate, bool $excludeArrivals = false, bool $excludeDepartures = false): void {
        $lessOperator = $excludeArrivals ? '<' : '<=';
        $moreOperator = $excludeDepartures ? '>' : '>=';

        $query->whereDate('arrival_date', $lessOperator, $endDate)
            ->where(function (Builder $q) use ($startDate, $moreOperator){
                $q->whereDate('departure_date', $moreOperator, $startDate)
                    ->orWhereNull('departure_date');
            });
    }

    public function scopePresents(Builder $query): void {
        $query->withinDates(today(), today());
    }

    public function scopeFuturArrivals(Builder $query, ?Carbon $date = null): void {
        if (!$date) $date = today();
        $query->where('arrival_date', '>=', $date);
    }

    public function scopeConfirmed(Builder $query): void {
        $query->where('confirmed', true);
    }

    public function scopeInStats(Builder $query): void {
        $query->where('remove_from_stats', false);
    }


}
