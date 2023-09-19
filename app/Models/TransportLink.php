<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TransportLink extends Model
{
    use HasFactory;
    protected $table = 'transports_links';

    public function generateLinkToken() {
        $this->link_token = Str::uuid();
    }

    public function getLink() {
        return urldecode(route('transports-disponibles') . '?link_token=' . $this->link_token);
    }

    public function getDateCarbonAttribute() {
        return Carbon::parse($this->date);
    }

    public function getLinkTypeAttribute(){
        $date = Carbon::parse($this->date);
        $dateBegin = $date->copy()->subDays($this->interval);
        $dateEnd= $date->copy()->addDays($this->interval);
        $interval = "entre le {$dateBegin->isoFormat('dddd Do MMMM YYYY')} et le {$dateEnd->isoFormat('dddd Do MMMM YYYY')}";
        return $this->type === "offer_places" ? "Personnes proposant des places de voitures {$interval}" : "Personnes recherchant des places de voitures {$interval}";
    }

    public function getReservations() {
        $date = Carbon::parse($this->date);
        $dateBegin = $date->copy()->subDays($this->interval);
        $dateEnd= $date->copy()->addDays($this->interval);

        return Reservation::where('confirmed', true)
            ->when($this->type == 'offer_places', function(Builder $query){
                $query->where('hasCarPlaces', true);
            }, function (Builder $query){
                $query->where('lookForCarPlaces', true);
            })
            ->whereDate('arrivaldate', '>=', $dateBegin)
            ->whereDate('arrivaldate', '<=', $dateEnd)
            ->orderBy('arrivaldate')
            ->get();
    }

}
