<?php

namespace App\Livewire;

use App\Models\Room;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Livewire\Component;

class RoomsOccupation extends Component implements HasTable, HasForms
{

    use InteractsWithForms;
    use InteractsWithTable;
    public Carbon $startDate;
    public Carbon $endDate;

    public function table(Table $table): Table
    {
        return $table
            ->query(Room::query())
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('beds')
                    ->numeric(),
                TextColumn::make('occupants')
                    ->state(function (Room $record){
                        $occupants = $record->sejours($this->startDate, $this->endDate)->get();
                        return $occupants->map(function($sejour){
                            return $sejour->visitor->full_name;
                        });
                    })
                    ->listWithLineBreaks()

            ])
            ->groups([
                Group::make('house.name')
                    ->label("Maison"),
            ])
            ->paginated(false)
            ->defaultGroup('house.name')
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

    public function mount(){
        if (!$this->startDate) $this->startDate = today();
        if (!$this->endDate) $this->endDate = today();

    }

    public function render()
    {
        return view('livewire.rooms-occupation');
    }
}
