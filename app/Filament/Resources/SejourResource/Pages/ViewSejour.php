<?php

namespace App\Filament\Resources\SejourResource\Pages;

use App\Filament\Resources\SejourResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSejour extends ViewRecord
{
    protected static string $resource = SejourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
