<?php

namespace App\Filament\Resources\SejourResource\Pages;

use App\Filament\Resources\SejourResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSejour extends EditRecord
{
    protected static string $resource = SejourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

//    protected function mutateFormDataBeforeFill(array $data): array
//    {
//        $data['reservation']['link'] = auth()->id();
//
//        return $data;
//    }
}
