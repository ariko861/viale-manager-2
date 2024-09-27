<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Nette\Utils\Html;

class ReservationTable extends BaseWidget
{

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()->orderByDesc('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('link_token')
                    ->label("Lien formulaire")
                    ->copyable()
                    ->copyableState(fn(Reservation $record): string => $record->getLink())
                    ->formatStateUsing(fn(Reservation $record): string => $record->getLink())
                ,
                Tables\Columns\TextColumn::make('remarques_accueil')
                    ->html()
                ,
                Tables\Columns\ToggleColumn::make('link_sent')
                    ->label("Lien envoyé")
                ,

            ])
            ->defaultPaginationPageOption(5)
            ->headerActions([
                Tables\Actions\Action::make('create_link_form')
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
                            ->required()
                        ,
                        RichEditor::make('remarques_accueil'),
                    ])
                    ->action(function(array $data): void {
                        $reservation = Reservation::createQuickReservation(max_days_change: $data['max_days_change'], max_visitors: $data["max_visitors"], remarques_accueil: $data['remarques_accueil']);
//                        $this->lien_reservation = $reservation->getLink();
                        Notification::make('link-created')
                            ->title("Lien de réservation créé")
                            ->body(new HtmlString("Le lien de réservation <a href='{$reservation->getLink()}'>{$reservation->getLink()}</a> a été créé"))
                            ->success()
                            ->persistent()
                            ->send()
                        ;

                    }),
            ])
            ;
    }
}
