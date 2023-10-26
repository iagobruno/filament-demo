<?php

namespace App\Livewire;

use Filament\Facades\Filament;
use Filament\Forms\Components\{Actions, Section, Toggle};
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class PublicToggle extends Component implements HasForms
{
    use InteractsWithForms;

    public string $is_public;

    public function mount()
    {
        $this->is_public = Filament::getTenant()->settings['public'];
    }

    public function updatedIsPublic()
    {
        $public = $this->is_public === '1';
        Filament::getTenant()->updateSettings([
            'public' => $public
        ]);
        Notification::make()
            ->title($public ? 'Seu blog está público na internet' : 'Seu blog está oculto')
            ->body($public ? 'Qualquer pessoa com o link poderá visualizar' : 'Somente você tem acesso')
            ->success()
            ->seconds(3)
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make()
                ->compact()
                ->extraAttributes(['style' => 'margin-top: 24px'])
                ->columns(5)
                ->schema([
                    Toggle::make('is_public')
                        ->label('Visível ao público')
                        ->live()
                        ->columnSpan(4),
                    Actions::make([
                        Action::make('view-live')
                            ->iconButton()
                            ->icon('heroicon-m-eye')
                            ->url(route('blog-homepage', [Filament::getTenant()->slug]))
                            ->openUrlInNewTab()
                            ->tooltip('Ver blog em outra guia')
                            ->size(\Filament\Support\Enums\ActionSize::ExtraSmall),
                    ]),
                ])
        ]);
    }

    public function render()
    {
        return <<<'HTML'
        <form wire:submit="save">
            {{ $this->form }}
        </form>
        HTML;
    }
}
