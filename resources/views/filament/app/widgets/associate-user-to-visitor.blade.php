<x-filament-widgets::widget>
    <x-filament::section>
        @if($user->visitor?->exists())
            Vous êtes identifié·e en tant que {{ $user->visitor?->prenom }} {{ $user->visitor?->nom }}
        @else
            {{ $this->associateVisitorAction }}
        @endif

    </x-filament::section>
    <x-filament-actions::modals />
</x-filament-widgets::widget>
