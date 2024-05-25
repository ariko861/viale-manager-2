<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = ["name", "price", "remarques", "is_default"];


    public function getEuroAttribute()
    {
        return number_format($this->price, 2,'€',' ');
    }

    protected $appends = ['euro'];
}
