<?php

namespace App\Livewire;

use App\Enums\MessageTypes;
use App\Models\Message;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class MessagesConfirmation extends Component implements HasForms
{
    use InteractsWithForms;

    public function form(Form $form): Form
    {
        $messages = Message::where('type', MessageTypes::Confirmation)->get();
        $messagesDisplay = $messages->map(function(Message $item, $index){
            return Placeholder::make($item->title)->content($item->message);
        });
        return $form->schema([
            Section::make('Bienvenue')
                ->schema($messagesDisplay->toArray())
        ]);
    }

    public function render()
    {
        return view('livewire.messages-confirmation');
    }
}
