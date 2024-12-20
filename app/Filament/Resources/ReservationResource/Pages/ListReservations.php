<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\Reservation;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;
    protected static string $view = 'filament.resources.reservations.pages.list-reservations';

    public string $lien_reservation = "";

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('create_link_form')
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
                        ->required(),
                ])
                ->action(function(array $data): void {
                    $reservation = Reservation::createQuickReservation(max_days_change: $data['max_days_change'], max_visitors: $data["max_visitors"]);
                    $this->lien_reservation = $reservation->getLink();
                    $this->dispatch('open-modal', id:"link-display");
                })
        ];
    }
}
