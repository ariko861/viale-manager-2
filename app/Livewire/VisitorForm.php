<?php

namespace App\Livewire;

use App\Enums\MessageTypes;
use App\Models\Message;
use App\Models\Profile;
use App\Models\Reservation;
use App\Models\Visitor;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
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
use Filament\Forms\Set;
use Filament\Support\Enums\ActionSize;
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
    public $existing_visitors;
    public int $selected_visitor_id = 0;

    public ?array $data = [];

    public function mount($link_token){
        $reservation = Reservation::firstWhere('link_token', $link_token);

        if (!$reservation) return;
        if ($reservation?->authorize_edition) {
            $this->valid_token = true;
            $this->reservation = $reservation;
        } else {
            $this->redirectRoute('confirmed', $link_token);
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
                    ->id('confirmation')
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
                                ])
                                ->columns(2),
                            Step::make('Visiteurs')
                                ->icon("heroicon-o-user-group")
                                ->description("Quelles seront les personnes présentes dans ce groupe ?")
                                ->schema([
                                    Repeater::make('sejours')
                                        ->relationship("sejours")
                                        ->label("Personnes")
                                        ->required()
                                        ->defaultItems(1)
                                        ->minItems(1)
                                        ->maxItems(1)
                                        ->reorderable()

                                        ->addAction( function(\Filament\Forms\Components\Actions\Action $action) {
                                            return $action
                                                ->label('Ajouter une personne')
                                                ->icon('heroicon-o-user');
                                        })
                                        ->schema([
                                            Section::make("Visiteur")
                                                ->icon('heroicon-m-user-plus')
                                                ->compact()
                                                ->schema([
                                                    Select::make("visitor_id")
                                                        ->relationship("visitor", "nom")
                                                        ->placeholder('Saisissez votre email ici')
                                                        ->searchable()
                                                        ->prefixIcon('heroicon-o-user')
                                                        ->helperText("Essayez de rechercher par adresse email si vous êtes déja venus, appuyer sur le bouton '+' à l'extrémité droite du champ sinon")
                                                        ->getSearchResultsUsing(fn (string $search) => Visitor::where('email',$search)->limit(50)->get()->mapWithKeys(function(Visitor $visitor){
                                                            return [ $visitor->id => "{$visitor->nom} {$visitor->prenom}" ];
                                                        }))
                                                        ->createOptionAction(
                                                            fn (\Filament\Forms\Components\Actions\Action $action) => $action->modalWidth('3xl')->color('info')->size(ActionSize::ExtraLarge),
                                                        )
                                                        ->editOptionAction(
                                                            fn (\Filament\Forms\Components\Actions\Action $action) => $action->modalWidth('3xl')->color('warning'),
                                                        )
                                                        ->editOptionForm([
                                                            DatePicker::make('date_de_naissance')
                                                                ->required()
                                                                ->helperText("Même approximative, c'est uniquement votre âge qui nous intéresse"),
                                                            TextInput::make('phone'),
                                                        ])
                                                        ->createOptionForm([
                                                            TextInput::make('nom')
                                                                ->required(),
                                                            TextInput::make('prenom')
                                                                ->required(),
                                                            TextInput::make('email')
                                                                ->email(),
                                                            DatePicker::make('date_de_naissance')
                                                                ->required()
                                                                ->helperText("Même approximative, c'est uniquement votre âge qui nous intéresse"),
                                                            TextInput::make('phone'),
                                                        ])
                                                        ->required(),
                                                ]),
                                            Section::make('dates')
                                                ->compact()
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
                                                ->required()
                                                ->relationship('profile', 'name')
                                                ->getOptionLabelFromRecordUsing(fn (Profile $record) => "{$record->name} {$record->euro}"),

                                        ])
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
            ->model($this->reservation);
//            ->statePath('data');
    }

    public function create(): void
    {
//        dd($this->form->getState());
//        $this->reservation->confirmed_at = now();
//        $this->reservation->authorize_edition = false;
//        $this->reservation->save();
//        foreach ($this->reservation->sejours as $sejour){
//            $sejour->confirmed = true;
//            $sejour->save();
//            if ($sejour->visitor?->email){
//                $sejour->visitor->confirmed = true;
//                $sejour->visitor->save();
//            }
//        }
        $this->redirectRoute('confirmed', $this->reservation->link_token);
    }

    public function selectVisitor()
    {
        $visitor = Visitor::find($this->selected_visitor_id);
        $this->form->fill(['sejours' => [
                0 => [
                    'nom' => $visitor->nom,
                    'prenom' => $visitor->prenom,
                    'date_de_naissance' => $visitor->date_de_naissance,
                    'phone' => $visitor->phone,
                ]
            ]
        ]);
        $this->dispatch('close-modal', id: 'select-existing-visitor');
    }

    public function render()
    {
        return view('livewire.visitor-form');
    }
}
