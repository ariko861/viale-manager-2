<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SejourResource;
use App\Models\Sejour;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{

    use HasWidgetShield;
    public string|null|\Illuminate\Database\Eloquent\Model $model = Sejour::class;
    protected static ?int $sort = 10;

    public function config(): array
    {
        return [
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => 'dayGridWeek,dayGridDay,dayGridMonth',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
            'initialView' => 'dayGridWeek'
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        // You can use $fetchInfo to filter events by date.
        // This method should return an array of event-like objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#returning-events
        // You can also return an array of EventData objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#the-eventdata-class
        return Sejour::query()
             ->withinDates($fetchInfo['start'], $fetchInfo['end'])
            ->get()
            ->map(
                fn (Sejour $sejour) => [
                    'id' => $sejour->id,
                    'title' => $sejour->visitor?->full_name,
                    'start' => $sejour->arrival_date->format('Y-m-d'),
                    'end' => $sejour->departure_date?->addDay()->format('Y-m-d') ?? Carbon::parse($fetchInfo['end'])->addYear(),
                    'url' => SejourResource::getUrl(name: 'view', parameters: ['record' => $sejour]),
                    'shouldOpenUrlInNewTab' => true,
                    'borderColor' => $sejour->confirmed ? 'green' : 'red',
                    'backgroundColor' => $sejour->reservation->getColor(),
                    'textColor' => 'black',
                ]
            )->toArray()

            ;
    }

    public function getFormSchema(): array
    {
        return [

            Grid::make()
                ->schema([
                    DateTimePicker::make('arrival_date'),

                    DateTimePicker::make('departure_date'),
                ]),
        ];
    }

    protected function modalActions(): array
    {
        return [
            EditAction::make()
                ->mountUsing(
                    function (Sejour $record, Form $form, array $arguments) {
                        $form->fill([
                            'name' => $record->name,
                            'starts_at' => $arguments['event']['start'] ?? $record->starts_at,
                            'ends_at' => $arguments['event']['end'] ?? $record->ends_at
                        ]);
                    }
                ),
            DeleteAction::make(),
        ];
    }

    protected function headerActions(): array
    {
        return [

        ];
    }
}
