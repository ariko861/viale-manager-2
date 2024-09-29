<?php

namespace App\Filament\Pages;

use App\Models\AssignationMaisonnee;
use App\Models\House;
use App\Models\MaisonneesPlanning;
use App\Models\Visitor;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Reactive;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Illuminate\Support\Collection;

class MaisonneesKanbanBoard extends KanbanBoard
{

    use HasPageShield;
    protected static string $model = AssignationMaisonnee::class;
    protected static string $recordStatusAttribute = 'house_id';

    protected static ?string $navigationLabel = 'Maisonnées';
//    public bool $disableEditModal = true;

//    protected static ?string $navigationGroup = 'Accueil';
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
                $this->planning = MaisonneesPlanning::create([
                    'begin' => $data['begin'],
                    'end' => $data['end'],
                ]);
                $this->planning->houses()->attach($data['houses']);
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
        if ($this->planning->houses()->exists()){
            return $this->planning->houses->prepareForKanban();
        } else {
            return House::prepareForKanban();
        }
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
                ->label("Recommencer les maisonnées")
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->modalHeading("Recommencer le partage des maisonnées")
                ->modalDescription("Cette action va remettre à zéro la répartition en maisonnées, êtes-vous sûr·e ?")
                ->requiresConfirmation()
                ->action(function (){
                    $this->planning->resetPlanning();
                })
            ,
            Action::make('change_planning')
                ->label("Changer de planning")
                ->color('info')
                ->icon('heroicon-o-calendar-date-range')
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
            ,
            Action::make('modify_houses')
                ->label("Modifier les maisons")
                ->color('success')
                ->icon('heroicon-o-home')
                ->form([
                    Select::make('houses')
                        ->label("Les maisons à utiliser pour ce planning")
                        ->options(House::query()->isMaisonnee()->pluck('name', 'id'))
                        ->multiple()
                        ->default(fn() => $this->planning->houses()->pluck('id')->toArray())
                    ,
                ])
                ->action(function(array $data){
                    $this->planning->houses()->sync($data['houses']);
                    $this->planning->resetWhereNoMaisonnee();
                })
            ,
        ];
    }

    protected function getEditModalFormSchema(null|int $recordId): array
    {
        $assignation = AssignationMaisonnee::query()->find($recordId);
        return [
            Fieldset::make('Séjour')
                ->relationship('sejour')
                ->schema([
                    Fieldset::make('Visiteur')
                        ->relationship('visitor')
                        ->schema([
                            TextInput::make('nom')
                                ->disabled()
                            ,
                            TextInput::make('prenom')
                                ->disabled()
                            ,
                            DatePicker::make('date_de_naissance')
                                ->disabled()
                            ,
                            Placeholder::make('age')
                                ->content(fn() => $assignation?->sejour?->visitor?->age)
                            ,
                        ])
                    ,
                    Fieldset::make('Dates')
                        ->schema([
                            DatePicker::make('arrival_date')
                                ->label("Arrivée")
                                ->disabled()
                            ,
                            DatePicker::make('departure_date')
                                ->label("Départ")
                                ->disabled()
                            ,

                        ]),
                ])
            ,

        ];
    }

    protected function editRecord($recordId, array $data, array $state): void
    {

    }

}
