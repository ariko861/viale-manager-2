<?php

namespace App\Filament\Resources\OptionResource\Pages;

use App\Filament\Resources\OptionResource;
use App\Models\Option;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOptions extends ListRecords
{
    protected static string $resource = OptionResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];
        $email = Option::firstWhere('name', 'email');
        if (filter_var( $email?->value, FILTER_VALIDATE_EMAIL ))
        {
            $actions[] = Actions\Action::make('Test email')
                ->color('success');
        }
        return $actions;
    }
}
