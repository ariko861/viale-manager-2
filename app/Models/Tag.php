<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'color',
    ];

    public function normalize()
    {
        $this->name = ucfirst(strtolower($this->name));
    }

    public function visitors()
    {
        return $this->belongsToMany(Visitor::class, 'visitor_tag');
    }
}
