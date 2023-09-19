<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatrixLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'link', 'homeserver', 'roomID',
    ];

    protected $attributes = [
        'gallery' => false,
        'displayAddress' => false,
        'displayDate' => false,
    ];

    protected $casts = [
        'gallery' => 'boolean',
        'displayAddress' => 'boolean',
        'displayDate' => 'boolean',

    ];

    public function getLink()
    {
        return urldecode(route('matrix') . '/' . $this->link);
    }
}
