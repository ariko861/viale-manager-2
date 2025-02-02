<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AutoMailTypes: string implements HasLabel
{
    case Confirmation = 'confirmation';
    case Arrival = 'arrival';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Confirmation => 'Mail envoyé à la confirmation de réservation',
            self::Arrival => "Mail envoyé à l'arrivée de la personne",
        };
    }
}
