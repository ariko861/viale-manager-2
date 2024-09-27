<?php

namespace App\Filament\App\Widgets;

use App\Filament\App\Pages\VisitorAssociation;
use App\Models\User;
use App\Models\Visitor;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AssociateUserToVisitor extends Widget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static string $view = 'filament.app.widgets.associate-user-to-visitor';

    public $user;
    public function mount(): void
    {
        $this->user = Auth::user();
    }

    public function associateVisitorAction(): Action
    {
        return Action::make('Associer un visiteur')
            ->color('info')
            ->url(VisitorAssociation::getUrl())
            ;
    }

}
