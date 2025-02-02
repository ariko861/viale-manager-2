<?php

namespace App\Console\Commands;

use App\Enums\AutoMailTypes;
use App\Models\AutoMail;
use App\Models\Sejour;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SendAutoMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-auto-mails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // On commence par les mails à l'arrivée
        $autoMailsArrivals = AutoMail::query()
            ->where('type', AutoMailTypes::Arrival)
            ->where('actif', true)
            ->get();


        foreach ($autoMailsArrivals as $autoMail){

            $today = Carbon::today();
            $date = $today->addDays($autoMail->time_delta);

            $emails = Visitor::query()
                ->whereHas('sejours', function (Builder $query) use ($date){
                    $query->whereDate('arrival_date', $date)
                        ->where('confirmed', true);
                })
//                ->get()
                ->select('email')
                ->pluck('email')
                ->toArray()
            ;

            // We remove duplicates
            $emails = array_unique($emails);
            $autoMail->sendTo($emails, hidden: true);

            $count = count($emails);

            echo "Email d'arrivée envoyé à {$count} personnes\n";


        }


    }
}
