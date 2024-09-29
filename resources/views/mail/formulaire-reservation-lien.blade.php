<x-mail::message>
# Votre lien pour le formulaire de réservation de {{ config('app.name') }}

Bonjour,
    voici le lien vers le formulaire à remplir pour votre séjour, vous pourrez y indiquer vos dates et les personnes vous accompagnant.

<x-mail::button :url="$reservation->getLink()">
Mon formulaire de réservation
</x-mail::button>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
