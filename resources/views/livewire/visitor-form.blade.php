<div class="max-w-full">
    <div class="max-w-6xl mx-auto mt-4">
    @if(!$valid_token)
        Pas de chance, ce lien n'existe pas
    @else
        <form wire:submit="create">
            {{$this->form}}
        </form>
    @endif
    </div>
</div>
