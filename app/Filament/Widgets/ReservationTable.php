<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Nette\Utils\Html;

class ReservationTable extends BaseWidget
{
    use HasWidgetShield;
//    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()->orderByDesc('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->color('info')
                    ->description("Cliquez pour copier le lien")
                    ->wrap()
                    ->copyable()
                    ->copyableState(fn(Reservation $record): string => $record->getLink())
                ,

                Tables\Columns\TextColumn::make('remarques_accueil')
                    ->wrapHeader()
                    ->wrap()
                    ->html()
                ,
                Tables\Columns\ToggleColumn::make('link_sent')
                    ->wrapHeader()
                    ->label("Lien envoyé")
                ,
                Tables\Columns\IconColumn::make('is_confirmed')
                    ->wrapHeader()
                    ->label("Réservation confirmée")
                    ->boolean()
                ,

            ])
            ->defaultPaginationPageOption(5)
            ->headerActions([
                Tables\Actions\Action::make('create_link_form')
                    ->label("Création lien formulaire")
                    ->color("success")
                    ->icon('heroicon-o-pencil')
                    ->form($this->getLienReservationForm())
                    ->action(function(array $data): void {
                        $reservation = Reservation::createQuickReservation(
                            max_days_change: $data['max_days_change'],
                            max_visitors: $data["max_visitors"],
                            remarques_accueil: $data['remarques_accueil']
                        );
                        $reservation->contact_email = $data['contact_email'];
                        $reservation->save();
//                        $this->lien_reservation = $reservation->getLink();
                        Notification::make('link-created')
                            ->title("Lien de réservation créé")
                            ->body(new HtmlString("Le lien de réservation <a href='{$reservation->getLink()}'>{$reservation->getLink()}</a> a été créé"))
                            ->success()
                            ->persistent()
                            ->send()
                        ;

                    })
                ,
            ])
            ->actions([
                Tables\Actions\Action::make('send_link')
                    ->iconButton()
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn(Reservation $record) : bool => $record->contact_email != null)
                    ->requiresConfirmation()
                    ->modalHeading("Envoi du lien de réservation")
                    ->modalDescription(fn(Reservation $record): string => "Vous allez envoyer le lien de réservation à {$record->contact_email}, êtes-vous sûr·e ?")
                    ->action(fn(Reservation $record) => $record->sendLink())
                ,
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->form($this->getLienReservationForm())
                ,
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->visible(fn(Reservation $record) => !$record->is_confirmed && !$record->link_sent)
            ])
            ;
    }


    private function getLienReservationForm(): array
    {
        return [
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
            TextInput::make('contact_email')
                ->label("Email de la personne de contact")
                ->email()
                ->prefixIcon('heroicon-o-envelope')
                ->required(fn(Get $get) => $get('groupe'))
            ,
            Toggle::make('all_mails_required')
                ->default(false)
                ->label("Exiger les emails de tous les inscrits")
                ->disabled(fn(Get $get) => $get('groupe'))
            ,
//            Toggle::make('groupe')
//                ->default(false)
//                ->label("Formulaire pour grand groupe")
//                ->helperText("Attention, le but est de fournir un formulaire simplifié, des dates et prix individuels ne pourront pas être définis")
//                ->hint("Formulaire spécial pour grands groupes, le formulaire sera simplifié et seuls les prénoms seront demandés")
//                ->live()
//                ->afterStateUpdated(function(Set $set, Get $get) {
//                    $set('all_mails_required', false);
//                    if ($get('max_visitors') <= 50) $set('max_visitors', 50);
//                })
//            ,
            TextInput::make('nom_groupe')
                ->label("Nom du groupe")
                ->required(fn(Get $get) => $get('groupe'))
                ->visible(fn(Get $get) => $get('groupe'))
            ,
            RichEditor::make('remarques_accueil'),
        ];
    }

}
