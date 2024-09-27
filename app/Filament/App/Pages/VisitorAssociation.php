<?php

namespace App\Filament\App\Pages;

use App\Models\Visitor;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class VisitorAssociation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.app.pages.visitor-association';
    protected static bool $shouldRegisterNavigation = false;

    public $user;
    public function mount(): void
    {
        $this->user = Auth::user();
    }

    public $defaultAction = 'onboarding';

    public function onboardingAction(): Action
    {
        return Action::make('Associer un visiteur')
            ->color('info')
            ->form([
                Select::make('visitor_id')
                    ->options(Visitor::query()->where('email', $this->user?->email)->get()->mapWithKeys(function(Visitor $visitor){
                        return [$visitor->id => $visitor->full_name];
                    }))
            ])
            ->action(function(array $data): void
            {
                $this->user->visitor_id = $data['visitor_id'];
                $this->user->save();
                $this->redirect(Dashboard::getUrl(panel: 'app'));
            });
    }

}
