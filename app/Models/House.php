<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class House extends Model
{
    use HasFactory;

    protected $appends = ['room_count'];

    protected $casts = [
        'community' => 'boolean',
        'displayHouseNameWithRoom' => 'boolean',
    ];

    protected $fillable = ['name', 'community', 'displayHouseNameWithRoom'];

    public function getRoomCountAttribute()
    {
        $this->rooms->count();
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function reservationVisitors()
    {
        return $this->hasMany(VisitorReservation::class);
    }
}
