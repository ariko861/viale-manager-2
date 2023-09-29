<?php

namespace App\Livewire;

use App\Enums\MessageTypes;
use App\Models\Message;
use App\Models\Reservation;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Fieldset;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Livewire\Component;

class SejourConfirmed extends Component implements HasForms, HasInfolists
{
    use InteractsWithInfolists;
    use InteractsWithForms;

    public bool $valid_token = false;
    public Reservation $reservation;

    public function mount($link_token): void {
        $reservation = Reservation::firstWhere('link_token', $link_token);

        if (!$reservation) return;

        $this->valid_token = true;
        $this->reservation = $reservation;

    }

    public function reservationInfolist(Infolist $infolist): Infolist
    {


        return $infolist
            ->record($this->reservation)
            ->schema([
                Section::make('Réservation confirmée')
                    ->schema([
                        RepeatableEntry::make('sejours')
                            ->label("Séjours")
                            ->schema([
                                Section::make('Visite')
                                    ->icon('heroicon-o-user')
                                    ->schema([
                                        Fieldset::make('Personne')->schema([
                                            TextEntry::make('visitor.nom')
                                                ->label("Nom"),
                                            TextEntry::make('visitor.prenom')
                                                ->label("Prénom"),
                                            TextEntry::make('visitor.date_de_naissance')
                                                ->label("Date de naissance")
                                                ->date(),
                                            TextEntry::make('visitor.email')
                                                ->icon('heroicon-m-envelope')
                                                ->label("Email"),
                                            TextEntry::make('visitor.phone')
                                                ->icon('heroicon-m-phone')
                                                ->label("Numéro de téléphone"),
                                        ]),
                                        Fieldset::make('Séjour')->schema([
                                            TextEntry::make('arrival_date')
                                                ->label("Date d'arrivée")
                                                ->date(),
                                            TextEntry::make('departure_date')
                                                ->label("Date de départ")
                                                ->date(),
                                        ]),
                                    ]),

                            ])
//                            ->columns(2)
                    ]),
            ]);
    }

    public function render()
    {
        return view('livewire.sejour-confirmed');
    }
}
