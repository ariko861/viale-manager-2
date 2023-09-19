<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

class VisitorReservation extends Pivot
{

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

     public function reservation()
     {
         return $this->belongsTo(Reservation::class);
     }

     public function visitor()
     {
         return $this->belongsTo(Visitor::class);
     }
     public function community()
     {
         return $this->belongsTo(House::class, 'house_id');
     }

     public static function getVisitorsInResasBetweenDates($begin, $end)
     {
        return static::whereNull('room_id')->whereRelation('reservation', function (Builder $query) use ($begin, $end) {
                $query->where('quickLink', false)->where(function($query) use ($begin, $end) {
                    $query->whereDate('arrivaldate', '<=', $end)->where(function($query) use ($begin) {
                        $query->whereDate('departuredate', '>=', $begin)
                            ->orWhere('nodeparturedate', true );
                    });
                });
        })->get()->sortBy('reservation.arrivaldate');
     }

    protected $table = 'visitor_reservation';

    public $incrementing = true;


}
