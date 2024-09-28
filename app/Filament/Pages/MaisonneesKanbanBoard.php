<?php

namespace App\Filament\Pages;

use App\Models\AssignationMaisonnee;
use App\Models\House;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Illuminate\Support\Collection;

class MaisonneesKanbanBoard extends KanbanBoard
{
    protected static string $model = AssignationMaisonnee::class;
//    protected static string $statusEnum = ModelStatus::class;

    protected function statuses(): Collection
    {
        return House::prepareForKanban();
    }

    protected function records(): Collection
    {
        return AssignationMaisonnee::all();
    }

}
