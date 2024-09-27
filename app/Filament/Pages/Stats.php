<?php

namespace App\Filament\Pages;

use App\Models\Sejour;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class Stats extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.stats';

    public function table(Table $table): Table
    {
        $today = today();
        $beginOfYear = $today->copy()->firstOfYear();
        $endOfYear = $today->copy()->lastOfYear();
        $sejoursQuery = Sejour::query()->confirmed()->inStats();
        return $table
            ->query($sejoursQuery)
            ->columns([
                TextColumn::make('visitor.nom'),
                TextColumn::make('visitor.prenom'),
                TextColumn::make('price')
                    ->money('eur'),
                TextColumn::make('nuits')
                    ->numeric()
                    ->summarize(Summarizer::make()
                        ->label("Total des nuitées sur année en cours")
                        ->using(function(Table $table): int {
                            return $this->getFilteredTableQuery()->get()->sum('nuits');
                        }),
                    ),
                TextColumn::make('total_price')
                    ->label("Coût du séjour")
                    ->money('eur')
                    ->summarize(Summarizer::make()
                        ->label("Total des revenus")
                        ->using(function() use ($sejoursQuery) : int {
                            return $this->getFilteredTableQuery()->get()->sum('total_price');
                        })
                        ->money('eur')
                    )


            ])
            ->filters([
                Filter::make('dates')
                    ->form([
                        DatePicker::make('from')
                            ->label("Depuis")
                            ->default($beginOfYear)
                        ,
                        DatePicker::make('until')
                            ->label("Jusque")
                            ->default($endOfYear)
                        ,
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] && $data['until'],
                                fn(Builder $query, $date): Builder => $query->withinDates($data['from'], $data['until']),
                            );
//                            ->when(
//                                $data['until'],
//                                fn(Builder $query, $date): Builder => $query->whereDate('arrival_date', '<=', $date),
//                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['from'] || !$data['until']) {
                            return null;
                        }

                        return 'Entre le ' . Carbon::parse($data['from'])->toFormattedDateString() . ' et le ' . Carbon::parse($data['until'])->toFormattedDateString();
                    })
                ,
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }

}
