<?php

namespace App\Filament\App\Resources\SejourResource\Pages;

use App\Filament\App\Resources\SejourResource;
use App\Models\Sejour;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListSejours extends ListRecords
{
    protected static string $resource = SejourResource::class;

    public function getTabs(): array
    {
        $visitor_id = Auth::user()->visitor_id;
        return [
            "Séjours à venir" => Tab::make()
                ->badge(fn() => Sejour::query()->where('visitor_id', $visitor_id)->whereDate('departure_date', '>=', today())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('departure_date', '>=', today()))
            ,
            "Séjours passés" => Tab::make()
                ->badge(fn() => Sejour::query()->where('visitor_id', $visitor_id)->whereDate('departure_date', '<', today())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('departure_date', '<', today()))
            ,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
