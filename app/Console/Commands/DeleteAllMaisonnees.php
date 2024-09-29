<?php

namespace App\Console\Commands;

use App\Models\AssignationMaisonnee;
use App\Models\MaisonneesPlanning;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DeleteAllMaisonnees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-all-maisonnees';

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
        DB::table('houses_in_maisonnees_planning')->truncate();
        AssignationMaisonnee::query()->delete();
        MaisonneesPlanning::query()->delete();

    }
}
