<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaisonneesPlanning extends Model
{
    use HasFactory;

    protected $table = "maisonnees_planning";

    protected $fillable = ['begin', 'end'];

    public function assignations(): HasMany
    {
        return $this->hasMany(AssignationMaisonnee::class, 'planning_id');
    }

    public function preparePlanning(): void
    {
        # On vérifie les assignations du planning de maisonnées
        $ids = $this->assignations()->pluck('sejour_id')->toArray();
        # On récupère les séjours non intégrés
        $sejours = Sejour::query()->whereNotIn('id', $ids)->withinDates($this->begin, $this->end)->get();
        $sejours->each(function (Sejour $sejour){
           AssignationMaisonnee::query()->create([
               'sejour_id' => $sejour->id,
               'planning_id' => $this->id,
               'house_id' => 0,
           ]);
        });
    }

    public function scopeCurrent(Builder $query, $date = null): void
    {
        if (!$date){
            $date = today();
        }
        $query->whereDate('begin', '<=', $date)
            ->whereDate('end', '>=', $date);
    }

}
