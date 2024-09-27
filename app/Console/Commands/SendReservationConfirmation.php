<?php

namespace App\Console\Commands;

use App\Mail\ReservationConfirmed;
use App\Models\Option;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReservationConfirmation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-reservation-confirmation {reservation_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoi la confirmation de réservation par email pour la réservation donnée';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = (int)$this->argument('reservation_id');
        $reservation = Reservation::query()->find($id);
        Mail::to(Option::getVialeEmail())->send(new ReservationConfirmed($reservation));

    }
}
