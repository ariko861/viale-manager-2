<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = ["name", "price", "remarques", "is_default"];


    public function getEuroAttribute()
    {
        return number_format($this->price, 2,'€',' ');
    }

    public static function retrieveOptions(): array|Collection
    {
        return self::all()->mapWithKeys(function($profile) {
            return [$profile->price => $profile->name. " ".$profile->euro];
        });
    }

    protected $appends = ['euro'];
}
