<?php

namespace App\Filament\Widgets;

use App\Models\Sejour;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class PresencesSemaineChart extends ChartWidget
{
    protected static ?string $heading = 'Présences sur la semaine';

    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = null;


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

        $stackedData = [];
        $today = today();

        $jours_semaine = [];
        for ($i=0; $i < 7; $i++) {
            # Pour chaque jour de la semaine à venir
            $day = $today->copy()->addDays($i);
            $jours_semaine[] = $day;

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
