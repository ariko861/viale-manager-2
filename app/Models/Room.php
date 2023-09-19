<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\VisitorReservation;

class Room extends Model
{
    use HasFactory;
    protected $fillable = ["name", "beds", "house_id"];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function reservationVisitors()
    {
        return $this->hasMany(VisitorReservation::class);
    }

    public function usersInRoom($beginDate, $endDate)
    {

        $beginDate = new Carbon($beginDate);
        $endDate = new Carbon($endDate);
        $result = collect([]);

        foreach ($this->reservationVisitors as $resaVisitor)
        {
            $reservation = Reservation::find($resaVisitor->reservation_id);
            if ( $reservation && $reservation->isBetweenDates($beginDate, $endDate) )
            {
//                 dd($reservation);
                $visitor = Visitor::find($resaVisitor->visitor_id);
                $result->push($visitor);
            }
        }
        return $result;
    }

    public function fullName()
    {
        if ($this->house && $this->house->displayHouseNameWithRoom)
        {
            return ($this->house->name)."->".($this->name);
        } else {
            return $this->name;
        }
    }


    public function reservationsForRoom($beginDate, $endDate)
    {
        $beginDate = new Carbon($beginDate);
        $endDate = new Carbon($endDate);
        $result = collect([]);

        foreach ($this->reservationVisitors as $resaVisitor)
        {
            $reservation = Reservation::find($resaVisitor->reservation_id);
            if ( $reservation && $reservation->isBetweenDates($beginDate, $endDate) )
            {
                $result->push($reservation);
            }
        }
        return $result;
    }

    public function visitorsInReservationsForRoom($beginDate, $endDate)
    {
        $beginDate = new Carbon($beginDate);
        $endDate = new Carbon($endDate);
        $result = collect([]);

        foreach ($this->reservationVisitors as $resaVisitor)
        {
            $reservation = Reservation::find($resaVisitor->reservation_id);
            if ( $reservation && $reservation->isBetweenDates($beginDate, $endDate) )
            {
                $result->push($resaVisitor);
            }
        }
        return $result;
    }


}
