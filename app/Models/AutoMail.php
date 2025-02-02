<?php

namespace App\Models;

use App\Enums\AutoMailTypes;
use App\Mail\AutoMailSender;
use App\Mail\ReservationConfirmed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class AutoMail extends Model
{
    use HasFactory;

    protected $table = 'auto_mails';

    protected $fillable = ['sujet', 'body', 'actif', 'type', 'time_delta'];

    protected $casts = [
        'type' => AutoMailTypes::class,
    ];

    public function sendTo(array|string $recipients): void
    {
        Mail::to($recipients)->queue(new AutoMailSender($this));
    }

}
