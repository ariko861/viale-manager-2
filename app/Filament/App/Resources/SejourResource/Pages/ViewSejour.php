<?php

namespace App\Filament\App\Resources\SejourResource\Pages;

use App\Filament\App\Resources\SejourResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\View\View;

class ViewSejour extends ViewRecord
{
    protected static string $resource = SejourResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function getFooter(): ?View
    {
        return view('filament.app.resources.sejours.footer-view',
            data: ['beginDate' => $this->record->arrival_date, 'endDate' => $this->record->departure_date]);
    }
}
