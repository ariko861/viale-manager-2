<?php

namespace App\Livewire;

use App\Enums\MessageTypes;
use App\Models\Message;
use App\Models\Profile;
use App\Models\Reservation;
use App\Models\Sejour;
use App\Models\Visitor;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
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
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
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
            if ($reservation->sejours()->count() > 0){
                $this->form->fill([
                    'sejours' => $reservation->sejours->map(function( Sejour $sejour){
                        return [
                            "select_visitor" => $sejour->visitor_id,
                            "nom" => $sejour->visitor?->nom,
                            "prenom" => $sejour->visitor?->prenom,
                            "email" => $sejour->visitor?->email,
                            "date_de_naissance" => $sejour->visitor->date_de_naissance,
                            "phone" => $sejour->visitor->phone,
                            "visitor_id" => $sejour->visitor_id,
                            "sejour_id" => $sejour->id,
                            "arrival_date" => $sejour->arrival_date,
                            "departure_date" => $sejour->departure_date,
                            "profile_id" => $sejour->profile_id,
                        ];
                    })->toArray(),
                    'remarques_visiteur' => $reservation->remarques_visiteur ?? "",
                ]);
            }
        } else {
            $this->redirectRoute('confirmed', $link_token);
        }

    }

    public function form(Form $form): Form
    {
        $messages = Message::where('type', MessageTypes::Link)->get();
        $messagesDisplay = $messages->map(function(Message $item, $index){
            return Placeholder::make($item->title)->content(fn() => new HtmlString($item->message));
        });
        return $form
            ->schema([
                Section::make('Confirmation de votre réservation')
                    ->id('confirmation')
                    ->schema([

                        Wizard::make([
                            Step::make('Bienvenue')
                                ->icon('heroicon-o-home')
                                ->schema($messagesDisplay->toArray()),
                            Step::make('Dates')
                                ->hidden(fn() => $this->reservation->sejours()->count() > 0)
                                ->icon("heroicon-o-calendar-days")
                                ->description('Pour quelles dates cette réservation est prévue ?')
                                ->schema([
                                    DatePicker::make('arrival_date')
                                        ->label("Date d'arrivée")
                                        ->hint("Date prévue de votre arrivée à la Viale")
                                        ->required()
                                        ->live()
                                        ->minDate(today())
                                        ->afterStateUpdated(fn ($state) => $this->arrival_date = $state),
                                    DatePicker::make('departure_date')
                                        ->label("Date de départ")
                                        ->hint("Date prévue de votre départ de la Viale")
                                        ->required()
                                        ->live()
                                        ->minDate(fn() => $this->arrival_date)
                                        ->afterStateUpdated(fn ($state) => $this->departure_date = $state),
                                ])
                                ->columns(2),
                            Step::make('Visiteurs')
                                ->icon("heroicon-o-user-group")
                                ->description("Quelles seront les personnes présentes dans ce groupe ?")
                                ->schema(fn()=> $this->reservation->groupe ? [
                                    Select::make('price')
                                        ->label("Profil de prix")
                                        ->prefixIcon('heroicon-o-currency-euro')
                                        ->required()
                                        ->options(fn() => Profile::all()->mapWithKeys(function($profile) {
                                            return [$profile->price => $profile->name. " ".$profile->euro];
                                        })),
                                    $this->getVisitorsSimpleRepeater()
                                ] : [
                                    $this->getVisitorsRepeater()
                                ])
                                ->afterValidation(function (Get $get, Set $set){
                                    if ($this->reservation->groupe) {
                                        $set('contact_email', $this->reservation->contact_email);

                                    } else {

                                        $first_visitor = array_values($get('sejours'))[0];
                                        $set('contact_email', $first_visitor['email']);
                                        $set('contact_phone', $first_visitor['phone']);
                                    }
                                })
                            ,

                            Step::make('Remarques')
                                ->icon("heroicon-o-chat-bubble-bottom-center-text")
                                ->description("Souhaite-vous ajouter une remarque à cette réservation ?")
                                ->schema([
                                    TextInput::make('contact_email')
                                        ->label("Email de la personne de contact")
                                        ->email()
                                        ->required()
                                    ,
                                    TextInput::make('contact_phone')
                                        ->label("Téléphone de la personne de contact")
                                        ->required()
                                    ,
                                    Textarea::make('remarques_visiteur')
                                        ->label("Remarques"),
                                ])
                        ])->submitAction(new HtmlString(Blade::render(<<<BLADE
    <x-filament::button
        type="submit"
        size="sm"
    >
        Confirmer
    </x-filament::button>
BLADE)))
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
//        dd($data);

        if ($this->reservation->groupe){
            $this->createGroupeSejours($data);
        } else {
            $this->createSejours($data);
        }

//        $this->reservation->sejours()->delete();

        $this->reservation->update([
            'remarques_visiteur' => $data['remarques_visiteur'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'],
        ]);
        $this->reservation->confirm();
        $this->redirectRoute('confirmed', $this->reservation->link_token);
    }

    private function createGroupeSejours(array $data): void
    {
        foreach ($data["sejours"] as $prenom) {
            $visitor = Visitor::query()->create([
                'nom' => $this->reservation->nom_groupe,
                'prenom' => $prenom
            ]);
            Sejour::create([
                'arrival_date' => $data["arrival_date"],
                'departure_date' => $data["departure_date"],
                'price' => $data["price"],
                'visitor_id' => $visitor->id,
                'reservation_id' => $this->reservation->id,
                'confirmed' => true,
            ]);
        }
    }

    private function createSejours(array $data): void
    {
        foreach ($data["sejours"] as $sejourData){
            // On commence par créer ou récupérer le visiteur
            if ($sejourData["visitor_id"]){
                $visitor = Visitor::find($sejourData["visitor_id"]);
                if ($sejourData["phone"]) $visitor->phone = $sejourData["phone"];
                if ($sejourData["date_de_naissance"]) $visitor->date_de_naissance = $sejourData["date_de_naissance"];
                $visitor->save();
            } else {
                $visitor = Visitor::create([
                    'nom' => $sejourData["nom"],
                    'prenom' => $sejourData["prenom"],
                    'date_de_naissance' => $sejourData["date_de_naissance"],
                    'phone' => $sejourData["phone"],
                    'email' => $sejourData["email"]
                ]);
            }
            $price = $sejourData["price"];
//            if (!$price) $price = Profile::where('is_default', true)->first()->price;

            // Pour ensuite l'assigner au séjour nouvellement créé:
            if ($sejourData["sejour_id"]){
                $sejour = Sejour::find($sejourData["sejour_id"])->update([
                    'arrival_date' => $sejourData["arrival_date"],
                    'departure_date' => $sejourData["departure_date"],
                    'price' => $price,
//                    'visitor_id' => $visitor->id,
//                    'reservation_id' => $this->reservation->id,
                    'confirmed' => true,
                ]);

            } else {
                $sejour = Sejour::create([
                    'arrival_date' => $sejourData["arrival_date"],
                    'departure_date' => $sejourData["departure_date"],
                    'price' => $price,
                    'visitor_id' => $visitor->id,
                    'reservation_id' => $this->reservation->id,
                    'confirmed' => true,
                ]);
            }

        }
    }


    private function getVisitorsSimpleRepeater(): Repeater
    {
        return Repeater::make('sejours')
            ->label("Personnes")
            ->hint("Saisissez les prénoms des personnes du groupe")
            ->required()
            ->helperText("Saisissez les prénoms uniquement")
            ->defaultItems(1)
            ->minItems(1)
            ->reorderable()
            ->cloneable()
            ->simple(
                TextInput::make('prenom')
                    ->label("Prénom")
                    ->required()
            )
            ;
    }

    private function getVisitorsRepeater(): Repeater
    {
        return Repeater::make('sejours')
            ->itemLabel(fn (array $state): ?string => $state['nom']." ".$state['prenom'] ?? null)
            ->label("Personnes")
            ->required()
            ->defaultItems(1)
            ->minItems(1)
            ->reorderable()
            ->addAction( function(\Filament\Forms\Components\Actions\Action $action) {
                return $action
                    ->label('Ajouter une personne')
                    ->icon('heroicon-o-user');
            })
            ->schema([
                Fieldset::make("Visiteur")
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->columnSpanFull()
                            ->prefixIcon('heroicon-o-envelope')
                            ->hint("Commencez par saisir une adresse email")
                            ->helperText("Optionnel, permet de retrouver vos informations")
                            ->disabled(fn(Get $get) => $get('visitor_id') )
                            ->live(debounce: 750)
                            ->required($this->reservation?->all_mails_required ?? false)
                            ->afterStateUpdated(function(string $state){
                                $this->existing_visitors = Visitor::where('email', $state)->get();

                            })
                            ->email(),
                        Select::make('select_visitor')
                            ->columnSpanFull()
                            ->label("Personnes existantes avec cet email")
                            ->helperText("Nous avons trouvé cet email dans notre base de données, veuillez vérifier que vous êtes dans cette liste")
                            ->visible(fn() => $this->existing_visitors?->count() > 0)
                            ->prefixIcon('heroicon-o-user')
                            ->options(fn() => $this->existing_visitors?->mapWithKeys(function ($visitor){
                                return [$visitor->id => $visitor->full_name];
                            }) )
                            ->live()
                            ->afterStateUpdated(function($state, Set $set){
                                $visitor = Visitor::find($state);
                                if ($visitor){
                                    $set('nom', $visitor->nom );
                                    $set('prenom', $visitor->prenom );
                                    $set('email', $visitor->email );
                                    $set('date_de_naissance', $visitor->date_de_naissance );
                                    $set('phone', $visitor->phone );
                                    $set('visitor_id', $visitor->id);
                                } else {
                                    $set('visitor_id', '');
                                }
                            }),
                        TextInput::make('nom')
                            ->prefixIcon("heroicon-o-identification")
                            ->disabled(fn(Get $get) => $get('visitor_id') )
                            ->live()
                            ->required(),
                        TextInput::make('prenom')
                            ->prefixIcon("heroicon-s-identification")
                            ->disabled(fn(Get $get) => $get('visitor_id') )
                            ->live()
                            ->required(),
                        DatePicker::make('date_de_naissance')
                            ->prefixIcon("heroicon-o-calendar-days")
                            ->required()
                            ->helperText("Nous sommes surtout intéressés par votre âge")
                        ,
                        TextInput::make('phone')
                            ->prefixIcon('heroicon-o-phone')
                            ->helperText('Optionnel')
                        ,

                        Hidden::make('visitor_id'),
                        Hidden::make('sejour_id'),
                    ]),
                Section::make('dates')
                    ->compact()
                    ->description("Vous pouvez changer les dates individuellement")
                    ->icon('heroicon-o-calendar')
                    ->compact()
                    ->aside()
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        DatePicker::make('arrival_date')
                            ->required()
                            ->label("Arrivée")
                            ->hint("Date prévue de votre arrivée à la Viale")
                            ->live()
                            ->minDate(today())
                            ->default($this->arrival_date),
                        DatePicker::make('departure_date')
                            ->required()
                            ->label("Départ")
                            ->hint("Date prévue de votre départ de la Viale")
                            ->minDate(fn(Get $get) => $get('arrival_date'))
                            ->default($this->departure_date),
                    ]),
                Select::make('price')
                    ->label("Profil de prix")
                    ->prefixIcon('heroicon-o-currency-euro')
                    ->required()
                    ->options(fn() => Profile::all()->mapWithKeys(function($profile) {
                        return [$profile->price => $profile->name. " ".$profile->euro];
                    })),
            ]);

    }


    public function render()
    {
        return view('livewire.visitor-form');
    }
}
