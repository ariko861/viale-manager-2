<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use App\Models\Sejour;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomResource extends Resource
{

    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $modelLabel = 'Chambre';
    protected static ?int $navigationSort = 7;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('house_id')
                    ->label("House")
                    ->relationship('house', 'name')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\Toggle::make('community')
                            ->label("Is a community"),
                        Forms\Components\Toggle::make('displayHouseNameWithRoom')
                            ->label("Afficher le nom de la maison avec la chambre"),

                    ]),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('beds')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('house.name')
                    ->label("Maison")
                    ->sortable()
                    ->icon('heroicon-o-home')
                ,
                Tables\Columns\TextColumn::make('name')
                    ->label("Nom")
                    ->searchable(),
                Tables\Columns\TextColumn::make('beds')
                    ->label("Lits")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('occupants')
                    ->label("Occupants")
                    ->getStateUsing(function(Room $record) {
                        return $record->sejours->map(function (Sejour $sejour) {
                            return $sejour->visitor->full_name;
                        });
                    })
                    ->listWithLineBreaks()
                ,
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Tables\Grouping\Group::make('house.name')
                    ->label("Maison"),
            ])
            ->paginated(false)
            ->defaultGroup('house.name')
            ->defaultSort('house.name')
            ->filters([
                Tables\Filters\Filter::make('has_occupants')
                    ->label("Chambres occupées")
                    ->default()
                    ->query(fn(Builder $query) => $query->whereHas('sejours'))
            ])
            ->actions([
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\SejoursRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
