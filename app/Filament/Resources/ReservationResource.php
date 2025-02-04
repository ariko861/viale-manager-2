<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static bool $shouldRegisterNavigation = false;


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Toggle::make('link_sent')
                    ->label("Lien du formulaire envoyé")
                ,
                Forms\Components\Toggle::make('authorize_edition')
                    ->label("Peut modifier sa réservation")
                ,
                Forms\Components\TextInput::make('contact_email')
                    ->email()
                    ->label("Email de contact")
                ,
                Forms\Components\TextInput::make('contact_phone')
                    ->label("Téléphone de contact")
                ,
                Forms\Components\RichEditor::make('remarques_accueil'),

                Forms\Components\RichEditor::make('remarques_visiteur')
                    ->disabled()
                    ->columnSpanFull()
                ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('authorize_edition')
                    ->boolean(),
                Tables\Columns\IconColumn::make('isConfirmed')
                    ->boolean(),
                Tables\Columns\TextColumn::make('max_days_change')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_visitors')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('confirmed_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
                Tables\Filters\Filter::make('isConfirmed')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('confirmed_at'))
                    ->toggle()
                    ->default(),

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
            RelationManagers\SejoursRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'view' => Pages\ViewReservation::route('/{record}'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
