<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <div class="flex flex-col gap-y-6">
        @if (count($tabs = $this->getTabs()))
            <x-filament::tabs>
                {{ \Filament\Support\Facades\FilamentView::renderHook('panels::resource.pages.list-records.tabs.start', scopes: $this->getRenderHookScopes()) }}

                @foreach ($tabs as $tabKey => $tab)
                    @php
                        $activeTab = (string) $activeTab;
                        $tabKey = (string) $tabKey;
                    @endphp

                    <x-filament::tabs.item
                        :active="$activeTab === $tabKey"
                        :badge="$tab->getBadge()"
                        :icon="$tab->getIcon()"
                        :icon-position="$tab->getIconPosition()"
                        :wire:click="'$set(\'activeTab\', ' . (filled($tabKey) ? ('\'' . $tabKey . '\'') : 'null') . ')'"
                    >
                        {{ $tab->getLabel() ?? $this->generateTabLabel($tabKey) }}
                    </x-filament::tabs.item>
                @endforeach

                {{ \Filament\Support\Facades\FilamentView::renderHook('panels::resource.pages.list-records.tabs.end', scopes: $this->getRenderHookScopes()) }}
            </x-filament::tabs>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook('panels::resource.pages.list-records.table.before', scopes: $this->getRenderHookScopes()) }}

        {{ $this->table }}

        {{ \Filament\Support\Facades\FilamentView::renderHook('panels::resource.pages.list-records.table.after', scopes: $this->getRenderHookScopes()) }}
    </div>

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
</x-filament-panels::page>
