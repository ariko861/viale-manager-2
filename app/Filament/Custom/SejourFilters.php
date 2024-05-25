<?php

namespace App\Filament\Custom;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class SejourFilters
{
    public static function dateFilter(string $column): Filter
    {
        return Filter::make($column)
            ->form([
                DatePicker::make('start')
                    ->label("Début de période")
                ,
                DatePicker::make('end')
                    ->label("Fin de période")
                ,
            ])->columns(2)
            ->query(function (Builder $query, array $data)use ($column): Builder  {
                return $query
                    ->when(
                        $data['start'],
                        fn (Builder $query, $date): Builder => $query->whereDate($column, '>=', $date),
                    )
                    ->when(
                        $data['end'],
                        fn (Builder $query, $date): Builder => $query->whereDate($column, '<=', $date),
                    );
            });
    }

    public static function presenceFilter(string $filter_name): Filter
    {
        return Filter::make($filter_name)
            ->form([
                DatePicker::make('start')
                    ->label("Début de période")
                ,
                DatePicker::make('end')
                    ->label("Fin de période")
                ,
            ])->columns(2)
            ->query(function (Builder $query, array $data): Builder  {
                return $query
                    ->when(
                        $data['start'],
                        fn (Builder $query, $date): Builder => $query->whereDate('departure_date', '>=', $date),
                    )
                    ->when(
                        $data['end'],
                        fn (Builder $query, $date): Builder => $query->whereDate('arrival_date', '<=', $date),
                    );
            });
    }


}
