<div>
    {{-- Do your work, then step back. --}}
    <x-filament::modal
                id="select-sejour-room"
                width="5xl"
            >
        <x-slot name="heading">
            Occupation des chambres du {{$startDate->toFormattedDayDateString()}} au {{$endDate->toFormattedDayDateString()}}
        </x-slot>
        {{ $this->table }}

    </x-filament::modal>
</div>
