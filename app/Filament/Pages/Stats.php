<?php

namespace App\Filament\Pages;

use App\Models\Sejour;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;

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
        $sejoursQuery = Sejour::query()->withinDates($beginOfYear, $endOfYear)->confirmed()->inStats();
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
                        ->label("Total des nuitées")
                        ->using(function() use ($sejoursQuery) : int {
                            return $sejoursQuery->get()->sum('nuits');
                        }),
                    ),
                TextColumn::make('total_price')
                    ->label("Coût du séjour")
                    ->money('eur')
                    ->summarize(Summarizer::make()
                        ->label("Total des revenus")
                        ->using(function() use ($sejoursQuery) : int {
                            return $sejoursQuery->get()->sum('total_price');
                        })
                        ->money('eur')
                    )


            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }

}
