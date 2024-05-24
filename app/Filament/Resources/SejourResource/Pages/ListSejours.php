<?php

namespace App\Filament\Resources\SejourResource\Pages;

use App\Filament\Resources\SejourResource;
use App\Models\Reservation;
use App\Models\Sejour;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\On;
use Livewire\Livewire;

class ListSejours extends ListRecords
{
    protected static string $resource = SejourResource::class;
    protected static string $view = 'filament.resources.sejours.pages.list-sejours';

    public string $lien_reservation = "";

    public $update;

    public function getTabs(): array
    {
        return [
            'tous' => Tab::make(),
            "Aujourd hui" => Tab::make()
                ->badge(fn() => Sejour::where('confirmed', true)->presents()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->presents()),
            'futures arrivées' => Tab::make()
                ->badge(fn() => Sejour::where('confirmed', true)->futurArrivals()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->futurArrivals()),

        ];
    }


    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('reservation')
                    ->state(fn(Sejour $record) => $record->reservation->getColor())
                ,
                Tables\Columns\TextColumn::make('visitor.nom')
                    ->label("Nom")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visitor.prenom')
                    ->label("Prénom")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('confirmed'),
//                Tables\Columns\ToggleColumn::make('remove_from_stats'),
                Tables\Columns\TextColumn::make('arrival_date')
                    ->label("Date d'arrivée")
                    ->date('D j F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('departure_date')
                    ->date('D j F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.name'),
                Tables\Columns\TextColumn::make('profile.price')
                    ->label("Prix choisi")
                    ->money('eur'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('arrival_date')
            ->groups([
                Tables\Grouping\Group::make('reservation.id')
                    ->label("Réservation")
                    ->getDescriptionFromRecordUsing(function(Sejour $record): ?string {
                        return $record->getRemarques();
                    }),
            ])
//            ->defaultGroup('reservation.id')
            ->filters([
                //
                Tables\Filters\Filter::make('remove_past')
                    ->label("Ne pas afficher les séjours passés")
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->scopes('withoutPast'))
                    ->default(),
                Tables\Filters\Filter::make('confirmed')
                    ->label("Confirmés uniquement")
                    ->query(fn (Builder $query): Builder => $query->confirmed())
                    ->toggle(),

            ])
            ->actions([
                Tables\Actions\Action::make('select_room')
//                    ->visible(fn(Sejour $record) => !$record->room()->exists() )
                    ->color('info')
                    ->iconButton()
                    ->icon('heroicon-o-home')
                    ->action(function (Sejour $record) {
                        $startDate = $record->arrival_date;
                        $endDate = $record->departure_date;
                        $this->dispatch('select-room', [$startDate, $endDate, $record->id]);
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->update = !$this->update;
    }

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

    public function getFooter(): ?View
    {
        return view('rooms-occupation');

    }

}
