<?php

namespace App\Livewire;

use App\Models\Sejour;
use Carbon\Carbon;
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


    ### TODO : Include @livewireCalendarScripts to make it work
    public function onEventDropped($eventId, $year, $month, $day)
    {
        // This event will fire when an event is dragged and dropped into another calendar day
        // You will get the event id, year, month and day where it was dragged to
        $this->authorize('reservation-edit');
        $date = new Carbon("{$year}-{$month}-${day}");
        $newdate = new Carbon("{$year}-{$month}-${day}");
        $sejourId = substr($eventId, 1);
        $eventType = $eventId[0];

        $sejour = Sejour::find($sejourId);
        switch($eventType){
            case "a":
                $sejour->arrival_date = $date;
                if ($date >= $sejour->departure_date)
                {
                    $sejour->departure_date = $newdate->addDays(1);
//                     dd($date);
                }
                $sejour->save();
                break;
            case "d":
                if ($date <= $sejour->arrival_date)
                {
                    $this->dispatch('showAlert', [ __("La date de départ ne peut pas précéder la date d'arrivée !"), "bg-red-500" ] );
                    ###TODO : Replace with Filament Notify

                } else {
                    $sejour->departure_date = $date;
                    $sejour->save();
                }
                break;
            default:
                return;
        }
    }

}
