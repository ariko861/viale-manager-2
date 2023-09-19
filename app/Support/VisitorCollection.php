<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Collection;

class VisitorCollection extends Collection
{
    public function getVisitorsByName($searchQuery)
    {
        return $this->where('confirmed', true)->where('quickLink', false)->where(function($query) use ($searchQuery) {
                $query->where('name', 'ilike', '%'.$searchQuery.'%')
                    ->orWhere('surname', 'ilike', '%'.$searchQuery.'%')
                    ->orWhere('email', 'ilike', '%'.$searchQuery.'%');
            });
    }

}

