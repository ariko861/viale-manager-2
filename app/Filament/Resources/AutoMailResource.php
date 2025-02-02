<?php

namespace App\Filament\Resources;

use App\Enums\AutoMailTypes;
use App\Filament\Resources\AutoMailResource\Pages;
use App\Filament\Resources\AutoMailResource\RelationManagers;
use App\Models\AutoMail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AutoMailResource extends Resource
{
    protected static ?string $model = AutoMail::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Configuration';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('sujet')
                    ->label("Sujet du mail")
                    ->maxLength(255)
                    ->required()
                ,
                Forms\Components\MarkdownEditor::make('body')
                    ->label("Corps du mail")
                    ->required()
                ,
                Forms\Components\Select::make('type')
                    ->options(AutoMailTypes::class)
                    ->required()
                ,
                Forms\Components\TextInput::make('time_delta')
                    ->label("Nombre de jours de décalage")
                    ->hint("Placez des nombres négatifs pour envoyer le mail avant l'évènement")
                    ->integer()
                ,

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sujet'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\ToggleColumn::make('actif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('test')
                    ->label("Test mail")
                    ->form([
                        Forms\Components\TextInput::make('test-mail')
                            ->email()
                            ->required()
                    ])
                    ->icon('heroicon-o-paper-airplane')
                    ->action(function(array $data, AutoMail $record){
                        $record->sendTo($data['test-mail']);
                        Notification::make()
                            ->title("Email de test envoyé")
                            ->success()
                            ->send();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListAutoMails::route('/'),
            'create' => Pages\CreateAutoMail::route('/create'),
            'edit' => Pages\EditAutoMail::route('/{record}/edit'),
        ];
    }
}
