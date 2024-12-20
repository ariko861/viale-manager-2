<?php

namespace App\Filament\Resources\SejourResource\Pages;

use App\Filament\Custom\SejourFilters;
use App\Filament\Resources\SejourResource;
use App\Filament\Widgets\PresencesSemaineChart;
use App\Filament\Widgets\ReservationTable;
use App\Models\Reservation;
use App\Models\Sejour;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Resources\Components\Tab;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Livewire;
use Filament\Support\Enums\MaxWidth;

class ListSejours extends ListRecords
{
    protected static string $resource = SejourResource::class;
    protected static string $view = 'filament.resources.sejours.pages.list-sejours';

    public string $lien_reservation = "";

    public $update;

    protected function getHeaderWidgets(): array
    {
        return [
            PresencesSemaineChart::class,
           ReservationTable::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'tous' => Tab::make(),
            "Présents aujourd hui" => Tab::make()
                ->badge(fn() => Sejour::where('confirmed', true)->presents()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->presents())
            ,
            "Arrivent aujourd hui" => Tab::make()
                ->badge(fn() => Sejour::where('confirmed', true)->where('arrival_date', today())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('arrival_date', today()))
            ,
            "Partent aujourd hui" => Tab::make()
                ->badge(fn() => Sejour::where('confirmed', true)->where('departure_date', today())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('departure_date', today()))
            ,

            'futures arrivées' => Tab::make()
                ->badge(fn() => Sejour::where('confirmed', true)->futurArrivals()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->futurArrivals())
            ,

        ];
    }


