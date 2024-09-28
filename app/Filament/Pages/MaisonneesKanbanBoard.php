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
use Livewire\Attributes\Reactive;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Illuminate\Support\Collection;

class MaisonneesKanbanBoard extends KanbanBoard
{
    protected static string $model = AssignationMaisonnee::class;
    protected static string $recordStatusAttribute = 'house_id';

    protected static ?string $navigationLabel = 'Maisonnées';

    protected static ?string $navigationGroup = 'Accueil';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 10;

    public MaisonneesPlanning $planning;

    public function mount(): void
    {
        if (MaisonneesPlanning::query()->current()->count() === 0) {
            $this->defaultAction = 'onboarding';
            $planning = new MaisonneesPlanning();
            $planning->begin = today();
            $planning->end = today();
            $this->planning = $planning;
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
                $this->planning = MaisonneesPlanning::create([
                    'begin' => $data['begin'],
                    'end' => $data['end'],
                ]);
                $this->planning->preparePlanning();
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
        return $this->planning?->assignations;
    }

    public function getHeading(): string|Htmlable
    {
        if ($this->planning?->id){
            return 'Maisonnées '. $this->planning?->display_name;
        }

        return 'Maisonnées';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_maisonnees')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (){
                    $this->planning->resetPlanning();
                })
            ,
            Action::make('change_planning')
                ->color('info')
                ->form([
                    Select::make('plannings')
                        ->options(MaisonneesPlanning::query()->where('end', '>=', today())->get()->mapWithKeys(function(MaisonneesPlanning $planning){
                            return [$planning->id => $planning->display_name];
                        }))
                        ->selectablePlaceholder(false)
                        ->default(fn() => $this->planning->id)
                        ->hintAction(
                            MaisonneesPlanning::getCreateAction(form: true),
                        )
                ])
                ->modalSubmitActionLabel("Changer de planning")
                ->action(function (array $data){
                    $this->planning = MaisonneesPlanning::query()->findOrFail($data['plannings']);
                    $this->planning->preparePlanning();
                })
        ];
    }

}
