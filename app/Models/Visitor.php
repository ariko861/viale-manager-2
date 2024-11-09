<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visitor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["nom", "prenom", "confirmed", "date_de_naissance", "email", "phone"];


    public function sejours(): HasMany
    {
        return $this->hasMany(Sejour::class)->chaperone('visitor');
    }


    public function last_sejour(): HasOne
    {
        return $this->sejours()->one()->latest('departure_date');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => "{$this->prenom} {$this->nom}",
        );
    }
    public function getFullNameAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    protected function Age(): Attribute
    {
        $birthdate = Carbon::parse($this->date_de_naissance);
        return Attribute::make(
            get: fn(): int => $birthdate->age
        );
    }

    public function scopeBetweenAges(Builder $query, int $startAge, int $endAge): void
    {
        $startDate = Carbon::today()->subYears($endAge);
        $endDate = Carbon::today()->subYears($startAge);
        $query->whereBetween('date_de_naissance', [$startDate, $endDate]);
    }

}
