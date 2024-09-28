<?php

namespace App\Filament\Pages;

use App\Models\AssignationMaisonnee;
use App\Models\House;
use App\Models\MaisonneesPlanning;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Illuminate\Contracts\Support\Htmlable;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Illuminate\Support\Collection;

class MaisonneesKanbanBoard extends KanbanBoard
{
    protected static string $model = AssignationMaisonnee::class;
    protected static string $recordStatusAttribute = 'house_id';

    public MaisonneesPlanning $planning;

    public function mount(): void
    {
        if (MaisonneesPlanning::query()->current()->count() === 0) {
            $this->defaultAction = 'onboarding';
        } else {
            $this->planning = MaisonneesPlanning::query()->current()->first();
            $this->planning->preparePlanning();
        }
    }

    public function onboardingAction(): Action
    {
        $begin_week = Carbon::today()->startOfWeek();
        $end_week = Carbon::today()->endOfWeek();
        return Action::make('create_planning')
            ->form([
                DatePicker::make('begin')
                    ->default($begin_week)
                ,
                DatePicker::make('end')
                    ->default($end_week)
                ,
            ])->action(function(array $data){
                MaisonneesPlanning::create([
                    'begin' => $data['begin'],
                    'end' => $data['end'],
                ]);
            });

    }

    public function onStatusChanged(int $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
//        dd($status);
        AssignationMaisonnee::find($recordId)->update(['house_id' => $status]);
//        User::setNewOrder($toOrderedIds);
    }

    protected function statuses(): Collection
    {
        return House::prepareForKanban();
    }

    protected function records(): Collection
    {
        return $this->planning->assignations;
    }

}
