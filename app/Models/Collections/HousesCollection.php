<?php

namespace App\Models\Collections;

use Illuminate\Database\Eloquent\Collection;

class HousesCollection extends Collection
{

    public function prepareForKanban(): \Illuminate\Support\Collection
    {
        $houses = $this->toArray();
        $houses = array_map(function($item){
            $item = array_combine(['id', 'title'], [$item['id'], $item['name']]);
            return $item;
        }, $houses);
        array_unshift($houses, ['id' => 0, 'title' => "à placer"]);

        return collect($houses);
    }
}
