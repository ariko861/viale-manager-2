<?php

namespace App\Filament\Resources;

use App\Filament\Custom\SejourFilters;
use App\Filament\Resources\SejourResource\Pages;
use App\Filament\Resources\SejourResource\RelationManagers;
use App\Livewire\RoomsOccupation;
use App\Models\Reservation;
use App\Models\Sejour;
use App\Models\Visitor;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class SejourResource extends Resource
{
    protected static ?string $model = Sejour::class;
    protected static ?string $modelLabel = "Séjour";
    protected static ?string $navigationGroup = "Accueil";

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Visiteur')
//                    ->label("Visiteur")
                    ->aside()
                    ->icon('heroicon-o-user')
                    ->relationship('visitor')
                    ->compact()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->required()
                            ->disabledOn('edit')
                        ,
                        Forms\Components\TextInput::make('prenom')
                            ->required()
                            ->disabledOn('edit')
                        ,
                        Forms\Components\TextInput::make('email')
                            ->required()
                            ->disabledOn('edit')
                        ,
                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->disabledOn('edit')
                        ,
                    ]),
                Forms\Components\Section::make('Détails')
                    ->aside()
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(2)
                    ->compact()
                    ->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->prefixIcon('heroicon-o-currency-euro')
                            ->columnSpanFull()
                        ,
                        Forms\Components\Toggle::make('confirmed')
                            ->label("Confirmé")
                        ,
                        Forms\Components\Toggle::make('remove_from_stats')
                            ->label("Retirer des statistiques")
                        ,
                    ]),
                Forms\Components\Section::make('dates')
                    ->label("Dates")
                    ->icon('heroicon-o-calendar')
                    ->compact()
                    ->aside()
                    ->columns(2)
                    ->schema([
                        Forms\Components\DatePicker::make('arrival_date')
                            ->label("Date d'arrivée")
                            ->required(),
                        Forms\Components\DatePicker::make('departure_date')
                            ->label("Date de départ")
                        ,
                    ]),
                Forms\Components\Section::make('reservation')
                    ->label("Réservation")
                    ->relationship('reservation')
                    ->schema([
                        Forms\Components\Textarea::make('remarques_visiteur')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('remarques_accueil')
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('lien')
                            ->content(function(Reservation $record): HtmlString {
                                return new HtmlString("<span class='text-primary-400 cursor-pointer'>{$record->getLink()}</span>");
                            })
                        ,
                        Toggle::make('authorize_edition')
                            ->label("Autorisé à modifier sa réservation")
                        ,
                    ])
            ]);
    }


        # Non utilisée ! on utilise la table dans ListSejours
    public static function table(Tables\Table $table): Tables\Table
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
                    Tables\Columns\TextColumn::make('room.name'),
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
//            ->defaultGroup('reservation.id')
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

    public function openRoomModal()
    {
        $this->dispatch('select-room');
    }



    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSejours::route('/'),
            'create' => ReservationResource\Pages\CreateReservation::route('/create'),
            'view' => Pages\ViewSejour::route('/{record}'),
            'edit' => Pages\EditSejour::route('/{record}/edit'),
        ];
    }
}
