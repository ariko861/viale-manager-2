<x-mail::message>
    # Réservation confirmée
    Votre réservation a bien été confirmée.
    Les séjours des personnes suivantes sont confirmés
    @foreach($reservation->sejours as $sejour)
        - {{$sejour->visitor?->prenom}} {{$sejour->visitor?->nom}}
        Arrivera le {{$sejour->arrival_date?->toFormattedDateString()}}
        Repartira le {{$sejour->departure_date?->toFormattedDateString()}}
        Âge : {{ $sejour->visitor?->age }}
        Prix de la nuit : {{ $sejour->price }}
    @endforeach

<x-mail::button :url="$reservation->getLinkConfirmed()">
Votre confirmation de réservation
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
