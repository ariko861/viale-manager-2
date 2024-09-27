<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function getFullNameAttribute(): string
    {
        if ($this->house && $this->house->displayHouseNameWithRoom)
        {
            return ($this->house->name)." : ".($this->name);
        } else {
            return $this->name;
        }
    }

    public function sejours(?Carbon $startDate = null, ?Carbon $endDate = null): HasMany
    {
        if (!$startDate) $startDate = today();
        if (!$endDate) $endDate = today();
        return $this->hasMany(Sejour::class)->withinDates($startDate, $endDate);
    }






}
