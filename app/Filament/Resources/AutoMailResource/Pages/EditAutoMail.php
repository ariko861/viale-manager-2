<?php

namespace App\Filament\Resources\AutoMailResource\Pages;

use App\Filament\Resources\AutoMailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAutoMail extends EditRecord
{
    protected static string $resource = AutoMailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
