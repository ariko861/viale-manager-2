<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\VisitorCollection;
use Illuminate\Database\Eloquent\Builder;

class Visitor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name', 'surname', 'email', 'phone', 'birthyear', 'confirmed'
    ];

    protected $attributes = [
        'confirmed' => true,
    ];

    public function getFullNameAttribute()
    {
        $surname = $this->surname === "x-inconnu" ? 'Prénom inconnu' : $this->surname;
        $name = $this->name === "x-inconnu" ? 'Nom inconnu' : $this->name;

        return "{$surname} {$name}";
    }

    public function normalize()
    {
        $this->name = ucfirst(strtolower($this->name));
        $this->surname = ucfirst(strtolower($this->surname));
    }

    public function getAgeAttribute()
    {
        if ($this->birthyear)
        {
            $current_year = Carbon::now()->year;
            return $current_year - $this->birthyear;
        } else {
            return "Âge inconnu";
        }
    }

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'visitor_reservation')->using(VisitorReservation::class)->withPivot('contact', 'room_id', 'id', 'price');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'visitor_tag');
    }

    public static function createQuickVisitor($email) {
        $visitor = new static();
        $visitor->email = $email;
        $visitor->name = "x-inconnu";
        $visitor->surname = "x-inconnu";
        $visitor->confirmed = false;
        $visitor->quickLink = true;
        $visitor->save();
        return $visitor;
    }

    public static function searchVisitorsByName($searchQuery, $onlyConfirmed = true) {
        $visitors = static::where('quickLink', false)
                ->where(function($query) use ($onlyConfirmed) {
                    if ($onlyConfirmed) $query->where('confirmed', true);
                })->where(function($query) use ($searchQuery) {
                    $query->where('name', 'ilike', '%'.$searchQuery.'%')
                        ->orWhere('surname', 'ilike', '%'.$searchQuery.'%')
                        ->orWhere('email', 'ilike', '%'.$searchQuery.'%');
                })->get()->sortBy('name');
        return $visitors;
    }

    public static function searchByPresenceDate($dateBegin, $dateEnd) {
        $visitors = static::whereHas('reservations', function (Builder $query) use ($dateBegin, $dateEnd){
            $query->where(function($query) use ($dateBegin, $dateEnd) {
                    $query->whereDate('arrivaldate', '<=', $dateEnd)
                            ->whereDate('departuredate', '>=', $dateBegin);
                    })
                    ->orWhere(function($query) use ($dateBegin, $dateEnd) {
                        $query->whereDate('arrivaldate', '<=', $dateEnd)
                        ->where('nodeparturedate', true );
                    });
        })->get();
        return $visitors;
    }

    public static function searchByAge($ageBegin, $ageEnd) {
        $current_year = Carbon::now()->year;
        $visitors = static::where('confirmed', true)->where('birthyear', '>=', $current_year - $ageEnd)->where('birthyear', '<=', $current_year - $ageBegin)->get();
        return $visitors;
    }

    public static function getVisitorsList($onlyConfirmed = true) {
        $visitors = static::where('quickLink', false)->where(function($query) use ($onlyConfirmed) {
                    if ($onlyConfirmed) $query->where('confirmed', true);
                });
        return $visitors;
    }

    public function newCollection(array $models = [])
    {
        return new VisitorCollection($models);
    }

    protected $casts = [
        'name' => 'string',
    ];

    protected $appends = ['full_name', 'age'];


}
