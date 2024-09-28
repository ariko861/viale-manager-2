<?php

namespace App\Livewire;

use App\Models\Sejour;
use Faker\Provider\Text;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class ListSejoursBetweenDates extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $beginDate;
    public $endDate;

    public function render()
    {
        return view('livewire.list-sejours-between-dates');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Sejour::query()->withinDates($this->beginDate, $this->endDate))
            ->columns([
                TextColumn::make('visitor.prenom')
                    ->label("Prénom")
                ,
                TextColumn::make('visitor.nom')
                    ->label("Nom")
                ,

            ])
            ;
    }


}
