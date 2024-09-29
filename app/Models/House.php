<?php

namespace App\Models;

use App\Models\Collections\HousesCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;


class House extends Model
{
    use HasFactory;

    protected $appends = ['room_count'];

    protected $casts = [
        'community' => 'boolean',
        'displayHouseNameWithRoom' => 'boolean',
    ];

    protected $fillable = ['name', 'community', 'displayHouseNameWithRoom'];

    protected $attributes = ['title'];

    public function getRoomCountAttribute()
    {
        $this->rooms->count();
    }

    public function title(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->name,
        );
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function reservationVisitors()
    {
        return $this->hasMany(VisitorReservation::class);
    }

    public static function prepareForKanban(): Collection
    {
        $houses = self::query()->select('name', 'id')->where('community', true)->without('rooms')->get()->toArray();
        $houses = array_map(function($item){
            $item = array_combine(['id', 'title'], [$item['id'], $item['name']]);
            return $item;
        }, $houses);
        array_unshift($houses, ['id' => 0, 'title' => "à placer"]);

        return collect($houses);
    }

    public function scopeIsMaisonnee(Builder $query): void
    {
        $query->where('community', true);
    }

    public function newCollection(array $models = []): Collection
    {
        return new HousesCollection($models);
    }

}
