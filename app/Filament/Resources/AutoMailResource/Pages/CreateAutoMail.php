<?php

namespace App\Filament\Resources\AutoMailResource\Pages;

use App\Filament\Resources\AutoMailResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAutoMail extends CreateRecord
{
    protected static string $resource = AutoMailResource::class;

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('index');
    }

}
