<?php

namespace App\Filament\Resources\SejourResource\Pages;

use App\Filament\Custom\SejourFilters;
use App\Filament\Resources\SejourResource;
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
                ]),
                Tables\Columns\IconColumn::make('confirmed')
                    ->boolean()
                    ->tooltip("Confirmé ?")
                    ,
//                Tables\Columns\ToggleColumn::make('remove_from_stats'),
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
                        Tables\Columns\TextColumn::make('room.full_name'),
                        Tables\Columns\TextColumn::make('price')
                            ->label("Prix choisi")
                            ->money('eur'),
//                    Tables\Columns\TextColumn::make('created_at')
//                        ->dateTime()
//                        ->sortable()
//                        ->toggleable(isToggledHiddenByDefault: true),
//                    Tables\Columns\TextColumn::make('updated_at')
//                        ->dateTime()
//                        ->sortable()
//                        ->toggleable(isToggledHiddenByDefault: true),
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
            ->actions([
                Tables\Actions\Action::make('select_room')
//                    ->visible(fn(Sejour $record) => !$record->room()->exists() )
                    ->color('info')
                    ->iconButton()
                    ->icon('heroicon-o-home')
                    ->action(function (Sejour $record) {
                        $startDate = $record->arrival_date;
                        $endDate = $record->departure_date;
                        $this->dispatch('select-room', [$startDate, $endDate, $record->id]);
                    }),
                Tables\Actions\Action::make('edit_dates')
//                    ->visible(fn(Sejour $record) => !$record->room()->exists() )
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

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            Actions\Action::make('create_link_form')
                ->label("Création lien formulaire")
                ->color("success")
                ->icon('heroicon-o-pencil')
                ->form([
                    TextInput::make('max_days_change')
                        ->label('Nombre de jours de décalage possibles')
                        ->numeric()
                        ->default(255)
                        ->required(),
                    TextInput::make('max_visitors')
                        ->label('Nombre de visiteurs maximum')
                        ->numeric()
                        ->default(5)
                        ->required(),
                ])
                ->action(function(array $data): void {
                    $reservation = Reservation::createQuickReservation(max_days_change: $data['max_days_change'], max_visitors: $data["max_visitors"]);
                    $this->lien_reservation = $reservation->getLink();
                    $this->dispatch('open-modal', id:"link-display");
                })
        ];
    }

    public function getFooter(): ?View
    {
        return view('rooms-occupation');

    }

}
