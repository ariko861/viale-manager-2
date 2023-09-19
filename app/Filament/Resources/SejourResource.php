<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SejourResource\Pages;
use App\Filament\Resources\SejourResource\RelationManagers;
use App\Models\Sejour;
use App\Models\Visitor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SejourResource extends Resource
{
    protected static ?string $model = Sejour::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('reservation_id')
                    ->relationship('reservation', 'id')
                    ->required(),
                Forms\Components\Select::make('visitor_id')
                    ->relationship('visitor', 'nom')
                    ->required(),
                Forms\Components\Toggle::make('confirmed')
                    ->required(),
                Forms\Components\Toggle::make('remove_from_stats')
                    ->required(),
                Forms\Components\DatePicker::make('arrival_date')
                    ->required(),
                Forms\Components\DatePicker::make('departure_date'),
                Forms\Components\Textarea::make('remarques_visiteur')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('remarques_accueil')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reservation.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visitor_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('confirmed')
                    ->boolean(),
                Tables\Columns\IconColumn::make('remove_from_stats')
                    ->boolean(),
                Tables\Columns\TextColumn::make('arrival_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departure_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'create' => Pages\CreateSejour::route('/create'),
            'view' => Pages\ViewSejour::route('/{record}'),
            'edit' => Pages\EditSejour::route('/{record}/edit'),
        ];
    }
}
