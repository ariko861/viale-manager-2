<?php

namespace App\Console\Commands;

use App\Mail\Test;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-email {email} {--queue} {--times=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoi un email de test';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->info("Cet email n'est pas un email valide");
            return;
        }

        if ($this->option("queue")) {
            Mail::to($email)->queue(new Test());
        } else {
            Mail::to($email)->send(new Test());
        }

        if ($this->option("times")) {
            $n = (int)$this->option("times");
            for ($x = 1; $x < $n; $x++) {
                if ($this->option("queue")) {
                    Mail::to($email)->queue(new Test());
                } else {
                    Mail::to($email)->send(new Test());
                }
            }
        }
    }
}
