<?php

namespace App\Livewire;

use App\Enums\MessageTypes;
use App\Models\Message;
use App\Models\Profile;
use App\Models\Reservation;
use App\Models\Visitor;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class VisitorForm extends Component implements HasForms
{

    use InteractsWithForms;

    public bool $valid_token = false;
    public Reservation $reservation;

    public $departure_date;
    public $arrival_date;
    public bool $no_departure_date = false;

    public ?array $data = [];

    public function mount($link_token){
        $reservation = Reservation::firstWhere('link_token', $link_token);

        if ($reservation) {
            $this->valid_token = true;
            $this->reservation = $reservation;
        }
    }

    public function form(Form $form): Form
    {
        $messages = Message::where('type', MessageTypes::Link)->get();
        $messagesDisplay = $messages->map(function(Message $item, $index){
            return Placeholder::make($item->title)->content($item->message);
        });
        return $form
            ->schema([
                Section::make('Confirmation de votre réservation')
                    ->description('prout prout')
                    ->schema([

                        Wizard::make([
                            Step::make('Bienvenue')
                                ->icon('heroicon-o-home')
                                ->schema($messagesDisplay->toArray()),
                            Step::make('Dates')
                                ->icon("heroicon-o-calendar-days")
                                ->description('Pour quelles dates cette réservation est prévue ?')
                                ->schema([
                                    DatePicker::make('arrival_date')
                                        ->required()
                                        ->live()
                                        ->minDate(today())
                                        ->afterStateUpdated(fn ($state) => $this->arrival_date = $state),
                                    DatePicker::make('departure_date')
                                        ->required()
                                        ->minDate($this->arrival_date)
                                        ->afterStateUpdated(fn ($state) => $this->departure_date = $state),
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
                                                ->getOptionLabelFromRecordUsing(fn(Visitor $record): string => "{$record->nom} {$record->prenom}")
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
                                                        ->required()
                                                        ->default($this->departure_date),
                                                ]),
                                            Select::make('profile_id')
                                                ->label("Profil de prix")
                                                ->relationship('profile', 'name')
                                                ->getOptionLabelFromRecordUsing(fn (Profile $record) => "{$record->name} {$record->euro}"),

                                        ])
                                        ->minItems(1)
                                ]),
                            Step::make('Remarques')
                                ->icon("heroicon-o-chat-bubble-bottom-center-text")
                                ->description("Souhaite-vous ajouter une remarque à cette réservation ?")
                                ->schema([
                                    Textarea::make('remarques_visiteur')
                                        ->label("Remarques"),
                                ])
                        ])->submitAction(new HtmlString(Blade::render(<<<BLADE
    <x-filament::button
        type="submit"
        size="sm"
    >
        Submit
    </x-filament::button>
BLADE)))
                    ]),
            ])
            ->model($this->reservation)
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.visitor-form');
    }
}
