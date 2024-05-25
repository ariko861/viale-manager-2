<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\Profile;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;
    protected static string $resource = ReservationResource::class;

    public $departure_date;
    public $arrival_date;
    public bool $no_departure_date = false;
    protected function getSteps(): array
    {
        return [
            Step::make('Dates')
                ->icon("heroicon-o-calendar-days")
                ->description('Pour quelles dates cette réservation est prévue ?')
                ->schema([
                    DatePicker::make('arrival_date')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state) => $this->arrival_date = $state),
                    DatePicker::make('departure_date')
                        ->disabled(fn (Get $get) => $get('no_departure_date') == true)
                        ->required(fn (Get $get) => $get('no_departure_date') == false)
                        ->afterStateUpdated(fn ($state) => $this->departure_date = $state),
                    Toggle::make('no_departure_date')
                        ->label("Ne connait pas sa date de départ")
                        ->live()
                        ->afterStateUpdated(fn ($state) => $this->no_departure_date = $state),
//                        ->afterStateUpdated(fn ($state) => $this->arrival_date = $state),
                ]),
            Step::make('Visiteurs')
                ->icon("heroicon-o-user-group")
                ->description("Quelles seront les personnes présentes dans ce groupe ?")
                ->schema([
                    Repeater::make('sejours')
                        ->relationship("sejours")
                        ->label("Personnes")
                        ->defaultItems(0)
                        ->schema([
                            Select::make("visitor_id")
                                ->relationship("visitor", "nom")
                                ->createOptionForm([
                                    TextInput::make('nom')
                                        ->required(),
                                    TextInput::make('prenom')
                                        ->required(),
                                    TextInput::make('email')
                                        ->email()
                                        ->required(),
                                    DatePicker::make('date_de_naissance'),
                                    TextInput::make('phone'),
                                ])
                                ->required(),
                            Section::make('dates')
                                ->description("Vous pouvez changer les dates individuellement")
                                ->collapsed()
                                ->schema([
                                    DatePicker::make('arrival_date')
                                        ->required()
                                        ->default($this->arrival_date),
                                    DatePicker::make('departure_date')
                                        ->disabled(fn (Get $get) => $get('no_departure_date') == true)
                                        ->required(fn (Get $get) => $get('no_departure_date') == false)
                                        ->default($this->departure_date),
                                    Toggle::make('no_departure_date')
                                        ->label("Ne connait pas sa date de départ")
                                        ->live()
                                        ->default($this->no_departure_date),
                                ]),
                            Select::make('price')
                                ->label("Profil de prix")
                                ->required()
                                ->prefixIcon('heroicon-o-currency-euro')
                                ->options(Profile::all()->mapWithKeys(function(Profile $profile){
                                    return [$profile->price => $profile->name." ".$profile->euro ];
                                }))
                            ,
                            Select::make('room_id')
                                ->label("Chambre")
                                ->relationship('room', 'name')

                        ])
                ]),
            Step::make('Remarques')
                ->icon("heroicon-o-chat-bubble-bottom-center-text")
                ->description("Souhaite-vous ajouter une remarque à cette réservation ?")
                ->schema([
                    Textarea::make('remarques_accueil')
                        ->label("Remarques"),
                ])
        ];
    }
}
