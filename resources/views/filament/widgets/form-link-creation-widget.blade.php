<x-filament-widgets::widget>
    <x-filament::section>
        {{ $this->createReservationForm }}
    </x-filament::section>

    <x-filament-actions::modals />

    <x-filament::modal id="link-display" width="3xl">
        <x-slot name="heading">
            Nouveau lien de réservation
        </x-slot>
        <x-slot name="description">
            Un nouveau lien de réservation à transmettre
        </x-slot>

        <x-filament::link
            id="lien-reservation"
            icon="heroicon-o-clipboard"
            href="{{$this->lien_reservation}}"
        >
            {{$this->lien_reservation}}
        </x-filament::link>

        {{-- Modal content --}}
    </x-filament::modal>

</x-filament-widgets::widget>
