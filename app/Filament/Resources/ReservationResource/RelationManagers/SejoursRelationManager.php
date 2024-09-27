<?php

namespace App\Filament\Resources\ReservationResource\RelationManagers;

use App\Models\Profile;
use App\Models\Visitor;
use Faker\Provider\Text;
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
                Forms\Components\Select::make('visitor_id')
                    ->relationship('visitor', 'nom')
                    ->getOptionLabelFromRecordUsing(fn(Visitor $record) => $record->full_name)
                    ->searchable(['nom', 'prenom'])
                ,
                Forms\Components\Fieldset::make('dates')
                    ->label("Dates")
                    ->schema([
                        Forms\Components\DatePicker::make('arrival_date'),
                        Forms\Components\DatePicker::make('departure_date'),
                    ]),
                Forms\Components\Select::make('price')
                    ->label("Prix")
                    ->options(Profile::retrieveOptions())
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('visitor.nom'),
                Tables\Columns\TextColumn::make('visitor.prenom'),
                Tables\Columns\TextColumn::make('arrival_date')
                    ->date()
                ,
                Tables\Columns\TextColumn::make('departure_date')
                    ->date()
                ,
                Tables\Columns\TextColumn::make('price')
                    ->money('eur')
                ,

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
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
}
