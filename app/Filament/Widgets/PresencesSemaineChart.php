<?php

namespace App\Filament\Widgets;

use App\Models\Sejour;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\Reactive;

class PresencesSemaineChart extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Présences, arrivées et départs';

    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = null;

    public bool $columnSpanFull = false;
    public function getColumnSpan(): int|array|string
    {
        if ($this->columnSpanFull){
            return 'full';
        }
        return $this->columnSpan;
    }

    #[Reactive]
    public ?string $begin = null;

    #[Reactive]
    public ?string $end = null;

    protected static ?array $options = [
        'scales' => [
            'x' => [
                'stacked' => true,
            ],
            'y' => [
                'stacked' => true,
            ],
        ],
        'indexAxis' => 'x',

    ];

    protected function getData(): array
    {

        $begin = $this->begin ? Carbon::parse($this->begin) : today();
        $end = $this->end ? Carbon::parse($this->end) : today()->addDays(6);

        $jours_semaine = [];
        $startDate = $begin;
        while ($startDate->lte($end)) {
            $jours_semaine[] = $startDate->copy();
            $startDate->addDay();
        }

        $countStaying = [];
        $countLeaving = [];
        $countArriving = [];
        foreach ($jours_semaine as $jour){
            $countStaying[] = Sejour::query()->withinDates($jour, $jour, excludeArrivals: true, excludeDepartures: true)->count();
            $countLeaving[] = Sejour::query()->whereDate('departure_date', $jour)->count();
            $countArriving[] = Sejour::query()->whereDate('arrival_date', $jour)->count();
        }

        $stackedData = [
            [
                'label' => 'Restent',
                'data' => $countStaying,
                'backgroundColor' => 'blue',
            ],
            [
                'label' => 'Arrivent',
                'data' => $countArriving,
                'backgroundColor' => 'green',
            ],
            [
                'label' => 'Partent',
                'data' => $countLeaving,
                'backgroundColor' => 'red',
            ],
        ];

        return [
            'datasets' => $stackedData,
            'labels' => array_map(fn(Carbon $day) => $day->dayName , $jours_semaine),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
