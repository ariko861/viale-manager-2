<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visitor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["nom", "prenom", "confirmed", "date_de_naissance", "email", "phone"];

    protected function full_name(): Attribute
    {
        return Attribute::make(
            get: fn (): string => "{$this->prenom} {$this->nom}",
        );
    }
}
