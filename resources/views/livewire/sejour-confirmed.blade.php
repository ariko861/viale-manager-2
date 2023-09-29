<div>
    @if($valid_token)
{{--        {{$this->messagesInfolist}}--}}
        <livewire:messages-confirmation/>
        {{$this->reservationInfolist}}
    @endif
</div>
