<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PresencesSemaineChart;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Pages\Page;

class Visualisations extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static string $view = 'filament.pages.visualisations';

    public ?array $data = [];

    public Carbon $begin;
    public Carbon $end;

    public function mount(): void
    {
        $this->begin = today();
        $this->end = today()->addDays(6);
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('begin')
                ->label("Début de période")
                ->default($this->begin)
                ->live()
                ->afterStateUpdated(fn($state) => $this->begin = Carbon::parse($state))
            ,
            DatePicker::make('end')
                ->label("Fin de période")
                ->default($this->end)
                ->live()
                ->minDate(fn() => $this->begin)
                ->afterStateUpdated(fn($state) => $this->end = Carbon::parse($state))
            ,
        ])->statePath('data');
    }

    protected function getFooterWidgets(): array
    {
        return [
            PresencesSemaineChart::make([
                'begin' => $this->begin,
                'end' => $this->end,
                'columnSpanFull' => true,
            ]),
        ];
    }
}
