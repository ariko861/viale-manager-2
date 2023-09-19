<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ["confirmed_at", "remarques_accueil", "remarques_visiteur"];
    protected $attributes = [
        'max_days_change' => 255,
        'max_visitors' => 5
    ];

    public function sejours(): HasMany
    {
        return $this->hasMany(Sejour::class);
    }

    protected function isConfirmed(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => !( $this->confirmed_at == null ),
        );
    }

    public function generateLinkToken(): void {
        $this->link_token = Str::uuid();
    }

    public function getLink(): string {
        return urldecode(route('home') . '?link_token=' . $this->link_token);
    }

    public static function createQuickReservation(int $max_days_change = 5, int $max_visitors = 5): self
    {
        $newReservation = new self();
        $newReservation->generateLinkToken();
        $newReservation->max_days_change = $max_days_change;
        $newReservation->max_visitors = $max_visitors;
        $newReservation->save();
        return $newReservation;
    }

    public function scopeIsConfirmed(Builder $query): void
    {
        $query->whereNotNull('confirmed_at');
    }

    protected static function booted(): void
    {
        static::creating(function (Reservation $reservation) {
            $reservation->generateLinkToken();
        });
    }
}
