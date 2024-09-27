<?php

namespace App\Filament\Resources\VisitorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SejoursRelationManager extends RelationManager
{
    protected static string $relationship = 'sejours';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('arrival_date')
                    ->label("Arrivée")
                    ->date()
                ,
                Tables\Columns\TextColumn::make('departure_date')
                    ->label("Départ")
                    ->date()
                ,
                Tables\Columns\TextColumn::make('room.full_name')
                    ->label("Chambre")
                ,

            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}
