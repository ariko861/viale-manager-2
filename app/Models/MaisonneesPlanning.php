<?php

namespace App\Models;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class MaisonneesPlanning extends Model
{
    use HasFactory;

    protected $table = "maisonnees_planning";

    protected $fillable = ['begin', 'end'];

    protected $casts = [
        'begin' => 'date',
        'end' => 'date'
    ];

    public function assignations(): HasMany
    {
        return $this->hasMany(AssignationMaisonnee::class, 'planning_id');
    }

    public function houses(): BelongsToMany
    {
        return $this->belongsToMany(House::class, 'houses_in_maisonnees_planning', 'planning_id');
    }

    public function displayName(): Attribute
    {
        return Attribute::make(
            get: fn(): string => "du {$this->begin->toFormattedDayDateString()} au {$this->end->toFormattedDayDateString()  }"
        );
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

    public function resetPlanning(): void
    {
        $this->assignations()->each(fn(AssignationMaisonnee $assignation) => $assignation->update(['house_id' => 0]));
        $this->preparePlanning();
    }

    public function prepareHousesForKanban(): Collection
    {
        $houses = $this->houses()->select('name', 'id')->where('community', true)->without('rooms')->get()->toArray();
        $houses = array_map(function($item){
            $item = array_combine(['id', 'title'], [$item['id'], $item['name'].' ('.$this->assignations()->where('house_id', $item['id'])->count().')']);
            return $item;
        }, $houses);
        array_unshift($houses, ['id' => 0, 'title' => "à placer (".$this->assignations()->where('house_id', 0)->count().')']);

        return collect($houses);
    }

    public function resetWhereNoMaisonnee(): void
    {
        # On remet à 0 les assignations dont la maison n'est pas dans les maisons du planning.
        # Ceci principalement pour éviter de supprimer une maison dans le planning et de supprimer des séjours avec
        $this->assignations()->whereNotIn('house_id', $this->houses()->pluck('id')->toArray())->update(['house_id' => 0]);
    }

    public static function getCreateAction(bool $form = false): Action|\Filament\Forms\Components\Actions\Action
    {
        if ($form){
            $action = \Filament\Forms\Components\Actions\Action::class;
        } else {
            $action = Action::class;
        }
        $plannings = self::query();
        if ($plannings->count() === 0 || $plannings->current()->count() === 0){
            $begin_week = Carbon::today()->startOfWeek();
            $end_week = Carbon::today()->endOfWeek();
        } else {
            $planning = $plannings->latest('end')->first();
            $begin_week = $planning->end->copy()->addDay();
            $end_week = $begin_week->copy()->addDays(6);

        }

        return $action::make('create_planning')
            ->label("Créer un nouveau planning de maisonnées")
            ->color('success')
            ->icon('heroicon-o-plus')
            ->form([
                DatePicker::make('begin')
                    ->label("Début de la période")
                    ->default($begin_week)
                ,
                DatePicker::make('end')
                    ->label("Fin de la période")
                    ->default($end_week)
                ,
                Select::make('houses')
                    ->label("Les maisons à utiliser pour ce planning")
                    ->options(House::query()->isMaisonnee()->pluck('name', 'id'))
                    ->multiple()
                ,
            ])->action(function(array $data){
                $planning = MaisonneesPlanning::query()->create([
                    'begin' => $data['begin'],
                    'end' => $data['end'],
                ]);
                $planning->houses()->attach($data['houses']);
                $planning->preparePlanning();
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
