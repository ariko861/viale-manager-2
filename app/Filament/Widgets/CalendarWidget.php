<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SejourResource;
use App\Models\Sejour;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{

    public function fetchEvents(array $fetchInfo): array
    {
        // You can use $fetchInfo to filter events by date.
        // This method should return an array of event-like objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#returning-events
        // You can also return an array of EventData objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#the-eventdata-class
        return Sejour::query()
            ->where(function (Builder $query) use ($fetchInfo) {
                // On récupère les dates de fin et début de séjour entre dates de début et date de fin du calendrier
                $query->orWhere(function (Builder $query) use ($fetchInfo) {
                        $query->where('arrival_date', '>=', $fetchInfo['start'])
                            ->where('arrival_date', '<=', $fetchInfo['end']);
                })->orWhere(function (Builder $query) use ($fetchInfo) {
                    $query->where('departure_date', '>=', $fetchInfo['start'])
                        ->where('departure_date', '<=', $fetchInfo['end']);
                });
            })
            ->get()
            ->map(
                fn (Sejour $sejour) => [
                    'title' => $sejour->visitor?->full_name,
                    'start' => $sejour->arrival_date,
                    'end' => $sejour->departure_date,
                    'url' => SejourResource::getUrl(name: 'view', parameters: ['record' => $sejour]),
                    'shouldOpenUrlInNewTab' => true,
                    'borderColor' => $sejour->confirmed ? 'green' : 'red',
                    'backgroundColor' => $sejour->reservation->getColor(),
                    'textColor' => 'black',
                ]
            )->toArray()

            ;
    }
}
