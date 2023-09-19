<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MessageTypes: string implements HasLabel
{

    case Confirmation = 'confirmation';
    case Link = 'link';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Confirmation => 'Message affiché après la confirmation',
            self::Link => "Message affiché à l'ouverture du lien",
        };
    }
}
