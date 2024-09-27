<?php

namespace App\Filament\Resources\VisitorResource\Pages;

use App\Filament\Resources\VisitorResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListVisitors extends ListRecords
{
    protected static string $resource = VisitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('get_emails')
                ->label("Obtenir les emails")
                ->requiresConfirmation()
                ->modalWidth(MaxWidth::Full)
                ->modalHeading("Emails des visiteurs sélectionnés")
                ->modalDescription(fn() => $this->getFilteredTableQuery()->pluck('email')->implode(', '))

        ];
    }
}