    # Une table est créée dans listes séjours pour manipuler les dispatch
    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Split::make([

                    Tables\Columns\ColorColumn::make('reservation')
                        ->state(fn(Sejour $record) => $record->reservation->getColor())
                    ,
                    Tables\Columns\Layout\Stack::make([

                        Tables\Columns\TextColumn::make('visitor.nom')
                            ->label("Nom")
                            ->formatStateUsing(fn (string $state): string => Str::upper($state))
                            ->searchable()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('visitor.prenom')
                            ->label("Prénom")
                            ->searchable()
                            ->sortable(),
                    ])
                    ,
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('email')
                            ->label("Email")
                            ->wrap()
                            ->limit(15)
                            ->icon('heroicon-o-envelope')
                            ->grow(false)
                            ->copyable()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('reservation.contact_phone')
                            ->label("Téléphone")
                            ->grow(false)
                            ->copyable()
                            ->icon('heroicon-o-phone')
                            ->sortable(),
                    ]),
                    Tables\Columns\IconColumn::make('confirmed')
                        ->boolean()
                        ->tooltip("Confirmé ?")
                    ,
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('arrival_date')
                            ->label("Date d'arrivée")
                            ->date('D j F Y')
                            ->sortable(),
                        Tables\Columns\TextColumn::make('departure_date')
                            ->label("Date de départ")
                            ->date('D j F Y')
                            ->sortable(),
                    ]),
                    Tables\Columns\TextColumn::make('room.full_name')
                        ->wrap()
                    ,
                    Tables\Columns\TextColumn::make('price')
                        ->label("Prix choisi")
                        ->money('eur'),

                ]),

            ])

            ->defaultSort('arrival_date')
            ->groups([
                Tables\Grouping\Group::make('reservation.id')
                    ->label("Réservation")
                    ->getDescriptionFromRecordUsing(function(Sejour $record): ?string {
                        return $record->getRemarques();
                    }),
            ])
            ->defaultGroup('reservation.id')
            ->filters([
                //
                Tables\Filters\Filter::make('remove_past')
                    ->label("Ne pas afficher les séjours passés")
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->scopes('withoutPast'))
                    ->default(),
                Tables\Filters\Filter::make('confirmed')
                    ->label("Confirmés uniquement")
                    ->query(fn (Builder $query): Builder => $query->confirmed())
                    ->toggle()
                ,
                SejourFilters::dateFilter('arrival_date'),
                SejourFilters::dateFilter('departure_date'),
                SejourFilters::presenceFilter('presence_filter'),


            ], layout: FiltersLayout::Modal)
            ->filtersFormWidth(MaxWidth::FourExtraLarge)
            ->filtersFormSchema(fn (array $filters): array => [
                $filters['remove_past'],
                $filters['confirmed'],
                Fieldset::make("Date d'arrivée entre")
                    ->schema([
                        $filters['arrival_date'],
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                ,
                Fieldset::make("Date de départ entre")
                    ->schema([
                        $filters['departure_date'],
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                ,
                Fieldset::make("Présents entre")
                    ->schema([
                        $filters['presence_filter'],
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                ,
            ])
            ->recordUrl(
                null
            )
            ->actions([
                # Action pour choisir la chambre
                Tables\Actions\Action::make('select_room')
                    ->label("Sélectionner une chambre")
                    ->visible(fn(Sejour $record) => Auth::user()->can('update', $record) )
                    ->color('info')
                    ->iconButton()
                    ->icon('heroicon-o-home')
                    ->action(function (Sejour $record) {
                        $startDate = $record->estEnCours() ? today() : $record->arrival_date;
                        $endDate = $record->departure_date;
                        $this->dispatch('select-room', [$startDate, $endDate, $record->id]);
                    }),
                # Action pour changer les dates
                Tables\Actions\Action::make('edit_dates')
                    ->label("Modifier les dates")
                    ->visible(fn(Sejour $record) => Auth::user()->can('update', $record) )
                    ->color('warning')
                    ->iconButton()
                    ->icon('heroicon-o-calendar-days')
                    ->fillForm(fn(Sejour $record): array => [
                        'arrival_date' => $record->arrival_date,
                        'departure_date' => $record->departure_date,
                        'no_departure_date' => !$record->departure_date,
                    ])
                    ->form([
                        DatePicker::make('arrival_date')
                            ->label("Date d'arrivée")
                            ->required()
                        ,
                        DatePicker::make('departure_date')
                            ->label("Date de départ")
                            ->required(fn(Get $get) => !$get('no_departure_date'))
                            ->live()
                            ->afterStateUpdated(function($state, Set $set){
                                if ($state){
                                    $set('no_departure_date', false);
                                }
                            })
                        ,
                        Toggle::make('no_departure_date')
                            ->live()
                            ->label("Ne connait pas sa date de départ")
                            ->afterStateUpdated(function($state, Set $set){
                                if ($state){
                                    $set('departure_date', null);
                                }
                            })
                    ])
                    ->action(function (Sejour $record, array $data) {
                        $record->arrival_date = $data['arrival_date'];
                        $record->departure_date = $data['departure_date'];
                        $record->save();
                        Notification::make('sejour_updated')
                            ->title("Les dates du séjour ont été mises à jour")
                            ->icon('heroicon-o-calendar')
                            ->success()
                            ->send();
                    }),
                # Action pour ajouter une absence durant le séjour
                Tables\Actions\Action::make('add_break')
                    ->label("Ajouter une absence")
                    ->icon('heroicon-o-sun')
                    ->color('warning')
                    ->iconButton()
                    ->visible(fn(Sejour $record) => Auth::user()->can('update', $record) )
                    ->form(fn(Sejour $record) => [
                        DatePicker::make('begin_date')
                            ->label("Début absence")
                            ->required()
                            ->live()
                            ->minDate($record->arrival_date)
                            ->maxDate($record->departure_date)
                        ,
                        DatePicker::make('end_date')
                            ->label("Fin absence")
                            ->required()
                            ->live()
                            ->minDate(fn(Get $get) => $get('begin_date'))
                            ->maxDate($record->departure_date)

                        ,

                    ])
                    ->action(function (Sejour $record, array $data) {
                        $record->createBreak($data['begin_date'], $data['end_date']);
                        Notification::make('break_create')
                            ->title("Absence créée")
                            ->icon('heroicon-o-sun')
                            ->success()
                            ->send();
                    })
                ,
                # Action pour annuler une réservation
                Tables\Actions\Action::make('cancel')
                    ->label("Annuler le séjour")
                    ->icon('heroicon-o-x-circle')
                    ->iconButton()
                    ->visible(fn(Sejour $record) => $record->isDeletable() )
                    ->color('danger')
                    ->modalHeading("Annulation du séjour")
                    ->modalDescription(fn(Sejour $record): string => "Vous allez annuler le séjour de {$record->visitor?->prenom} {$record->visitor?->nom}, êtes-vous sûr·e ?")
                    ->action(function (Sejour $record) {
                        $record->delete();
                        Notification::make('sejour-canceled')
                            ->title("Séjour annulé")
                            ->body("Vous avez annulé le séjour de {$record->visitor?->prenom} {$record->visitor?->nom}")
                            ->warning()
                            ->send()
                        ;
                    })
                ,
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->update = !$this->update;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getFooter(): ?View
    {
        return view('rooms-occupation');

    }

}
