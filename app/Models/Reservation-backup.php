<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VisitorReservation;
use App\Support\ReservationCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;


class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'arrivaldate', 'departuredate', 'confirmed'
    ];

    protected $attributes = [
        'nodeparturedate' => false,
        'confirmed' => false,
        'removeFromStats' => false,
    ];

    protected $casts = [
        'nodeparturedate' => 'boolean',
        'confirmed' => 'boolean',
        'removeFromStats' => 'boolean',

    ];

    protected $appends = ['arrival', 'departure', 'contact_person'];

    public function getArrivalAttribute()
    {
        $date = new Carbon($this->arrivaldate);
        return $date->translatedFormat('l d F Y');
    }

    public function getDepartureAttribute()
    {
        if ($this->nodeparturedate)
        {
            return __("Pas de date de départ définie");
        }
        else {
            $date = new Carbon($this->departuredate);
            return $date->translatedFormat('l d F Y');
        }
    }

    public function getContactPersonAttribute()
    {
        foreach ($this->visitors as $visitor) {
            if ( $visitor->pivot->contact )
            {
                return $visitor;
            }
        }
    }

    public function getPersonNumberAttribute()
    {
        return $this->visitors->count() > 1 ? $this->visitors->count()." ".__("personnes") : $this->visitors->count()." ".__("personne");
    }

    public function getVisitorListAttribute()
    {
        $visitors = "";
        foreach ($this->visitors as $visitor) {
            if ($visitors == "")
            {
                $visitors = $visitors.$visitor->full_name;
            } else {
                $visitors = $visitors.", ".$visitor->full_name;
            }
        }
        return $visitors;
    }

    public function getVisitorAndRoomsListAttribute()
    {
        $visitors = "";
        foreach ($this->visitors as $visitor) {
            if ($visitor->pivot->room_id) {
                $display = $visitor->full_name.'->'.$visitor->pivot->room->fullName();
            } else {
                $display = $visitor->full_name;
            }
            if ($visitors == "")
            {
                $visitors = $visitors.$display;
            } else {
                $visitors = $visitors.", ".$display;
            }
        }
        return $visitors;
    }

    public function getNightsAttribute()
    {
        $today = Carbon::now();
        $begindate = new Carbon($this->arrivaldate);
        $enddate = new Carbon($this->departuredate);

        if ( $this->nodeparturedate ) {
            return ( $begindate->gt($today) ? 0 : $begindate->diffInDays($enddate) );
        } else {
            return (  $begindate->diffInDays($enddate) );
        }

    }

    public function getPerNightAttribute()
    {
        if ( $this->confirmed )
        {
            $total = 0;

            foreach ($this->visitors as $visitor)
            {
                $total += $visitor->pivot->price;
            }
            return $total;

        } else {
            return 0;
        }
    }

    public function getPerNightEuroAttribute()
    {
        return number_format($this->per_night, 2,'€',' ');
    }

    public function getTotalPriceAttribute()
    {
        return $this->per_night * $this->nights;
    }

    public function getTotalPriceEuroAttribute()
    {
        return number_format($this->per_night * $this->nights, 2,'€',' ');
    }

    public function isBetweenDates($beginDate, $endDate)
    {
        $departureDate = new Carbon($this->departuredate);
        $arrivalDate = new Carbon($this->arrivaldate);
        return ( $arrivalDate <= $endDate ) && ( $departureDate >= $beginDate || $this->nodeparturedate );
    }

    public function getNonContactVisitors()
    {
        $visitors = collect([]);
//         return $this->belongsToMany(Visitor::class)->wherePivot('contact', false);
        foreach ( $this->visitors as $visitor )
        {
            if (! $visitor->pivot->contact)
            {
                $visitors->push($visitor);
            }
        }
        return $visitors;
    }


    public function visitors()
    {
        return $this->belongsToMany(Visitor::class, 'visitor_reservation')->using(VisitorReservation::class)->withPivot('contact', 'room_id', 'id', 'price');
    }

    public function contactVisitor()
    {
        return $this->belongsToMany(Visitor::class, 'visitor_reservation')->using(VisitorReservation::class)->wherePivot('contact', true);
    }

    public function links()
    {
        return $this->hasMany(ReservationLink::class);
    }

    public function newCollection(array $models = [])
    {
        return new ReservationCollection($models);
    }

    public static function createQuickReservation($visitor_id) {
        $date = Carbon::now();
        $reservation = new static();
        $reservation->arrivaldate = $date;
        $reservation->departuredate = $date;
        $reservation->nodeparturedate = false;
        $reservation->confirmed = false;
        $reservation->quickLink = true;
        $reservation->save();
        $reservation->visitors()->attach($visitor_id, ['contact' => true ]);
        return $reservation;
    }

    public static function getPresencesBetweenDates($dateBegin, $dateEnd, $confirmed = true){

        return static::where('quickLink', false)->where(function($query) use ($confirmed){
                    if ($confirmed) $query->where('confirmed', true);
                })->where(function($query) use ($dateBegin, $dateEnd) {
                $query->where(function($query) use ($dateBegin, $dateEnd) {
                    $query->whereDate('arrivaldate', '<=', $dateEnd)
                            ->whereDate('departuredate', '>=', $dateBegin);
                    })
                    ->orWhere(function($query) use ($dateBegin, $dateEnd) {
                        $query->whereDate('arrivaldate', '<=', $dateEnd)
                        ->where('nodeparturedate', true );
                    });
                })->get();
    }

    public static function getPresencesExcludeDates($dateBegin, $dateEnd, $confirmed = true){

        return static::where('quickLink', false)->where(function($query) use ($confirmed){
                    if ($confirmed) $query->where('confirmed', true);
                })->where(function($query) use ($dateBegin, $dateEnd) {
                $query->where(function($query) use ($dateBegin, $dateEnd) {
                    $query->whereDate('arrivaldate', '<', $dateEnd)
                            ->whereDate('departuredate', '>', $dateBegin);
                    })
                    ->orWhere(function($query) use ($dateBegin, $dateEnd) {
                        $query->whereDate('arrivaldate', '<', $dateEnd)
                        ->where('nodeparturedate', true );
                    });
                })->get();
    }

    public function scopeIsPresent(Builder $query): void
    {
        $today = Carbon::today();
        $query->whereDate('arrivaldate', '<=', $today)
            ->where(function($query) use ($today) {
                $query->where("departuredate", ">=", $today)
                    ->orWhereNull("departuredate");
            });
    }

    protected static function booted()
    {
        static::updating(function ($reservation) {

            if ($reservation->confirmed && $reservation->quickLink) {
                $reservation->quickLink = false;
                $reservation->save();
            }
        });

        static::deleting(function ($reservation) {

            if ( $reservation->links && $reservation->links->count() ){
                foreach ($reservation->links as $link)
                {
                    $link->delete();
                }
            }
            VisitorReservation::where('reservation_id', $reservation->id)->delete();

        });
    }


}
