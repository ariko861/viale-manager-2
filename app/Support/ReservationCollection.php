<?php

namespace App\Support;

use App\Support\ReservationCollection;
use Illuminate\Database\Eloquent\Collection;

class ReservationCollection extends Collection
{
    public function fetchReservationsByVisitorName($searchQuery)
    {
        return $this->whereHas('visitors', function (Builder $query) {
                $query->where('name', 'like', '%'.$searchQuery.'%')
                    ->orWhere('surname', 'like', '%'.$searchQuery.'%');
            });
    }

    public function getTotalAmountOfVisitors()
    {
        $visitor_count = 0;
        foreach ($this as $reservation)
        {
            $visitor_count += $reservation->visitors->count();
        }
        return $visitor_count;
    }

}

