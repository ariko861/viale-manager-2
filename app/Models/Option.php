<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'value', 'description',
    ];

    public static function initiate(): void
    {
        self::firstOrCreate(['name' => 'email'], ['description' => "Email de l'utilisateur principal de viale-manager"]);
        self::firstOrCreate(['name' => 'phone'], ['description' => "Numéro de téléphone apparaissant sur la page d'accueil"]);
        self::firstOrCreate(['name' => 'address'], ['description' => "Adresse apparaissant sur la page d'accueil"]);
        self::firstOrCreate(['name' => 'reservationLinksMaxUse'], ['description' => "Nombre maximum d'utilisations d'un lien par un visiteur", 'value' => 3]);
//        Option::where('name', 'confirmation_message')->orderBy('id')->get();
//        Option::where('name', 'reservation_link_message')->orderBy('id')->get();
    }
}
