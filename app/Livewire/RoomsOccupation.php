<?php

namespace App\Livewire;

use App\Models\Room;
use App\Models\Sejour;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class RoomsOccupation extends Component implements HasTable, HasForms
{

    use InteractsWithForms;
    use InteractsWithTable;
    public Carbon $startDate;
    public Carbon $endDate;
    public int $sejourId;

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
                Action::make('choose_room')
                    ->label("Choisir cette chambre")
                    ->action(function(Room $record) {
                        $sejour = Sejour::find($this->sejourId);
                        $sejour->room_id = $record->id;
                        $sejour->save();
                        $this->dispatch('refresh');
                        $this->dispatch('close-modal', id: 'select-sejour-room');
                    } ),
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function mount(){
        $this->startDate = today();
        $this->endDate = today();

    }

    #[On('select-room')]
    public function updateRoomOccupation(array $dates)
    {
        $this->startDate = Carbon::parse($dates[0]) ?? today();
        $this->endDate = Carbon::parse($dates[1]) ?? today();
        $this->sejourId = $dates[2] ?? 0;
        $this->dispatch('open-modal', id: 'select-sejour-room');
    }

    public function render()
    {
        return view('livewire.rooms-occupation');
    }
}
