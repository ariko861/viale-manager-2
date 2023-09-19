<?php

namespace App\Filament\Resources\SejourResource\Pages;

use App\Filament\Resources\SejourResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSejours extends ListRecords
{
    protected static string $resource = SejourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
