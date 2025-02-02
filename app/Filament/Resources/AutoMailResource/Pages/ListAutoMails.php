<?php

namespace App\Filament\Resources\AutoMailResource\Pages;

use App\Filament\Resources\AutoMailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAutoMails extends ListRecords
{
    protected static string $resource = AutoMailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
