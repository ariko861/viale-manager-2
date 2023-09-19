<?php

namespace App\Livewire;

use App\Models\Sejour;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;
use Asantibanez\LivewireCalendar\LivewireCalendar;


class SejourCalendar extends LivewireCalendar
{
    use AuthorizesRequests;

    public function events() : Collection
    {

        $reservations = Sejour::query()
            ->where(function($query) {
                $query->where('quickLink', false)
                    ->whereDate('arrival_date', '>=', $this->gridStartsAt)
                    ->whereDate('arrival_date', '<=', $this->gridEndsAt);
            })
            ->orWhere(function($query) {
                $query
                    ->whereDate('departure_date', '>=', $this->gridStartsAt)
                    ->whereDate('departure_date', '<=', $this->gridEndsAt);
            })
            ->get();

        $arrivalEvents = $reservations->map(function (Sejour $sejour) {
            return [
                'id' => "a{$sejour->id}",
                'title' => __("Arrivée").' '.$sejour->person_number,
                'description' => $sejour->visitor->full_name,
                'date' => $sejour->arrival_date,
                'classes' => ($sejour->confirmed ? 'border-green-400' : 'border-yellow-400')." bg-red-100",
            ];
        });

        $departureEvents = $reservations->whereNotNull('departure_date')->map(function (Sejour $sejour) {
            return [
                'id' => "d{$sejour->id}",
                'title' => __("Départ").' '.$sejour->person_number,
                'description' => $sejour->visitor->full_name,
                'date' => $sejour->departure_date,
                'classes' => ($sejour->confirmed ? 'border-green-400' : 'border-yellow-400')." bg-yellow-100",
            ];
        });

        return $arrivalEvents->concat($departureEvents);
    }

//    public function render()
//    {
//        return view('livewire.sejour-calendar');
//    }
}
