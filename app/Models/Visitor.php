<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
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

    public static function mergeVisitors(array $visitors_id, int $visitor_ref): void
    {
        # On récupère tous les visiteurs qui ne sont pas le visiteur de référence
//        $visitors = Visitor::query()->whereIn('id', $visitors_id)->whereNot('id', $visitor_ref)->get();
        # On récupère le visiteur de référence
        if ( !Visitor::query()->find($visitor_ref)) return;

        # On récupère les séjours dont on doit modifier les id visiteurs
        $sejours = Sejour::query()->whereIn('visitor_id', $visitors_id)->update([
            'visitor_id' => $visitor_ref,
        ]);

        # On supprime les autres visiteurs
        Visitor::query()->whereIn('id', $visitors_id)->whereNot('id', $visitor_ref)->delete();
    }

    /**
     * Renvoi une référence possible pour un merge de visiteurs
     *
     * @param array $visitors_id
     * @return int
     */
    public static function lookReferenceForMerge(array $visitors_id): Visitor|Collection|null
    {
        $visitors_q = self::query()->whereIn('id', $visitors_id);
        $visitors_with_user = clone $visitors_q;
        $visitors_with_user = $visitors_with_user->whereHas('user')->get();
        if ($visitors_with_user->count() > 1){
            return null;
        }
        if ($visitors_with_user->count() === 1){
            return $visitors_with_user->first();
        }
        $visitors_with_mail = clone $visitors_q;
        $visitors_with_mail = clone $visitors_with_mail->whereNotNull('email')->get();
        if ($visitors_with_mail->count() > 0){
            return $visitors_with_mail;
        }
        return $visitors_q->get();

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

    public function scopeHasUser(Builder $query): void
    {
        $query->whereHas('user');
    }

}
