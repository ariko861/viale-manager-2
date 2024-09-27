<x-mail::message>
    # Réservation confirmée
    La réservation {{ $reservation->id }} a bien été confirmée.
    Les séjours des personnes suivantes sont confirmés
    @foreach($reservation->sejours as $sejour)
        - {{$sejour->visitor?->prenom}} {{$sejour->visitor?->nom}}
            Arrivera le {{$sejour->arrival_date?->toFormattedDateString()}}
            Repartira le {{$sejour->departure_date?->toFormattedDateString()}}
            Âge : {{ $sejour->visitor?->age }}
            Prix de la nuit : {{ $sejour->price }}
    @endforeach

<x-mail::button :url="\App\Filament\Resources\ReservationResource::getUrl('view', ['record' => $reservation->id])">
Accéder à la réservation
</x-mail::button>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
