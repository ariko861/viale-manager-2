<div>
    @if(!$valid_token)
        Pas de chance, ce lien n'existe pas
    @else
        <form wire:submit="create">
            {{$this->form}}
        </form>
    @endif
{{--        <x-filament-actions::modals />--}}

{{--        <x-filament::modal--}}
{{--            id="select-existing-visitor"--}}
{{--            :close-by-clicking-away="false"--}}
{{--            :close-button="false"--}}
{{--            width="2xl"--}}
{{--        >--}}
{{--            <x-slot name="heading">--}}
{{--                Personnes avec cette email--}}
{{--            </x-slot>--}}
{{--            @if($existing_visitors)--}}
{{--                <x-filament::input.wrapper>--}}
{{--                    <x-filament::input.select wire:model.live="selected_visitor_id">--}}
{{--                        <option value="0"> --- </option>--}}
{{--                        @foreach($existing_visitors as $visitor)--}}
{{--                            <option value="{{$visitor->id}}">{{$visitor->nom}} {{$visitor->prenom}}</option>--}}
{{--                        @endforeach--}}
{{--                    </x-filament::input.select>--}}
{{--                </x-filament::input.wrapper>--}}
{{--                {{$selected_visitor_id}}--}}

{{--                <x-slot name="footer">--}}
{{--                    <x-filament::button color="warning" wire:click="$dispatch('close-modal', {id: 'select-existing-visitor'})">--}}
{{--                        Aucune personne ne correspond--}}
{{--                    </x-filament::button>--}}
{{--                    @if($selected_visitor_id > 0)--}}
{{--                        <x-filament::button color="success" wire:click="selectVisitor">--}}
{{--                            Sélectionner cette personne--}}
{{--                        </x-filament::button>--}}
{{--                    @endif--}}

{{--                </x-slot>--}}
{{--            @endif--}}

{{--            --}}{{-- Modal content --}}
{{--        </x-filament::modal>--}}



    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
</div>
