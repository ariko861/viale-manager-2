<?php

namespace App\Models;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public static function getCreateAction(bool $form = false): Action|\Filament\Forms\Components\Actions\Action
    {
        if ($form){
            $action = \Filament\Forms\Components\Actions\Action::class;
        } else {
            $action = Action::class;
        }
        $begin_week = Carbon::today()->startOfWeek();
        $end_week = Carbon::today()->endOfWeek();
        return $action::make('create_planning')
            ->label("Créer un nouveau planning de maisonnées")
            ->color('success')
            ->icon('heroicon-o-plus')
            ->form([
                DatePicker::make('begin')
                    ->default($begin_week)
                ,
                DatePicker::make('end')
                    ->default($end_week)
                ,
            ])->action(function(array $data){
                $planning = MaisonneesPlanning::query()->create([
                    'begin' => $data['begin'],
                    'end' => $data['end'],
                ]);
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
