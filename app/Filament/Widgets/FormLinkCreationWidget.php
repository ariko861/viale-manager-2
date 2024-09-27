<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class FormLinkCreationWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static string $view = 'filament.widgets.form-link-creation-widget';
    protected static ?int $sort = 0;

    public string $lien_reservation = "";

    public function createReservationForm(): Action
    {
        return Action::make('create_link_form')
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
                Notification::make('link-created')
                    ->title("Lien de réservation créé")
                    ->body('erer')
                    ->success()
                    ->persistent()
                    ->send()
                ;

                $this->dispatch('open-modal', id:"link-display");
            });
    }


}
