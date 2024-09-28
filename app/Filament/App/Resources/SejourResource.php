<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SejourResource\Pages;
use App\Filament\App\Resources\SejourResource\RelationManagers;
use App\Models\Sejour;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SejourResource extends Resource
{
    protected static ?string $model = Sejour::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $pluralModelLabel = "Vos séjours";

    protected static ?string $modelLabel = "Votre séjour";

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('visitor_id', Auth::user()->visitor_id)->where('departure_date', '<', today());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->visitor()->exists();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\DatePicker::make('arrival_date')
                    ->required(),
                Forms\Components\DatePicker::make('departure_date'),
                Forms\Components\Select::make('room_id')
                    ->relationship('room', 'name'),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('€'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('arrival_date')
                    ->label("Arrivée")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label("Départ")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nuits')
                    ->numeric(),

                Tables\Columns\TextColumn::make('room.full_name')
                    ->label("Chambre")
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
            ]);
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
            'view' => Pages\ViewSejour::route('/{record}'),
        ];
    }
}
