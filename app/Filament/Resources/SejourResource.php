<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SejourResource\Pages;
use App\Filament\Resources\SejourResource\RelationManagers;
use App\Livewire\RoomsOccupation;
use App\Models\Reservation;
use App\Models\Sejour;
use App\Models\Visitor;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

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
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('reservation')
                    ->state(fn(Sejour $record) => $record->reservation->getColor())
                ,
                Tables\Columns\TextColumn::make('visitor.nom')
                    ->label("Nom")
                    ->sortable(),
                Tables\Columns\TextColumn::make('visitor.prenom')
                    ->label("Prénom")
                    ->sortable(),
                Tables\Columns\IconColumn::make('confirmed')
                    ->boolean(),
                Tables\Columns\ToggleColumn::make('remove_from_stats'),
                Tables\Columns\TextColumn::make('arrival_date')
                    ->label("Date d'arrivée")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departure_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.name'),
                Tables\Columns\TextColumn::make('profile.price')
                    ->label("Prix choisi")
                    ->money('eur'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('arrival_date')
            ->groups([
                Group::make('reservation.id')
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
                    ->default()

            ])
            ->actions([
                Tables\Actions\Action::make('select_room'),
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
