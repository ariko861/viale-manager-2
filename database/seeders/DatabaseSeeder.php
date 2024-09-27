<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Enums\MessageTypes;
use App\Models\Message;
use App\Models\Option;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Option::initiate();
        Message::query()->create([
            "title" => "",
            "message" => "Bonjour, ce formulaire de réservation a été conçu spécifiquement pour la Viale. Il nous sert à faciliter le travail d'organisation et d'administration entièrement bénévole de la communauté de la Viale.",
            "type" => MessageTypes::Link,
        ]);
        Message::query()->create([
            "title" => "",
            "message" => "Prière de bien renseigner toutes les personnes vous accompagnant dans les champs prévus à cet effet. Le renseignement des adresses email est optionnel.",
            "type" => MessageTypes::Link,
        ]);
        Message::query()->create([
            "title" => "",
            "message" => "Les données recueillies sont utiles uniquement au fonctionnement interne de la Viale. Le logiciel utilisé repose sur du code complètement opensource publié sur [Github](https://github.com/ariko861/viale-manager).",
            "type" => MessageTypes::Link,
        ]);
        Message::query()->create([
            "title" => "",
            "message" => "<p>Pour le paiement de votre séjour:</p><ul><li>en liquide sur place</li><li>par virement sur le compte IBAN FR76 1350 6100 0077 3729 1300 076.</li></ul>",
            "type" => MessageTypes::Link,
        ]);

        Message::query()->create([
            "title" => "",
            "message" => "Vous pouvez consulter les documents indispensables à votre séjour ici : <a href='https://cloud.alternumerica.org/s/NCWkntmSip2YMtn'>Documents aux visiteurs</a>",
            "type" => MessageTypes::Confirmation,
        ]);

        Message::query()->create([
            "title" => "",
            "message" => " Inscrivez vous à la <a href='https://laviale.be/newsletter/'>newsletter de la Viale</a> pour recevoir les dernières nouvelles.",
            "type" => MessageTypes::Confirmation,
        ]);

        Message::query()->create([
            "title" => "",
            "message" => "<p>Vous pouvez aussi rejoindre le salon de discussion de la Viale sur</p><ul><li><a href='https://matrix.to/#/#lavialelozerepratique:alternumerica.org'>Matrix ( Element )</a></li><li>sur <a href='https://signal.group/#CjQKIJxCW2gkyyIIbm9KP6DsTi3053VrHu7KQhEg8JFf2js-EhCUfllZHhcDUx7z8nZP0Uhg'>Signal</a></li><li>sur <a href='https://t.me/joinchat/TZIfobU4ZcswZTI0'>Telegram</a></li><li>sur <a href='https://chat.whatsapp.com/KrNvTeTFBqvJKsusEd3uwG'>WhatsApp</a></li></ul>",
            "type" => MessageTypes::Confirmation,
        ]);

        Message::query()->create([
            "title" => "",
            "message" => "<p>Pour le paiement de votre séjour:</p><ul><li>en liquide sur place</li><li>par virement sur le compte IBAN FR76 1350 6100 0077 3729 1300 076.</li></ul>",
            "type" => MessageTypes::Confirmation,
        ]);



    }
}
