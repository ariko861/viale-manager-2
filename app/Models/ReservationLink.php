<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReservationLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'email', 'link_token', 'registered_at', 'times_used'
    ];

    public function generateLinkToken() {
        $this->link_token = Str::uuid();
    }

    public function getLink() {
        return urldecode(route('confirmation') . '?link_token=' . $this->link_token);
    }

    public function reservation() {
        return $this->belongsTo(Reservation::class);
    }

    public function getTimesLeftAttribute(){

        $maxUse = $this->getMaxUseForLink();
        return $maxUse - $this->times_used;
    }

    public function useLinkOnce(){
        
        $this->times_used += 1;
        
        $maxUse = $this->getMaxUseForLink();
        
        if ($this->times_used >= $maxUse){
            $this->delete();
            return "Vous ne pouvez plus utiliser ce lien de confirmation";
        } else {
            $this->save();
            return "Vous pouvez utiliser ce lien encore ".($maxUse - $this->times_used)." fois pour modifier votre réservation";
        }
    }

    public function getMaxUseForLink(){
        $optionsMaxUsed = Option::where('name', 'reservationLinksMaxUse')->first(); 
        if ($optionsMaxUsed) {
            return (int) $optionsMaxUsed->value;
        } else {
            return 1;
        }
    }
}
